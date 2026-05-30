<?php
require_once __DIR__ . '/../config/database.php';

// Filter params
$f_search = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$f_year = isset($_GET['academic_year']) ? trim($_GET['academic_year']) : '';
$f_gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$f_gpa = isset($_GET['gpa_range']) ? trim($_GET['gpa_range']) : '';
$f_entry_year = isset($_GET['entry_year']) ? trim($_GET['entry_year']) : '';
$f_class_id = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';

// Build dynamic WHERE clause
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
if ($f_year !== '') {
    $where_clauses[] = "g.academic_year = ?";
    $params[] = $f_year;
    $types .= "s";
}
if ($f_entry_year !== '') {
    $where_clauses[] = "SUBSTRING(s.student_code, 1, 4) = ?";
    $params[] = $f_entry_year;
    $types .= "s";
}
if ($f_class_id !== '') {
    $where_clauses[] = "s.class_id = ?";
    $params[] = $f_class_id;
    $types .= "i";
}

$where_sql = implode(" AND ", $where_clauses);

// Note: Chart data operates on individual GRADE records, not the aggregated student GPA.
// So for the charts, we just joined students and grades and apply the filters.
// If there's a gpa_range filter, we must compute student GPA first, and only include those students' grades.
$join_clause = "FROM grades g JOIN students s ON g.student_id = s.id";

if ($f_gpa !== '') {
    // We only want grades of students who meet the overall GPA filter
    $having_clause = "";
    if ($f_gpa === 'excellent') $having_clause = " >= 8.0";
    elseif ($f_gpa === 'good') $having_clause = " >= 6.5 AND avg_gpa < 8.0";
    elseif ($f_gpa === 'average') $having_clause = " >= 5.0 AND avg_gpa < 6.5";
    elseif ($f_gpa === 'weak') $having_clause = " < 5.0";

    $join_clause = "
        FROM grades g 
        JOIN students s ON g.student_id = s.id
        JOIN (
            SELECT student_id, AVG(average_score) as avg_gpa 
            FROM grades 
            GROUP BY student_id 
            HAVING avg_gpa $having_clause
        ) as valid_s ON s.id = valid_s.student_id
    ";
}

// Pass/Fail distribution
$pass_fail_query = "SELECT 
    SUM(CASE WHEN g.average_score >= 5.0 THEN 1 ELSE 0 END) as pass,
    SUM(CASE WHEN g.average_score < 5.0 THEN 1 ELSE 0 END) as fail
    $join_clause WHERE $where_sql";

$stmt = $conn->prepare($pass_fail_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pass_fail_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pass_count = $pass_fail_data['pass'] ?? 0;
$fail_count = $pass_fail_data['fail'] ?? 0;

// Grade distribution
$grade_dist_query = "SELECT g.letter_grade, COUNT(*) as count 
    $join_clause 
    WHERE $where_sql 
    GROUP BY g.letter_grade 
    ORDER BY FIELD(g.letter_grade, 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F')";

$stmt = $conn->prepare($grade_dist_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$grade_dist_result = $stmt->get_result();
$grade_dist = [];
while ($row = $grade_dist_result->fetch_assoc()) {
    if ($row['letter_grade']) {
        $grade_dist[$row['letter_grade']] = (int)$row['count'];
    }
}
$stmt->close();

$data = [
    'pass_fail' => [
        'labels' => ['Đạt (>= 5.0)', 'Trượt (< 5.0)'],
        'data' => [(int)$pass_fail_data['pass'], (int)$pass_fail_data['fail']]
    ],
    'grade_distribution' => [
        'labels' => array_keys($grade_dist),
        'data' => array_values($grade_dist)
    ]
];

header('Content-Type: application/json');
echo json_encode($data);
?>