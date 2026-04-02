<?php
session_start();
require_once __DIR__ . '/../middleware/auth.php';
handleAuth();
require_once __DIR__ . '/../config/database.php';

$search_code = isset($_GET['student_code']) ? trim($_GET['student_code']) : '';
$students = [];

if ($search_code !== '') {
    // Use prepared statement with LIKE prefix match to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT s.id, s.student_code, s.full_name, s.date_of_birth, s.gender, s.email, s.phone,
               ROUND(AVG(g.average_score), 2) as gpa
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id
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
