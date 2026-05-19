<?php
session_start();

define('BASE_PATH', '../');
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';

handleAuth();
checkRole(['admin']);

$subjectId = intval($_GET['id'] ?? 0);
if ($subjectId > 0) {
    $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ?');
    $stmt->execute([$subjectId]);
}

header('Location: ' . BASE_PATH . 'subjects/list.php?deleted=1');
exit();
?>