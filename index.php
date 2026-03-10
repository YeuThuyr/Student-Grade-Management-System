<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-card">
        <div style="text-align: center; margin-top: 1.5rem;">
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">This is your dashboard placeholder.</p>
            <a href="auth/logout.php" class="logout-btn" style="text-decoration: none;">Sign Out</a>
        </div>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> Grade Management System
        </div>
    </div>
</body>
</html>
