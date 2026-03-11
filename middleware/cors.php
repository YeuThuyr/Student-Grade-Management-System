<?php
/**
 * CORS Middleware
 * Cho phép các domain khác gọi API (Cross-Origin Resource Sharing)
 * Chỉ dùng cho các route API, không áp dụng tự động cho toàn bộ web thường
 */
function handleCors() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
    // Xử lý preflight request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
?>
