<?php
require_once __DIR__ . '/../config/database.php';

// Filter params
$f_search = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$f_year = isset($_GET['academic_year']) ? trim($_GET['academic_year']) : '';
$f_gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$f_gpa = isset($_GET['gpa_range']) ? trim($_GET['gpa_range']) : '';

// Build dynamic WHERE and HAVING clauses
$where_clauses = ["1=1"];
$params = [];
$types = "";

if ($f_search !== '') {
    $where_clauses[] = "s.student_code LIKE ?";
    $params[] = $f_search . '%';
    $types .= "s";
}
if ($f_gender !== '') {
    $where_clauses[] = "s.gender = ?";
    $params[] = $f_gender;
    $types .= "s";
}

$where_sql = implode(" AND ", $where_clauses);

// 1. Total students (need to join grades if filtering by year)
// If year is selected, only count students who have records in that year
if ($f_year !== '') {
    $total_students_query = "SELECT COUNT(DISTINCT s.id) as total FROM students s JOIN grades g ON s.id = g.student_id WHERE $where_sql AND g.academic_year = ?";
    $total_params = $params;
    $total_params[] = $f_year;
    $total_types = $types . "s";
} else {
    $total_students_query = "SELECT COUNT(DISTINCT s.id) as total FROM students s WHERE $where_sql";
    $total_params = $params;
    $total_types = $types;
}

$stmt = $conn->prepare($total_students_query);
if (!empty($total_params)) {
    $stmt->bind_param($total_types, ...$total_params);
}
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 2. Average GPA and Pass/Fail (only considering filtered students)
// We calculate each student's overall GPA first, then average those, or filter by year.
$grades_where = $where_sql;
if ($f_year !== '') {
    $grades_where .= " AND g.academic_year = ?";
}

// Subquery to get average GPA per student
$student_gpas_query = "
    SELECT s.id, AVG(g.average_score) as student_gpa
    FROM students s
    JOIN grades g ON s.id = g.student_id
    WHERE $grades_where
    GROUP BY s.id
";

// If there's a GPA range filter, we apply it as a HAVING clause
$having_clause = "";
if ($f_gpa === 'excellent') $having_clause = " HAVING student_gpa >= 8.0";
elseif ($f_gpa === 'good') $having_clause = " HAVING student_gpa >= 6.5 AND student_gpa < 8.0";
elseif ($f_gpa === 'average') $having_clause = " HAVING student_gpa >= 5.0 AND student_gpa < 6.5";
elseif ($f_gpa === 'weak') $having_clause = " HAVING student_gpa < 5.0";

$student_gpas_query .= $having_clause;

$stmt = $conn->prepare($student_gpas_query);
if ($f_year !== '') {
    $g_params = $params;
    $g_params[] = $f_year;
    $g_types = $types . "s";
    $stmt->bind_param($g_types, ...$g_params);
} elseif (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$total_gpa_sum = 0;
$valid_students_count = 0;
$pass_count = 0;
$fail_count = 0;

while ($row = $res->fetch_assoc()) {
    $gpa = (float)$row['student_gpa'];
    $total_gpa_sum += $gpa;
    $valid_students_count++;
    if ($gpa >= 5.0) {
        $pass_count++;
    } else {
        $fail_count++;
    }
}
$stmt->close();

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
    header('Content-Type: application/json');
    echo json_encode($summary);
}
?>