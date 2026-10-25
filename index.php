<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/functions.php";
$u = $_SESSION["u"] ?? null;
require_once __DIR__ . "/includes/actions.php";
require_once __DIR__ . "/includes/navigation.php";$logo = '<div class="logo"><div class="ct">CT</div>CuyoTech</div>';
$flash = $_SESSION["f"] ?? "";
unset($_SESSION["f"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>CuyoTech Student Services Information System</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Rozha+One&family=Sofia+Sans+Condensed:wght@400;600&family=Inter:wght@400;500&display=swap">
    <link rel="stylesheet" href="assets/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <?php if (!$u): ?>
    <div id="login">
        <section class="hero"><?= str_replace("CuyoTech", "CuyoTech University", $logo,) ?>
            <div style="margin:auto 0">
                <small style="color:#ddd">Student Services Information System</small>
                <h1>Official Student Services Portal of CuyoTech.</h1>
                <ul>
                    <li>View profile, courses and grades</li>
                    <li>Request a document and track its status</li>
                    <li>Process clearance and payments</li>
                </ul>
            </div>
        </section>
        <section class="formside">
            <h2>Login</h2>
            <div style="color:#555;margin-top:4px">Use your provided university account.</div>
            <form method="post" action="?a=login"><?= csrfIn() ?>
                <label for="uid">UNIVERSITY ID NUMBER</label>
                <input id="uid" name="id" placeholder="eg. 260001" autocomplete="username" aria-describedby="login-error" required>
                <label for="pw">PASSWORD</label>
                <div class="pw">
                    <input id="pw" name="pw" type="password" placeholder="Enter your password"
                        autocomplete="current-password" aria-describedby="login-error" required>
                    <button type="button" class="password-toggle" style="color:black;" id="password-toggle" aria-pressed="false" onclick="togglePassword()"><i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="err" id="login-error" role="alert" aria-live="polite"><?= e($flash,) ?></div>
                <div style="text-align:right;margin-top:12px">
                    <button type="button" class="forgot-link" onclick="document.getElementById('forgot-dialog').showModal()">Forgot Password?</button>
                </div>
                <dialog class="modal" id="forgot-dialog" aria-labelledby="forgot-title">
                    <h2 id="forgot-title">Forgot your password?</h2>
                    <p>Please ask the Admin to reset your password.</p>
                    <button type="button" class="btn s" onclick="document.getElementById('forgot-dialog').close()">Close</button>
                </dialog>
                <button class="btn" style="margin-top:6px">Login</button>
            </form>
            <p style="font-size:15px;color:#666;margin-top:22px"><b>Demo accounts: </b> 260001 (student), registrar, cashier, dept, admin <br> <b>passwords</b>: password
            </p>
        </section>
    </div>
    <?php else:
          $nav = $NAV[$u["role"]];
          $ids = array_column($nav, 0);
          $p = $_GET["p"] ?? "";
          if (!in_array($p, $ids)) {
              $p = $ids[0];
          }
          $sid = $u["id"];
          ?>
    <div id="app">
        <header><?= $logo ?>
        <div class="u">
          <div class="av"><?= e( mb_substr($u["name"], 0, 1),) ?></div>
            <div style="line-height:1.15"><?= e($u["name"],) ?>
              <br>
              <small style="color:#bbb"><?= e($u["username"]) ?></small>
            </div>
                <?= F(
                  "logout", "", "", "<i class='bi bi-box-arrow-right' aria-hidden='true'></i> Log out", "ghost",) 
                ?>
            </div>
        </header>
        <div class="body">
            <nav>
              <?php foreach ($nav as $n) {
                echo "<a class='" . ($n[0] == $p ? "on" : "") . "' href='?p={$n[0]}'><i class='bi {$n[2]}' aria-hidden='true'></i><span>{$n[1]}</span></a>";} 
              ?>
            </nav>
            <main>
                <?php
                    if ($flash) {
                        echo '<div class="card" style="max-width:900px"><div class="cb" role="status">' . e($flash) ."</div></div>";
                    }
                    switch ($p) {
                        case "dashboard":
                          $s = q("SELECT * FROM students WHERE user_id=?", [$sid, ])->fetch();
                          $en =q("SELECT status FROM enrollments WHERE student_id=?", [$sid], )->fetchColumn() ?: "Pending"; ?>
                <div class="card" style="max-width:1000px">
                    <div class="cb"
                        style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">
                        <div>
                            <h2 style="font-size:24px"><?= e($u["name"], ) ?></h2>
                            <small><?= e($s["program"]) ?>
                                <br><?= e($s["year_level"],) ?>
                            </small>
                        </div>
                        <div style="text-align:right">
                            <small>1st Semester</small> <br> <b>A.Y. 2026-2027</b> <br><?= badge($en,) ?>
                        </div>
                    </div>
                </div>
                <div class="grid">
                    <div class="card">
                        <div class="ch">Courses <a class="ghost" href="?p=courses">View Schedule</a>
                        </div><?= courseTbl($sid) ?>
                    </div>
                    <div>
                        <div class="card">
                            <div class="ch">Document Requests <a class="ghost" href="?p=docs">View All</a> </div>
                            <?= tbl(["DOCUMENT", "STATUS"], 
                            array_map( fn($r) => [e($r["name"]), badge($r["status"])], array_slice(myReqs($sid), 0, 3),
                          ),
                      ) ?>
                        </div>
                        <div class="card">
                            <div class="ch">Clearance <a class="ghost" href="?p=clear">View</a></div><?php
                      $r = [];
                      foreach (array_slice(clearance($sid), 0, 3, true) as $o => $st) {
                          $r[] = [$o, badge($st)];
                      }
                      echo tbl(["OFFICE", "STATUS"], $r);
                      ?>
                        </div>
                    </div>
                </div>
                <?php break;
                        case "courses":
                            echo '<h1>Class Schedule</h1><p>Your course meetings for 1st Semester, A.Y. 2026–2027. Check this schedule for your class days, times, rooms, and instructors.</p><div class="card">' .
                                courseScheduleTbl($sid) .
                                "</div>";
                            break;
                        case "grades":
                            $g = q(
                                "SELECT g.course_id,g.grade FROM grades g
                                 JOIN student_courses sc ON sc.student_id=g.student_id AND sc.course_id=g.course_id
                                   AND sc.semester=? AND sc.school_year=?
                                 WHERE g.student_id=?",
                                ["1st Semester", "2026-2027", $sid],
                            )->fetchAll(PDO::FETCH_KEY_PAIR);
                            $gradeSummary = q(
                                "SELECT COUNT(*) AS graded_courses,COALESCE(SUM(g.grade*c.units),0) AS weighted_total,COALESCE(SUM(c.units),0) AS graded_units
                                 FROM grades g JOIN courses c ON c.id=g.course_id
                                 JOIN student_courses sc ON sc.student_id=g.student_id AND sc.course_id=g.course_id
                                   AND sc.semester=? AND sc.school_year=?
                                 WHERE g.student_id=?",
                                ["1st Semester", "2026-2027", $sid],
                            )->fetch();
                            $weightedAverage = $gradeSummary["graded_units"]
                                ? number_format(
                                    $gradeSummary["weighted_total"] /
                                        $gradeSummary["graded_units"],
                                    2,
                                )
                                : "—";
                            echo '<h1>Grades</h1><p>Your posted grades for 1st Semester, A.Y. 2026–2027.</p>
                                <div class="academic-summary">
                                    <div><span>Courses graded</span><strong>' .
                                (int) $gradeSummary["graded_courses"] .
                                '</strong></div>
                                    <div><span>Units graded</span><strong>' .
                                (int) $gradeSummary["graded_units"] .
                                '</strong></div>
                                    <div><span>Weighted grade average</span><strong>' .
                                $weightedAverage .
                                '</strong></div>
                                </div>';
                            echo '<div class="card">' .
                                tbl(
                                    ["COURSE CODE", "SUBJECT", "GRADE"],
                                    array_map(
                                        fn($c) => [
                                            e($c["code"]),
                                            e($c["title"]),
                                            isset($g[$c["id"]])
                                                ? number_format($g[$c["id"]], 2)
                                                : '<span class="mut">Not yet posted</span>',
                                        ],
                                        q(
                                            "SELECT c.* FROM courses c JOIN student_courses sc ON sc.course_id=c.id
                                             WHERE sc.student_id=? AND sc.semester=? AND sc.school_year=? ORDER BY c.id",
                                            [$sid, "1st Semester", "2026-2027"],
                                        )->fetchAll(),
                                    ),
                                ) .
                                "</div>";
                            break;
                        case "docs": ?>
                <h1>Request a Document</h1>
                <p>Choose a document to request and follow its progress.</p>
                <div class="card">
                    <h3>Request Details</h3>
                    <form class="cb" method="post" action="?a=req"><?= csrfIn() ?>
                        <input type="hidden" name="p" value="docs">
                        <small>Document Type</small>
                        <div class="dts" role="radiogroup"><?php foreach (
                            q("SELECT * FROM document_types")->fetchAll()
                            as $d
                        ) {
                            echo "<label class='dt'><b>" .
                                e($d["name"]) .
                                "</b><input type='radio' name='type' value='{$d["id"]}' required><span>{$d["fee"]} pesos - 1 copy<br>ready in {$d["days"]} working " .
                                ($d["days"] > 1 ? "days" : "day") .
                                "</span></label>";
                        } ?></div>
                        <label for="pu" style="font-size:13px;color:var(--mut)">Purpose</label>
                        <textarea id="pu" name="purpose" rows="2" maxlength="200" required
                            placeholder="e.g. For Employment, For Scholarship"
                            style="border-color:var(--bd);margin:6px 0 14px"></textarea>
                        <div style="text-align:right">
                            <button class="btn s">Submit Request</button>
                        </div>
                    </form>
                </div>
                <div class="card">
                    <h3>My Requests</h3><?= tbl(
                            [
                                "REFERENCE",
                                "DOCUMENT",
                                "DATE REQUESTED",
                                "STATUS",
                            ],
                            array_map(
                                fn($r) => [
                                    ref($r["id"]),
                                    e($r["name"]),
                                    dt($r["created_at"]),
                                    badge($r["status"]),
                                ],
                                myReqs($sid),
                            ),
                        ) ?>
                </div>
                <?php break;
                        case "enroll":
                            $s = q(
                                "SELECT status FROM enrollments WHERE student_id=?",
                                [$sid],
                            )->fetchColumn(); ?>
                <h1>Enrollment</h1>
                <p>1st Semester, A.Y. 2026-2027</p>
                <div class="card">
                    <div class="cb">Status: <?= badge( $s ?: "Not enrolled", ) ?>
                        <p><?= $s == "Enrolled"
                              ? "You are officially enrolled. Your subjects are on the Courses page." 
                              : ($s == "Pending"
                                ? "Your enrollment is waiting for Registrar approval."
                                  : "Send your enrollment request to the Registrar.") ?></p>
                                  <?= $s? "": F("enroll", "enroll", "", "Request enrollment") ?>
                    </div>
                </div>
                <?php break;
                        case "clear":
                            $r = [];
                            foreach (clearance($sid) as $o => $st) {
                                $r[] = [$o, badge($st)];
                            }
                            echo '<h1>Clearance</h1><p>Each office must clear you before the Registrar releases your records.</p><div class="card">' .
                                tbl(["OFFICE", "STATUS"], $r) .
                                "</div>";
                            break;
                        case "rq":
                            need("registrar");
                            $rows = [];
                            foreach (
                                q(
                                    "SELECT r.*,t.name,u.name sname,(SELECT COUNT(*) FROM payments p WHERE p.request_id=r.id) paid FROM document_requests r JOIN document_types t ON t.id=r.type_id JOIN users u ON u.id=r.student_id ORDER BY r.id DESC",
                                )->fetchAll()
                                as $r
                            ) {
                                $o = "";
                                foreach (
                                    [
                                        "Pending",
                                        "Processing",
                                        "Ready",
                                        "Completed",
                                        "Rejected",
                                    ]
                                    as $s
                                ) {
                                    $o .=
                                        "<option" .
                                        ($s == $r["status"]
                                            ? " selected"
                                            : "") .
                                        ">$s</option>";
                                }
                                $rows[] = [
                                    ref($r["id"]),
                                    e($r["sname"]),
                                    e($r["name"]),
                                    dt($r["created_at"]),
                                    $r["paid"] ? "Yes" : "No",
                                    F(
                                        "status",
                                        "rq",
                                        "<input type='hidden' name='id' value='{$r["id"]}'><select name='status' aria-label='Status of " .
                                            ref($r["id"]) .
                                            "'>$o</select> ",
                                        "Update",
                                        "ghost",
                                    ),
                                ];
                            }
                            echo '<h1>Document Requests</h1><p>Update a request after the cashier records payment.</p><div class="card">' .
                                tbl(
                                    [
                                        "REFERENCE",
                                        "STUDENT",
                                        "DOCUMENT",
                                        "DATE",
                                        "PAID",
                                        "STATUS",
                                    ],
                                    $rows,
                                ) .
                                "</div>";
                            break;
                        case "ge":

                            need("registrar");
                            $studentNumber = trim($_GET["student_no"] ?? "");
                            $student = null;
                            if ($studentNumber !== "") {
                                $student = q(
                                    "SELECT u.id,u.name,u.username,s.program,s.year_level
                                     FROM users u JOIN students s ON s.user_id=u.id
                                     WHERE u.username=? AND u.role='student'",
                                    [$studentNumber],
                                )->fetch();
                            }
                            $id = (int) ($student["id"] ?? 0);
                            $g = $id ? q(
                                "SELECT g.course_id,g.grade FROM grades g
                                 JOIN student_courses sc ON sc.student_id=g.student_id AND sc.course_id=g.course_id
                                   AND sc.semester=? AND sc.school_year=?
                                 WHERE g.student_id=?",
                                ["1st Semester", "2026-2027", $id],
                            )->fetchAll(PDO::FETCH_KEY_PAIR) : [];
                            ?>
                <h1>Grade Encoding</h1>
                <p>Enter a student number to load their details and registered courses.</p>
                <div class="card">
                    <form class="cb" method="get">
                        <input type="hidden" name="p" value="ge">
                        <label for="student-no">Student number</label>
                        <div class="row"><div><input id="student-no" name="student_no" value="<?= e($studentNumber) ?>" inputmode="numeric" autocomplete="off" placeholder="e.g. 260001" required></div><div><button class="btn s">Find student</button></div></div>
                    </form>
                    <?php if ($studentNumber !== "" && !$student): ?>
                    <div class="cb" role="alert">No student was found with that student number.</div>
                    <?php elseif ($student): ?>
                    <div class="academic-summary">
                        <div><span>Student</span><strong><?= e($student["name"]) ?></strong></div>
                        <div><span>Student number</span><strong><?= e($student["username"]) ?></strong></div>
                        <div><span>Program and year level</span><strong><?= e($student["program"]) ?>, <?= e($student["year_level"]) ?></strong></div>
                    </div>
                    <form method="post" action="?a=grades"><?= csrfIn() ?>
                        <input type="hidden" name="p" value="ge">
                        <input type="hidden" name="sid" value="<?= $id ?>">
                        <?= tbl(
                                    ["COURSE", "SUBJECT", "GRADE"],
                                    array_map(
                                        fn($c) => [
                                            e($c["code"]),
                                            e($c["title"]),
                                            "<input name='g[{$c["id"]}]' value='" .
                                            (isset($g[$c["id"]])
                                                ? number_format($g[$c["id"]], 2)
                                                : "") .
                                            "' placeholder='e.g. 1.75' style='width:110px;padding:8px;border-color:var(--bd)'>",
                                        ],
                                        q(
                                            "SELECT c.* FROM courses c JOIN student_courses sc ON sc.course_id=c.id
                                             WHERE sc.student_id=? AND sc.semester=? AND sc.school_year=? ORDER BY c.id",
                                            [$id, "1st Semester", "2026-2027"],
                                        )->fetchAll(),
                                    ),
                                ) ?>
                        <?php if (!q("SELECT 1 FROM student_courses WHERE student_id=? AND semester=? AND school_year=? LIMIT 1", [$id, "1st Semester", "2026-2027"])->fetch()): ?>
                        <div class="cb">No courses are registered for this student in the current term.</div>
                        <?php else: ?>
                        <div class="cb" style="text-align:right"><button class="btn s">Save grades</button></div>
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </div>
                <?php break;
                        case "cr":
                            need("registrar");
                            $studentNumber = trim($_GET["student_no"] ?? "");
                            $student = null;
                            $isEnrolled = false;
                            if ($studentNumber !== "") {
                                $student = q(
                                    "SELECT u.id,u.name,u.username,s.program,s.year_level
                                     FROM users u JOIN students s ON s.user_id=u.id
                                     WHERE u.username=? AND u.role='student'",
                                    [$studentNumber],
                                )->fetch();
                                if ($student) {
                                    $isEnrolled = (bool) q(
                                        "SELECT 1 FROM enrollments WHERE student_id=? AND status='Enrolled'",
                                        [$student["id"]],
                                    )->fetch();
                                }
                            }
                            $studentId = $isEnrolled ? (int) $student["id"] : 0;
                            $availableCourses = [];
                            if ($studentId) {
                                $availableCourses = q(
                                    "SELECT c.id,c.code,c.title,c.units,cs.days,cs.start_time,cs.end_time,cs.room,cs.instructor,
                                            sc.course_id IS NOT NULL AS registered
                                     FROM courses c
                                     LEFT JOIN course_schedules cs ON cs.course_id=c.id
                                       AND cs.semester=? AND cs.school_year=?
                                     LEFT JOIN student_courses sc ON sc.course_id=c.id AND sc.student_id=?
                                       AND sc.semester=? AND sc.school_year=?
                                     ORDER BY c.code",
                                    ["1st Semester", "2026-2027", $studentId, "1st Semester", "2026-2027"],
                                )->fetchAll();
                            }
                            ?>
                <h1>Course Registration</h1>
                <p>Enter a student number to view and update their registered courses for 1st Semester, A.Y. 2026-2027.</p>
                <div class="card">
                    <form class="cb" method="get">
                        <input type="hidden" name="p" value="cr">
                        <label for="registration-student-number">Student number</label>
                        <div class="row"><div><input id="registration-student-number" name="student_no" value="<?= e($studentNumber) ?>" inputmode="numeric" autocomplete="off" placeholder="e.g. 260001" required></div><div><button class="btn s">Find student</button></div></div>
                    </form>
                    <?php if ($studentNumber !== "" && !$student): ?>
                    <div class="cb" role="alert">No student was found with that student number.</div>
                    <?php elseif ($student && !$isEnrolled): ?>
                    <div class="cb" role="alert"><?= e($student["name"]) ?> (<?= e($student["username"]) ?>) is not approved for enrollment yet.</div>
                    <?php elseif ($student): ?>
                    <div class="academic-summary">
                        <div><span>Student</span><strong><?= e($student["name"]) ?></strong></div>
                        <div><span>Student number</span><strong><?= e($student["username"]) ?></strong></div>
                        <div><span>Program and year level</span><strong><?= e($student["program"]) ?>, <?= e($student["year_level"]) ?></strong></div>
                    </div>
                    <form class="course-registration" method="post" action="?a=assign_courses">
                        <?= csrfIn() ?>
                        <input type="hidden" name="p" value="cr">
                        <input type="hidden" name="sid" value="<?= $studentId ?>">
                        <input type="hidden" name="student_no" value="<?= e($student["username"]) ?>">
                        <?php foreach ($availableCourses as $course):
                            $schedule = $course["days"]
                                ? e($course["days"]) . " · " . date("g:i A", strtotime($course["start_time"])) . "–" . date("g:i A", strtotime($course["end_time"])) . " · " . e($course["room"])
                                : "Schedule to be announced";
                        ?>
                        <label class="registration-course">
                            <input type="checkbox" name="courses[]" value="<?= (int) $course["id"] ?>" <?= $course["registered"] ? "checked" : "" ?>>
                            <span><strong><?= e($course["code"]) ?> · <?= e($course["title"]) ?></strong>
                                <small><?= (int) $course["units"] ?> units · <?= $schedule ?> · <?= e($course["instructor"] ?? "Instructor to be announced") ?></small>
                            </span>
                        </label>
                        <?php endforeach; ?>
                        <?php if (!$availableCourses): ?><div class="cb">No courses are available for registration.</div><?php endif; ?>
                        <div class="registration-actions">
                            <p>Saving replaces this student's course list for the selected term.</p>
                            <button class="btn s">Save registration</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <?php break;
                        case "en":
                            need("registrar");
                            $rows = [];
                            foreach (
                                q(
                                    "SELECT u.id,u.name,s.program,e.status FROM users u JOIN students s ON s.user_id=u.id LEFT JOIN enrollments e ON e.student_id=u.id WHERE u.role='student'",
                                )->fetchAll()
                                as $r
                            ) {
                                $rows[] = [
                                    e($r["name"]),
                                    e($r["program"]),
                                    badge($r["status"] ?: "Not enrolled"),
                                    $r["status"] == "Enrolled"
                                        ? "<a class='ghost' href='?p=cr&sid={$r["id"]}'>Manage courses</a>"
                                        : F(
                                            "enroll_ok",
                                            "en",
                                            "<input type='hidden' name='sid' value='{$r["id"]}'>",
                                            "Approve",
                                        ),
                                ];
                            }
                            echo '<h1>Enrollment</h1><p>Approve student enrollment requests.</p><div class="card">' .
                                tbl(
                                    ["STUDENT", "PROGRAM", "STATUS", "ACTION"],
                                    $rows,
                                ) .
                                "</div>";
                            break;
                        case "pay":
                            need("cashier");
                            $documentDueRows = [];
                            $documentDueTotal = 0;
                            $documentReceiptRows = [];
                            foreach (
                                q(
                                    "SELECT r.id,u.name,t.name dn,t.fee FROM document_requests r JOIN users u ON u.id=r.student_id JOIN document_types t ON t.id=r.type_id WHERE NOT EXISTS(SELECT 1 FROM payments p WHERE p.request_id=r.id) ORDER BY r.id",
                                )->fetchAll()
                                as $r
                            ) {
                                $documentDueTotal += (float) $r["fee"];
                                $documentDueRows[] = [
                                    ref($r["id"]),
                                    e($r["name"]),
                                    e($r["dn"]),
                                    money($r["fee"]),
                                    F(
                                        "pay",
                                        "pay|documents",
                                        "<input type='hidden' name='id' value='{$r["id"]}'>",
                                        "Record payment",
                                    ),
                                ];
                            }
                            foreach (
                                q(
                                    "SELECT p.*,u.name FROM payments p JOIN document_requests r ON r.id=p.request_id JOIN users u ON u.id=r.student_id ORDER BY p.id DESC",
                                )->fetchAll()
                                as $r
                            ) {
                                $documentReceiptRows[] = [
                                    e($r["receipt_no"]),
                                    e($r["name"]),
                                    ref($r["request_id"]),
                                    money($r["amount"]),
                                    dt($r["paid_at"]),
                                ];
                            }
                            $students = q(
                                "SELECT u.id,u.name,u.username,s.program,s.year_level
                                 FROM users u JOIN students s ON s.user_id=u.id
                                 WHERE u.role='student' AND u.active=1 ORDER BY u.name",
                            )->fetchAll();
                            $cashierTab = $_GET["tab"] ?? "enrollment";
                            if (!in_array($cashierTab, ["enrollment", "documents", "other"], true)) {
                                $cashierTab = "enrollment";
                            }
                            $feeDueRows = [];
                            foreach (
                                q(
                                    "SELECT f.id,f.category,f.description,f.amount,f.due_date,f.semester,f.school_year,
                                            u.name,u.username,s.program,s.year_level
                                     FROM student_fee_charges f
                                     JOIN users u ON u.id=f.student_id
                                     JOIN students s ON s.user_id=f.student_id
                                     WHERE f.status='Unpaid' ORDER BY f.due_date IS NULL,f.due_date,u.name,f.id",
                                )->fetchAll()
                                as $fee
                            ) {
                                $feeDueRows[] = [
                                    e($fee["name"]) . "<br><small>" . e($fee["username"]) . "</small>",
                                    e($fee["program"]) . "<br>" . e($fee["year_level"]),
                                    e($fee["semester"]) . "<br>" . e($fee["school_year"]),
                                    e($fee["category"]),
                                    e($fee["description"]),
                                    $fee["due_date"] ? dt($fee["due_date"]) : "?",
                                    money($fee["amount"]),
                                    F(
                                        "pay_fee",
                                        "pay|" . $cashierTab,
                                        "<input type='hidden' name='id' value='{$fee["id"]}'>",
                                        "Record payment",
                                    ),
                                ];
                            }
                            $feeReceiptRows = [];
                            foreach (
                                q(
                                    "SELECT f.receipt_no,f.category,f.description,f.amount,f.paid_at,f.semester,f.school_year,
                                            u.name,u.username,s.program,s.year_level
                                     FROM student_fee_charges f
                                     JOIN users u ON u.id=f.student_id
                                     JOIN students s ON s.user_id=f.student_id
                                     WHERE f.status='Paid' ORDER BY f.paid_at DESC,f.id DESC",
                                )->fetchAll()
                                as $fee
                            ) {
                                $feeReceiptRows[] = [
                                    e($fee["receipt_no"]),
                                    e($fee["name"]) . "<br><small>" . e($fee["username"]) . "</small>",
                                    e($fee["category"]) . " - " . e($fee["description"]),
                                    e($fee["program"]) . " - " . e($fee["year_level"]),
                                    e($fee["semester"]) . " - " . e($fee["school_year"]),
                                    money($fee["amount"]),
                                    dt($fee["paid_at"]),
                                ];
                            }
                            $feeTotals = q(
                                "SELECT COALESCE(SUM(CASE WHEN status='Unpaid' THEN amount ELSE 0 END),0) outstanding,
                                        COALESCE(SUM(CASE WHEN status='Paid' THEN amount ELSE 0 END),0) collected,
                                        SUM(status='Unpaid') unpaid_count
                                 FROM student_fee_charges",
                            )->fetch();
                            ?>
                <h1>Student Payments</h1>
                <p>Manage enrollment charges, document request payments, and other student fees.</p>
                <div class="academic-summary">
                    <div><span>Outstanding student fees | <?= (int) $feeTotals["unpaid_count"] ?> charges</span><strong><?= money($feeTotals["outstanding"]) ?></strong></div>
                    <div><span>Student fees collected</span><strong><?= money($feeTotals["collected"]) ?></strong></div>
                    <div><span>Unpaid document requests | <?= count($documentDueRows) ?></span><strong><?= money($documentDueTotal) ?></strong></div>
                </div>
                <nav class="cashier-tabs" aria-label="Cashier sections">
                    <a class="<?= $cashierTab === "enrollment" ? "active" : "" ?>" href="?p=pay&amp;tab=enrollment">Enrollment</a>
                    <a class="<?= $cashierTab === "documents" ? "active" : "" ?>" href="?p=pay&amp;tab=documents">Document Requests</a>
                    <a class="<?= $cashierTab === "other" ? "active" : "" ?>" href="?p=pay&amp;tab=other">Other Fees</a>
                </nav>
                <?php if ($cashierTab === "enrollment"): ?>
                <div class="card">
                    <h3>Add an enrollment charge</h3>
                    <?php if (!$students): ?>
                    <div class="cb">Create a student account before adding a fee.</div>
                    <?php else: ?>
                    <form class="cb" method="post" action="?a=add_fee">
                        <?= csrfIn() ?>
                        <input type="hidden" name="p" value="pay">
                        <input type="hidden" name="tab" value="enrollment">
                        <input type="hidden" name="category" value="Enrollment">
                        <div class="row">
                            <div><label for="enrollment-student-id">Student ID number</label><input id="enrollment-student-id" name="sid" inputmode="numeric" autocomplete="off" placeholder="e.g. 260001" required></div>
                            <div><label for="enrollment-description">Description</label><input id="enrollment-description" name="description" maxlength="120" placeholder="e.g. First semester enrollment fee" required></div>
                            <div><label for="enrollment-amount">Amount (PHP)</label><input id="enrollment-amount" name="amount" type="number" min="0.01" step="0.01" placeholder="0.00" required></div>
                            <div><label for="enrollment-due-date">Due date</label><input id="enrollment-due-date" name="due_date" type="date"></div>
                        </div>
                        <button class="btn s">Add enrollment charge</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card"><h3>Enrollment charges awaiting payment</h3><?= tbl(["STUDENT", "PROGRAM / YEAR", "TERM", "FEE TYPE", "DESCRIPTION", "DUE DATE", "AMOUNT", "ACTION"], array_values(array_filter($feeDueRows, fn($row) => $row[3] === "Enrollment"))) ?></div>
                <div class="card"><h3>Enrollment receipts</h3><?= tbl(["RECEIPT", "STUDENT", "FEE", "PROGRAM / YEAR", "TERM", "AMOUNT", "PAID DATE"], array_values(array_filter($feeReceiptRows, fn($row) => str_starts_with($row[2], "Enrollment")))) ?></div>
                <?php elseif ($cashierTab === "documents"): ?>
                <div class="card"><h3>Unpaid document requests</h3><?= tbl(["REFERENCE", "STUDENT", "DOCUMENT", "AMOUNT", "ACTION"], $documentDueRows) ?></div>
                <div class="card"><h3>Document receipts</h3><?= tbl(["RECEIPT NO.", "STUDENT", "REFERENCE", "AMOUNT", "DATE"], $documentReceiptRows) ?></div>
                <?php else: ?>
                <div class="card">
                    <h3>Add a laboratory or other fee</h3>
                    <?php if (!$students): ?>
                    <div class="cb">Create a student account before adding a fee.</div>
                    <?php else: ?>
                    <form class="cb" method="post" action="?a=add_fee">
                        <?= csrfIn() ?>
                        <input type="hidden" name="p" value="pay">
                        <input type="hidden" name="tab" value="other">
                        <div class="row">
                            <div><label for="other-student-id">Student ID number</label><input id="other-student-id" name="sid" inputmode="numeric" autocomplete="off" placeholder="e.g. 260001" required></div>
                            <div><label for="other-category">Fee type</label><select id="other-category" name="category" required><option value="Laboratory">Laboratory</option><option value="Other">Other</option></select></div>
                            <div><label for="other-description">Description</label><input id="other-description" name="description" maxlength="120" placeholder="e.g. Laboratory materials" required></div>
                            <div><label for="other-amount">Amount (PHP)</label><input id="other-amount" name="amount" type="number" min="0.01" step="0.01" placeholder="0.00" required></div>
                            <div><label for="other-due-date">Due date</label><input id="other-due-date" name="due_date" type="date"></div>
                        </div>
                        <button class="btn s">Add fee charge</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card"><h3>Laboratory and other charges awaiting payment</h3><?= tbl(["STUDENT", "PROGRAM / YEAR", "TERM", "FEE TYPE", "DESCRIPTION", "DUE DATE", "AMOUNT", "ACTION"], array_values(array_filter($feeDueRows, fn($row) => $row[3] !== "Enrollment"))) ?></div>
                <div class="card"><h3>Laboratory and other fee receipts</h3><?= tbl(["RECEIPT", "STUDENT", "FEE", "PROGRAM / YEAR", "TERM", "AMOUNT", "PAID DATE"], array_values(array_filter($feeReceiptRows, fn($row) => !str_starts_with($row[2], "Enrollment")))) ?></div>
                <?php endif; ?>
                <?php
                            break;
                        case "dc":
                            need(...array_keys(CLEARANCE_ROLE_OFFICES));
                            $office = clearanceOfficeForRole($u["role"]);
                            $search = trim($_GET["search"] ?? "");
                            $yearLevel = trim($_GET["year"] ?? "");
                            $where = ["u.role='student'"];
                            $params = [$office];
                            if ($search !== "") {
                                $where[] = "(u.name LIKE ? OR u.username LIKE ?)";
                                $params[] = "%" . $search . "%";
                                $params[] = "%" . $search . "%";
                            }
                            if ($yearLevel !== "") {
                                $where[] = "s.year_level=?";
                                $params[] = $yearLevel;
                            }
                            $yearLevels = q("SELECT DISTINCT year_level FROM students ORDER BY year_level")->fetchAll(PDO::FETCH_COLUMN);
                            $rows = [];
                            foreach (
                                q(
                                    "SELECT u.id,u.username,u.name,s.program,s.year_level,COALESCE(c.status,'Pending') AS clearance_status
                                     FROM users u
                                     JOIN students s ON s.user_id=u.id
                                     LEFT JOIN clearances c ON c.student_id=u.id AND c.office=?
                                     WHERE " . implode(" AND ", $where) . "
                                     ORDER BY s.program,s.year_level,u.name",
                                    $params,
                                )->fetchAll()
                                as $s
                            ) {
                                $status = $s["clearance_status"];
                                $rows[] = [
                                    e($s["name"]),
                                    e($s["username"]),
                                    e($s["program"]),
                                    e($s["year_level"]),
                                    badge($status),
                                    F(
                                        "clr",
                                        "dc",
                                        "<input type='hidden' name='sid' value='{$s["id"]}'><input type='hidden' name='office' value='" . e($office) . "'><input type='hidden' name='search' value='" . e($search) . "'><input type='hidden' name='year' value='" . e($yearLevel) . "'>",
                                        $status === "Cleared" ? "Mark pending" : "Clear student",
                                        "ghost",
                                    ),
                                ];
                            }
                            ?>
                <h1><?= e($office) ?> Clearance</h1>
                <div class="clearance-term-banner">
                    <span>Clearance period</span>
                    <strong><?= e(CURRENT_SEMESTER) ?></strong>
                    <strong>School Year <?= e(CURRENT_SCHOOL_YEAR) ?></strong>
                </div>
                <p>Search students by name or student number, then filter by year level.</p>
                <div class="card">
                    <form class="cb" method="get">
                        <input type="hidden" name="p" value="dc">
                        <div class="row">
                            <div>
                                <label for="clearance-search">Student name or number</label>
                                <input id="clearance-search" name="search" value="<?= e($search) ?>" placeholder="Search students" autocomplete="off">
                            </div>
                            <div>
                                <label for="clearance-year">Year level</label>
                                <select id="clearance-year" name="year">
                                    <option value="">All year levels</option> <?php foreach ($yearLevels as $level): ?>
                                    <option value="<?= e($level) ?>" <?= $yearLevel === $level ? "selected" : "" ?>><?= e($level) ?>
                                </option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <button class="btn s">Search and filter</button>
                             </div>
                        </div>
                    </form>
                    <?= tbl(["STUDENT", "STUDENT ID", "PROGRAM", "YEAR LEVEL", "STATUS", "ACTION"], $rows) ?>
                </div>
                <?php break;
                        case "ac": need("admin");
                            $rows = [];
                            foreach (
                                q("SELECT * FROM users ORDER BY id")->fetchAll() as $r
                            ) {
                                $rows[] = [
                                    e($r["username"]),
                                    e($r["name"]),
                                    $r["role"],
                                    badge(
                                        $r["active"] ? "Active" : "Deactivated",
                                    ),
                                    $r["id"] == $u["id"]
                                        ? "â€”"
                                        : F(
                                            "toggle",
                                            "ac",
                                            "<input type='hidden' name='id' value='{$r["id"]}'>",
                                            $r["active"]
                                                ? "Deactivate"
                                                : "Activate",
                                            "ghost",
                                        ),
                                ];
                            }
                            $ro = "";
                            foreach (
                                [
                                    "student",
                                    "registrar",
                                    "cashier",
                                    "dept",
                                    "library",
                                    "guidance",
                                    "clinic",
                                    "affairs",
                                    "lab",
                                    "property",
                                    "admin",
                                ]
                                as $x
                            ) {
                                $ro .= "<option>$x</option>";
                            }
                            $st = 'style="border-color:var(--bd)"';
                            ?>
                <h1>User Accounts</h1>
                <p>Create accounts and deactivate users who no longer need access.</p>
                <div class="card">
                    <h3>New account</h3>
                    <form class="cb" method="post" action="?a=adduser"><?= csrfIn() ?>
                        <input type="hidden" name="p" value="ac">
                        <div class="row">
                            <div>
                                <label for="ni">ID number</label>
                                <input id="ni" name="id" <?= $st ?> required>
                            </div>
                            <div>
                                <label for="nn">Full name</label>
                                <input id="nn" name="name" <?= $st ?> required>
                            </div>
                            <div>
                                <label for="nr">Role</label>
                                <select id="nr" name="role" <?= $st ?>><?= $ro ?></select>
                            </div>
                            <div>
                                <label for="np">Temporary password</label>
                                <input id="np" name="pw" type="password" minlength="6" <?= $st ?>
                                    required>
                            </div>
                        </div>
                        <button class="btn s">Create account</button>
                    </form>
                </div>
                <div class="card">
                <?= tbl(
                    [
                        "ID",
                        "NAME",
                        "ROLE",
                        "STATUS",
                        "ACTION",
                    ],
                    $rows,
                ) ?>
                </div>
                <?php } ?>
            </main>
        </div>
    </div>
    <?php endif; ?>
    <script src="js/script.js" defer></script>
</body>

</html>
