<?php
session_start();

define('BASE_PATH', '../');
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
require_once __DIR__ . '/../config/database.php';

handleAuth();
checkRole(['admin']);

$studentId = intval($_GET['id'] ?? 0);
if ($studentId > 0) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE students SET is_active = 0 WHERE id = ?');
        $stmt->execute([$studentId]);

        $userStmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE student_id = ?');
        $userStmt->execute([$studentId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

header('Location: ' . BASE_PATH . 'students/list.php?deleted=1');
exit();
?>