<?php
require_once __DIR__ . '/config/database.php';
$years = [];
$res = $conn->query("SELECT DISTINCT academic_year FROM grades WHERE academic_year IS NOT NULL ORDER BY academic_year DESC");
while ($row = $res->fetch_assoc()) $years[] = $row['academic_year'];

$genders = [];
$res = $conn->query("SELECT DISTINCT gender FROM students WHERE gender IS NOT NULL");
while ($row = $res->fetch_assoc()) $genders[] = $row['gender'];

header('Content-Type: application/json');
echo json_encode(['years' => $years, 'genders' => $genders]);
?>
