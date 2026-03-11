<?php
/**
 * Guest Middleware
 * Chặn người dùng đã đăng nhập quay lại trang login/register.
 */
function handleGuest() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user'])) {
        // Nếu đã đăng nhập thì chuyển hướng về trang dashboard hoặc home
        header("Location: /index.php");
        exit;
    }
}
?>
