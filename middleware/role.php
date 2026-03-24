<?php
/**
 * Role-Based Access Control Middleware
 * Kiểm tra xem người dùng đã đăng nhập có đúng loại quyền (admin/teacher/student) hay không.
 * Nếu chưa đăng nhập, sử dụng auth.php trước hoặc gọi kiểm tra chung ở đây.
 *
 * @param array $allowedRoles Mảng các role được phép thao tác. VD: ['admin', 'teacher']
 */
function checkRole($allowedRoles = []) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        $isApi = strpos($_SERVER['REQUEST_URI'], '/api/') !== false || 
                 (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
                 
        if ($isApi) {
            http_response_code(401); // 401 Unauthorized
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Unauthorized. Vui lòng đăng nhập để tiếp tục."]);
            exit;
        } else {
            header("Location: /auth/login.php");
            exit;
        }
    }

    $userRole = $_SESSION['user']['role'] ?? '';

    if (!empty($allowedRoles)) {
        if (!in_array($userRole, $allowedRoles)) {
            $isApi = strpos($_SERVER['REQUEST_URI'], '/api/') !== false || 
                     (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
                     
            if ($isApi) {
                http_response_code(403); // 403 Forbidden
                header('Content-Type: application/json');
                echo json_encode(["status" => "error", "message" => "Forbidden. Tài khoản của bạn ($userRole) không có quyền truy cập chức năng này."]);
                exit;
            } else {
                // Hiển thị lỗi hoặc chuyển hướng đến trang từ chối truy cập
                http_response_code(403);
                die("<h3>403 Forbidden</h3><p>Tài khoản của bạn (" . htmlspecialchars($userRole) . ") không có quyền truy cập trang này.</p>");
            }
        }
    }
}
?>
