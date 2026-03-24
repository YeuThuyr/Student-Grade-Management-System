<?php
/**
 * Auth Middleware
 * Chặn người dùng chưa đăng nhập truy cập các trang cần xác thực
 */

function handleAuth() {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {

        // Detect API request
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        $isApi =
            str_contains($uri, '/api/') ||
            str_contains($accept, 'application/json');

        if ($isApi) {

            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized. Vui lòng đăng nhập để tiếp tục."
            ]);

            exit;
        }

        // Redirect web request
        header("Location: /grade_management/auth/login.php");
        exit;
    }
}