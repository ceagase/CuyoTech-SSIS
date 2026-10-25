<?php
function flash($m)
{
    $_SESSION["f"] = $m;
}
function go($p = "")
{
    $parts = explode("|", $p, 2);
    $location = "index.php?p=" . urlencode($parts[0]);
    if (isset($parts[1])) {
        parse_str($parts[1], $query);
        foreach (["tab", "student_no", "search", "year"] as $key) {
            if (isset($query[$key])) {
                $location .= "&" . $key . "=" . urlencode((string) $query[$key]);
            }
        }
    }
    header("Location: " . $location);
    exit();
}
function need(...$r)
{
    global $u;
    if (!$u || !in_array($u["role"], $r)) {
        http_response_code(403);
        exit("Access denied");
    }
}
const OFFICES = [
    "IT Department",
    "Library",
    "Registrar Office",
    "Cashier",
    "Guidance Office",
    "Clinic",
    "Student Affairs",
    "Laboratory",
    "Property Office",
];
const CLEARANCE_ROLE_OFFICES = [
    "dept" => "IT Department",
    "library" => "Library",
    "registrar" => "Registrar Office",
    "cashier" => "Cashier",
    "guidance" => "Guidance Office",
    "clinic" => "Clinic",
    "affairs" => "Student Affairs",
    "lab" => "Laboratory",
    "property" => "Property Office",
];
const CURRENT_SEMESTER = "1st Semester";
const CURRENT_SCHOOL_YEAR = "2026-2027";

function clearanceOfficeForRole($role)
{
    return CLEARANCE_ROLE_OFFICES[$role] ?? null;
}
function money($amount)
{
    return "&#8369;" . number_format((float) $amount, 2);
}


/* ---------- helpers for views ---------- */
function badge($s)
{
    $c =
        [
            "Ready" => "ok",
            "Cleared" => "ok",
            "Enrolled" => "ok",
            "Completed" => "ok",
            "Active" => "ok",
            "Processing" => "dk",
        ][$s] ?? "";
    return "<span class='b $c'>" . e($s) . "</span>";
}
function tbl($h, $r)
{
    $o = '<div class="tw"><table><tr>';
    foreach ($h as $x) {
        $o .= "<th>$x</th>";
    }
    $o .= "</tr>";
    foreach ($r as $w) {
        $o .= "<tr><td>" . implode("</td><td>", $w) . "</td></tr>";
    }
    if (!$r) {
        $o .=
            '<tr><td colspan="' .
            count($h) .
            '" class="mut">Nothing here yet.</td></tr>';
    }
    return $o . "</table></div>";
}
function F($a, $p, $in, $btn, $c = "btn s")
{
    return "<form method='post' action='?a=$a' style='display:inline'><input type='hidden' name='t' value='{$_SESSION["t"]}'><input type='hidden' name='p' value='$p'>$in<button class='$c'>$btn</button></form>";
}
function ref($id)
{
    return "REQ-" . (26100 + $id);
}
function dt($s)
{
    return date("M j, Y", strtotime($s));
}
function clearance($sid)
{
    $m = q("SELECT office,status FROM clearances WHERE student_id=?", [
        $sid,
    ])->fetchAll(PDO::FETCH_KEY_PAIR);
    $o = [];
    foreach (OFFICES as $x) {
        $o[$x] = $m[$x] ?? "Pending";
    }
    return $o;
}
function courseTbl($studentId)
{
    $c = q(
        "SELECT c.* FROM courses c JOIN student_courses sc ON sc.course_id=c.id
         WHERE sc.student_id=? AND sc.semester=? AND sc.school_year=? ORDER BY c.id",
        [$studentId, "1st Semester", "2026-2027"],
    )->fetchAll();
    if (!$c) {
        return '<div class="cb mut">No courses have been registered for this term yet.</div>';
    }
    $t = 0;
    $r = array_map(function ($x) use (&$t) {
        $t += $x["units"];
        return [e($x["code"]), e($x["title"]), $x["units"]];
    }, $c);
    return tbl(["COURSE CODE", "SUBJECT", "UNITS"], $r) .
        "<div style='text-align:right;padding:10px 22px;border-top:1px solid var(--bd)'>Total Units: <b>$t</b></div>";
}
function courseScheduleTbl($studentId)
{
    $courses = q(
        "SELECT c.code,c.title,c.units,cs.days,cs.start_time,cs.end_time,cs.room,cs.instructor
         FROM courses c
         JOIN student_courses sc
           ON sc.course_id=c.id AND sc.student_id=? AND sc.semester=? AND sc.school_year=?
         LEFT JOIN course_schedules cs
           ON cs.course_id=c.id AND cs.semester=sc.semester AND cs.school_year=sc.school_year
         ORDER BY FIELD(cs.days,'Monday / Wednesday','Tuesday / Thursday','Friday'),cs.start_time,c.code",
        [$studentId, "1st Semester", "2026-2027"],
    )->fetchAll();
    if (!$courses) {
        return '<div class="cb mut">No courses have been registered for this term yet. The Registrar can add courses after enrollment is approved.</div>';
    }
    $totalUnits = 0;
    $rows = [];
    foreach ($courses as $course) {
        $totalUnits += (int) $course["units"];
        $time = $course["start_time"]
            ? date("g:i A", strtotime($course["start_time"])) .
                " – " .
                date("g:i A", strtotime($course["end_time"]))
            : "Not scheduled";
        $rows[] = [
            e($course["code"]),
            e($course["title"]),
            (int) $course["units"],
            e($course["days"] ?? "To be announced"),
            e($time),
            e($course["room"] ?? "To be announced"),
            e($course["instructor"] ?? "To be announced"),
        ];
    }
    return tbl(
        ["COURSE CODE", "COURSE", "UNITS", "DAYS", "TIME", "ROOM", "INSTRUCTOR"],
        $rows,
    ) .
        "<div class='summary-line'>" .
        count($courses) .
        " courses <span>Total registered units: <b>" .
        $totalUnits .
        "</b></span></div>";
}
function myReqs($sid)
{
    return q(
        "SELECT r.*,t.name,t.fee,t.days,(SELECT COUNT(*) FROM payments p WHERE p.request_id=r.id) paid FROM document_requests r JOIN document_types t ON t.id=r.type_id WHERE r.student_id=? ORDER BY r.id DESC",
        [$sid],
    )->fetchAll();
}
function csrfIn()
{
    return "<input type='hidden' name='t' value='{$_SESSION["t"]}'>";
}

