<?php
require_once __DIR__ . '/../config/database.php';

// Pass/Fail distribution
$pass_fail_query = "SELECT 
    SUM(CASE WHEN average_score >= 5.0 THEN 1 ELSE 0 END) as pass,
    SUM(CASE WHEN average_score < 5.0 THEN 1 ELSE 0 END) as fail
    FROM grades";
$pass_fail_result = $conn->query($pass_fail_query);
$pass_fail_data = $pass_fail_result->fetch_assoc();

// Grade distribution
$grade_dist_query = "SELECT letter_grade, COUNT(*) as count FROM grades GROUP BY letter_grade ORDER BY FIELD(letter_grade, 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F')";
$grade_dist_result = $conn->query($grade_dist_query);
$grade_dist = [];
while ($row = $grade_dist_result->fetch_assoc()) {
    $grade_dist[$row['letter_grade']] = (int)$row['count'];
}

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