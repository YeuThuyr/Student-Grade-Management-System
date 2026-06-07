<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../middleware/rate_limit.php';
// Limit clients to 60 requests per minute
handleRateLimit(60, 60);

// Release session lock early to support high concurrency (SYS-NFR-02)
session_write_close();

require_once __DIR__ . '/../config/database.php';

$gradePointSql = "
    CASE g.letter_grade
        WHEN 'A+' THEN 4.0
        WHEN 'A' THEN 3.7
        WHEN 'B+' THEN 3.5
        WHEN 'B' THEN 3.0
        WHEN 'C+' THEN 2.5
        WHEN 'C' THEN 2.0
        WHEN 'D' THEN 1.0
        ELSE 0.0
    END
";

// Filter params
$f_search = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$f_year = isset($_GET['academic_year']) ? trim($_GET['academic_year']) : '';
$f_gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$f_gpa = isset($_GET['gpa_range']) ? trim($_GET['gpa_range']) : '';
$f_entry_year = isset($_GET['entry_year']) ? trim($_GET['entry_year']) : '';
$f_class_id = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';

// Build dynamic WHERE and HAVING clauses
$where_clauses = ["1=1"];
$params = [];

if ($f_search !== '') {
    $where_clauses[] = "s.student_code LIKE ?";
    $params[] = $f_search . '%';
}
if ($f_gender !== '') {
    $where_clauses[] = "s.gender = ?";
    $params[] = $f_gender;
}
if ($f_entry_year !== '') {
    $where_clauses[] = "SUBSTRING(s.student_code, 1, 4) = ?";
    $params[] = $f_entry_year;
}
if ($f_class_id !== '') {
    $where_clauses[] = "s.class_id = ?";
    $params[] = $f_class_id;
}

$where_sql = implode(" AND ", $where_clauses);

// 1. Total students (need to join grades if filtering by year)
if ($f_year !== '') {
    $total_students_query = "SELECT COUNT(DISTINCT s.id) as total FROM students s JOIN grades g ON s.id = g.student_id WHERE $where_sql AND g.academic_year = ?";
    $total_params = $params;
    $total_params[] = $f_year;
} else {
    $total_students_query = "SELECT COUNT(DISTINCT s.id) as total FROM students s WHERE $where_sql";
    $total_params = $params;
}

$stmt = $pdo->prepare($total_students_query);
$stmt->execute($total_params);
$total_students = (int)$stmt->fetchColumn();

// 2. Average GPA and Pass/Fail (only considering filtered students)
$grades_where = $where_sql;
$grades_params = $params;
if ($f_year !== '') {
    $grades_where .= " AND g.academic_year = ?";
    $grades_params[] = $f_year;
}

// Subquery to get credit-weighted GPA per student
$student_gpas_query = "
    SELECT s.id, SUM(($gradePointSql) * sub.credit) / SUM(sub.credit) as student_gpa
    FROM students s
    JOIN grades g ON s.id = g.student_id
    JOIN subjects sub ON g.subject_id = sub.id
    WHERE $grades_where
    GROUP BY s.id
";

// If there's a GPA range filter, we apply it as a HAVING clause
$having_clause = "";
if ($f_gpa === 'excellent') $having_clause = " HAVING student_gpa >= 3.6";
elseif ($f_gpa === 'good') $having_clause = " HAVING student_gpa >= 3.0 AND student_gpa < 3.6";
elseif ($f_gpa === 'average') $having_clause = " HAVING student_gpa >= 2.0 AND student_gpa < 3.0";
elseif ($f_gpa === 'weak') $having_clause = " HAVING student_gpa < 2.0";

$student_gpas_query .= $having_clause;

$stmt = $pdo->prepare($student_gpas_query);
$stmt->execute($grades_params);
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_gpa_sum = 0;
$valid_students_count = 0;
$pass_count = 0;
$fail_count = 0;

foreach ($res as $row) {
    $gpa = (float)$row['student_gpa'];
    $total_gpa_sum += $gpa;
    $valid_students_count++;
    if ($gpa >= 1.0) {
        $pass_count++;
    } else {
        $fail_count++;
    }
}

// Recalculate total students if GPA filter is applied
if ($f_gpa !== '') {
    $total_students = $valid_students_count;
}

$avg_gpa = $valid_students_count > 0 ? ($total_gpa_sum / $valid_students_count) : 0;
$pass_rate = ($pass_count + $fail_count) > 0 ? round(($pass_count / ($pass_count + $fail_count)) * 100, 2) : 0;

$summary = [
    'total_students' => (int)$total_students,
    'average_gpa' => round((float)$avg_gpa, 2),
    'pass_count' => (int)$pass_count,
    'fail_count' => (int)$fail_count,
    'pass_rate' => $pass_rate
];

if (basename($_SERVER['PHP_SELF']) == 'summary.php') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($summary);
}
