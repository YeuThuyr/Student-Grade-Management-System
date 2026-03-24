<?php
require_once __DIR__ . '/../config/database.php';

// Total students
$total_students_query = "SELECT COUNT(*) as total FROM students";
$total_students_result = $conn->query($total_students_query);
$total_students = $total_students_result->fetch_assoc()['total'];

// Average GPA across all grades
$avg_gpa_query = "SELECT AVG(average_score) as avg_gpa FROM grades";
$avg_gpa_result = $conn->query($avg_gpa_query);
$avg_gpa = $avg_gpa_result->fetch_assoc()['avg_gpa'] ?? 0;

// Pass/Fail counts (Threshold 5.0)
$pass_count_query = "SELECT COUNT(*) as pass_count FROM grades WHERE average_score >= 5.0";
$pass_count_result = $conn->query($pass_count_query);
$pass_count = $pass_count_result->fetch_assoc()['pass_count'];

$fail_count_query = "SELECT COUNT(*) as fail_count FROM grades WHERE average_score < 5.0";
$fail_count_result = $conn->query($fail_count_query);
$fail_count = $fail_count_result->fetch_assoc()['fail_count'];

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