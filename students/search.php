<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
handleAuth();
require_once __DIR__ . '/../config/database.php';

$search_code = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$students = [];

if ($search_code !== '') {
    // Use prepared statement with LIKE prefix match to prevent SQL injection
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

    $stmt = $conn->prepare("
        SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone,
               ROUND(SUM(($gradePointSql) * subj.credit) / SUM(subj.credit), 2) as gpa
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id
        LEFT JOIN subjects subj ON g.subject_id = subj.id
        WHERE s.student_code LIKE ?
        GROUP BY s.id
        ORDER BY s.student_code ASC
    ");
    $like_param = $search_code . '%';
    $stmt->bind_param("s", $like_param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// Return JSON for potential AJAX use, or redirect back to dashboard
header('Content-Type: application/json');
echo json_encode([
    'search_code' => $search_code,
    'count' => count($students),
    'students' => $students
]);
?>
