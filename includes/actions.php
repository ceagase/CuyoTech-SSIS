<?php
/* ---------- POST actions (CSRF-checked, role-checked, prepared statements) ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["t"], $_POST["t"] ?? "")) {
        http_response_code(400);
        exit("Invalid form token");
    }
    $a = $_GET["a"] ?? "";
    $back = $_POST["p"] ?? "";
    if ($a === "login") {
        $_SESSION["tries"] = $_SESSION["tries"] ?? 0;
        if ($_SESSION["tries"] >= 5) {
            flash("Too many attempts. Close the browser and try again later.");
            go();
        }
        $r = q("SELECT * FROM users WHERE username=?", [
            trim($_POST["id"] ?? ""),
        ])->fetch();
        if (!$r || !password_verify($_POST["pw"] ?? "", $r["password_hash"])) {
            $_SESSION["tries"]++;
            flash("Wrong ID number or password. Check both and try again.");
            go();
        }
        if (!$r["active"]) {
            flash("This account is deactivated. Contact the Admin.");
            go();
        }
        unset($r["password_hash"]);
        session_regenerate_id(true);
        $_SESSION["u"] = $r;
        $_SESSION["tries"] = 0;
        go();
    }
    if (!$u) {
        http_response_code(401);
        exit("Please log in");
    }
    if ($a === "logout") {
        session_destroy();
        header("Location: index.php");
        exit();
    }
    if ($a === "req") {
        need("student");
        $t = (int) ($_POST["type"] ?? 0);
        $pu = trim($_POST["purpose"] ?? "");
        if (!q("SELECT 1 FROM document_types WHERE id=?", [$t])->fetch()) {
            flash("Choose a document type first.");
        } elseif ($pu === "") {
            flash("Enter the purpose of your request.");
        } else {
            q(
                "INSERT INTO document_requests(student_id,type_id,purpose) VALUES(?,?,?)",
                [$u["id"], $t, mb_substr($pu, 0, 200)],
            );
            flash("Request submitted.");
        }
    }
    if ($a === "enroll") {
        need("student");
        q(
            "INSERT IGNORE INTO enrollments(student_id,status) VALUES(?, 'Pending')",
            [$u["id"]],
        );
        flash("Enrollment request sent.");
    }
    if ($a === "enroll_ok") {
        need("registrar");
        q("UPDATE enrollments SET status='Enrolled' WHERE student_id=?", [
            (int) $_POST["sid"],
        ]);
        flash("Enrollment approved.");
    }
    if ($a === "assign_courses") {
        need("registrar");
        $studentId = (int) ($_POST["sid"] ?? 0);
        $isEnrolled = q(
            "SELECT 1 FROM enrollments WHERE student_id=? AND status='Enrolled'",
            [$studentId],
        )->fetch();
        if (!$isEnrolled) {
            flash("Approve the student's enrollment before assigning courses.");
        } else {
            q(
                "DELETE FROM student_courses WHERE student_id=? AND semester=? AND school_year=?",
                [$studentId, "1st Semester", "2026-2027"],
            );
            $submittedCourses = is_array($_POST["courses"] ?? null)
                ? $_POST["courses"]
                : [];
            $courseIds = array_unique(array_map("intval", $submittedCourses));
            foreach ($courseIds as $courseId) {
                if ($courseId > 0) {
                    q(
                        "INSERT IGNORE INTO student_courses(student_id,course_id,semester,school_year)
                         SELECT ?,id,?,? FROM courses WHERE id=?",
                        [$studentId, "1st Semester", "2026-2027", $courseId],
                    );
                }
            }
            flash("Course registration saved.");
        }
        $back = "cr|student_no=" . rawurlencode($_POST["student_no"] ?? "");
    }
    if ($a === "status") {
        need("registrar");
        $id = (int) $_POST["id"];
        $s = $_POST["status"] ?? "";
        if (
            !in_array($s, [
                "Pending",
                "Processing",
                "Ready",
                "Completed",
                "Rejected",
            ])
        ) {
            exit("Bad status");
        }
        $paid = q("SELECT 1 FROM payments WHERE request_id=?", [$id])->fetch();
        if (!$paid && in_array($s, ["Processing", "Ready", "Completed"])) {
            flash("Payment is not recorded yet. Ask the cashier first.");
        } else {
            q("UPDATE document_requests SET status=? WHERE id=?", [$s, $id]);
            flash("Status updated.");
        }
    }
    if ($a === "grades") {
        need("registrar");
        $sid = (int) $_POST["sid"];
        foreach ($_POST["g"] ?? [] as $cid => $v) {
            $v = trim($v);
            $cid = (int) $cid;
            $registered = q(
                "SELECT 1 FROM student_courses
                 WHERE student_id=? AND course_id=? AND semester=? AND school_year=?",
                [$sid, $cid, "1st Semester", "2026-2027"],
            )->fetch();
            if (!$registered) {
                $bad = 1;
                continue;
            }
            if ($v === "") {
                q("DELETE FROM grades WHERE student_id=? AND course_id=?", [
                    $sid,
                    $cid,
                ]);
            } elseif (
                preg_match('/^[1-5](\.\d{1,2})?$/', $v) &&
                (float) $v <= 5
            ) {
                q(
                    "REPLACE INTO grades(student_id,course_id,grade) VALUES(?,?,?)",
                    [$sid, $cid, $v],
                );
            } else {
                $bad = 1;
            }
        }
        flash(
            isset($bad)
                ? "Some grades were skipped. Use values from 1.00 to 5.00."
                : "Grades saved.",
        );
        $back = "ge&sid=$sid";
    }
    if ($a === "pay") {
        need("cashier");
        $id = (int) $_POST["id"];
        $r = q(
            "SELECT t.fee FROM document_requests r JOIN document_types t ON t.id=r.type_id WHERE r.id=?",
            [$id],
        )->fetch();
        if (
            $r &&
            !q("SELECT 1 FROM payments WHERE request_id=?", [$id])->fetch()
        ) {
            q("INSERT INTO payments(request_id,amount) VALUES(?,?)", [
                $id,
                $r["fee"],
            ]);
            q(
                'UPDATE payments SET receipt_no=CONCAT("OR-",1000+id) WHERE request_id=?',
                [$id],
            );
            flash("Payment recorded, receipt issued.");
        }
    }
    if ($a === "add_fee") {
        need("cashier");
        $studentNumber = trim($_POST["sid"] ?? "");
        $category = $_POST["category"] ?? "";
        $description = trim($_POST["description"] ?? "");
        $amount = trim($_POST["amount"] ?? "");
        $dueDate = trim($_POST["due_date"] ?? "");
        $parsedDueDate = $dueDate
            ? DateTime::createFromFormat("!Y-m-d", $dueDate)
            : null;
        $validDueDate = !$dueDate ||
            ($parsedDueDate && $parsedDueDate->format("Y-m-d") === $dueDate);
        $student = q(
            "SELECT u.id FROM users u JOIN students s ON s.user_id=u.id
             WHERE u.username=? AND u.role='student' AND u.active=1",
            [$studentNumber],
        )->fetch();
        $tab = $_POST["tab"] ?? "enrollment";
        $validCategory = ($tab === "enrollment" && $category === "Enrollment") ||
            ($tab === "other" && in_array($category, ["Laboratory", "Other"], true));
        if (
            !$student ||
            !preg_match('/^\d+$/', $studentNumber) ||
            !$validCategory ||
            $description === "" ||
            !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $amount) ||
            (float) $amount <= 0 ||
            !$validDueDate
        ) {
            flash("Check the student ID number, fee type, description, amount, and due date.");
        } else {
            q(
                "INSERT INTO student_fee_charges(student_id,category,description,amount,due_date,semester,school_year,created_by)
                 VALUES(?,?,?,?,?,?,?,?)",
                [
                    $student["id"],
                    $category,
                    mb_substr($description, 0, 120),
                    $amount,
                    $dueDate ?: null,
                    CURRENT_SEMESTER,
                    CURRENT_SCHOOL_YEAR,
                    $u["id"],
                ],
            );
            flash("Student fee added to the account.");
        }
        $back = "pay|" . (in_array($tab, ["enrollment", "other"], true) ? $tab : "enrollment");
    }
    if ($a === "pay_fee") {
        need("cashier");
        $id = (int) ($_POST["id"] ?? 0);
        $receipt = "STF-" . str_pad((string) $id, 6, "0", STR_PAD_LEFT);
        $result = q(
            "UPDATE student_fee_charges SET status='Paid',receipt_no=?,paid_by=?,paid_at=CURRENT_TIMESTAMP
             WHERE id=? AND status='Unpaid'",
            [$receipt, $u["id"], $id],
        );
        flash(
            $result->rowCount()
                ? "Payment recorded. Receipt $receipt issued."
                : "That fee was already paid or could not be found.",
        );
        $tab = $_POST["tab"] ?? "enrollment";
        $back = "pay|" . (in_array($tab, ["enrollment", "other"], true) ? $tab : "enrollment");
    }
    if ($a === "clr") {
        need("dept", "library", "registrar", "cashier", "guidance", "clinic", "affairs", "lab", "property");
        $o = $_POST["office"] ?? "";
        if (!$o || $o !== clearanceOfficeForRole($u["role"])) {
            exit("Bad office");
        }
        q(
            "INSERT INTO clearances(student_id,office,status) VALUES(?,?,'Cleared') ON DUPLICATE KEY UPDATE status=IF(status='Cleared','Pending','Cleared')",
            [(int) $_POST["sid"], $o],
        );
        flash("Clearance updated.");
        $back = "dc|search=" . rawurlencode($_POST["search"] ?? "") . "&year=" . rawurlencode($_POST["year"] ?? "");
    }
    if ($a === "adduser") {
        need("admin");
        $id = trim($_POST["id"]);
        $n = trim($_POST["name"]);
        $rl = $_POST["role"];
        $pw = $_POST["pw"];
        if (
            !$id ||
            !$n ||
            strlen($pw) < 6 ||
            !in_array($rl, ["student", "registrar", "cashier", "dept", "library", "guidance", "clinic", "affairs", "lab", "property", "admin"])
        ) {
            flash("Fill all fields. Password needs 6 or more characters.");
        } elseif (q("SELECT 1 FROM users WHERE username=?", [$id])->fetch()) {
            flash("That ID number already exists.");
        } else {
            q(
                "INSERT INTO users(username,password_hash,name,role) VALUES(?,?,?,?)",
                [$id, password_hash($pw, PASSWORD_DEFAULT), $n, $rl],
            );
            if ($rl === "student") {
                q(
                    "INSERT INTO students(user_id,program,year_level) VALUES(?, 'BS Information Technology','1st Year')",
                    [$pdo->lastInsertId()],
                );
            }
            flash("Account created.");
        }
    }
    if ($a === "toggle") {
        need("admin");
        $id = (int) $_POST["id"];
        if ($id != $u["id"]) {
            q("UPDATE users SET active=1-active WHERE id=?", [$id]);
        }
    }
    go($back);
}

