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

if ($f_search !== '') {
    $where_clauses[] = "s.student_code LIKE ?";
    $params[] = $f_search . '%';
}
if ($f_gender !== '') {
    $where_clauses[] = "s.gender = ?";
    $params[] = $f_gender;
}
if ($f_year !== '') {
    $where_clauses[] = "g.academic_year = ?";
    $params[] = $f_year;
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

// Note: Chart data operates on individual GRADE records, not the aggregated student GPA.
$join_clause = "FROM grades g JOIN students s ON g.student_id = s.id";

if ($f_gpa !== '') {
    $having_clause = "";
    if ($f_gpa === 'excellent') $having_clause = " >= 8.0";
    elseif ($f_gpa === 'good') $having_clause = " >= 6.5 AND avg_gpa < 8.0";
    elseif ($f_gpa === 'average') $having_clause = " >= 5.0 AND avg_gpa < 6.5";
    elseif ($f_gpa === 'weak') $having_clause = " < 5.0";

    $join_clause = "
        FROM grades g 
        JOIN students s ON g.student_id = s.id
        JOIN (
            SELECT student_id, SUM(grades.average_score * sub.credit) / SUM(sub.credit) as avg_gpa 
            FROM grades 
            JOIN subjects sub ON grades.subject_id = sub.id
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

$stmt = $pdo->prepare($pass_fail_query);
$stmt->execute($params);
$pass_fail_data = $stmt->fetch(PDO::FETCH_ASSOC);

$pass_count = $pass_fail_data['pass'] ?? 0;
$fail_count = $pass_fail_data['fail'] ?? 0;

// Grade distribution
$grade_dist_query = "SELECT g.letter_grade, COUNT(*) as count 
    $join_clause 
    WHERE $where_sql 
    GROUP BY g.letter_grade 
    ORDER BY FIELD(g.letter_grade, 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F')";

$stmt = $pdo->prepare($grade_dist_query);
$stmt->execute($params);
$grade_dist_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize all letter grades with 0 count to ensure clean layout
$grade_dist = [
    'A+' => 0, 'A' => 0, 'B+' => 0, 'B' => 0, 'C+' => 0, 'C' => 0, 'D' => 0, 'F' => 0
];
foreach ($grade_dist_rows as $row) {
    if ($row['letter_grade'] && isset($grade_dist[$row['letter_grade']])) {
        $grade_dist[$row['letter_grade']] = (int)$row['count'];
    }
}

$data = [
    'pass_fail' => [
        'labels' => ['Đạt (>= 5.0)', 'Trượt (< 5.0)'],
        'data' => [(int)$pass_count, (int)$fail_count]
    ],
    'grade_distribution' => [
        'labels' => array_keys($grade_dist),
        'data' => array_values($grade_dist)
    ]
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);