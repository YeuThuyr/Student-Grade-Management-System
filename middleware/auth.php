<?php
/**
 * Auth Middleware
 * Chặn người dùng chưa đăng nhập truy cập các trang cần xác thực
 */

function handleAuth()
{

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../config/env.php';

    if (!isset($_SESSION['user_id'])) {

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        $isApi =
            strpos($uri, '/api/') !== false ||
            strpos($accept, 'application/json') !== false;

        if ($isApi) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized. Vui lòng đăng nhập để tiếp tục."
            ]);
            exit;
        }

        header("Location: " . BASE_PATH . "auth/login.php");
        exit;
    }
}
