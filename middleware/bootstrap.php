<?php
/**
 * Bootstrap Middleware
 * Dùng để thiết lập nền tảng chung cho ứng dụng (khởi chạy session an toàn, 
 * nạp tự động các hàm middleware, chặn lỗi, v.v.)
 */

// Đảm bảo session luôn được khởi động một lần duy nhất
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nạp sẵn toàn bộ các module middleware để có thể gọi ở bất kì đâu
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/guest.php';
require_once __DIR__ . '/role.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/cors.php';

// ---------------------------------------------------------
// Middleware chạy mặc định (Global Middleware)
// ---------------------------------------------------------

// Bạn có thể bật Rate Limit mặc định cho tất cả các trang tại đây để chống DDoS/Spam
// handleRateLimit(60, 60);

?>
