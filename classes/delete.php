<?php
session_start();

define('BASE_PATH', '../');
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';

handleAuth();
checkRole(['admin']);

$classId = intval($_GET['id'] ?? 0);
if ($classId > 0) {
    $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ?');
    $stmt->execute([$classId]);
}

header('Location: ' . BASE_PATH . 'classes/list.php?deleted=1');
exit();
?>