<?php
/**
 * Rate Limiting Middleware
 * Chặn spam API bằng cách giới hạn số yêu cầu trong một khoảng thời gian.
 *
 * @param int $limit Số yêu cầu tối đa cho phép
 * @param int $timeWindow Khoảng thời gian (tính bằng giây)
 */
function handleRateLimit($limit = 60, $timeWindow = 60) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $currentTime = time();
    $key = "rate_limit_" . $ip;

    if (!isset($_SESSION[$key])) {
        // Lần đầu tiên gọi API
        $_SESSION[$key] = [
            'count' => 1,
            'start_time' => $currentTime
        ];
        return;
    }

    $timePassed = $currentTime - $_SESSION[$key]['start_time'];

    if ($timePassed < $timeWindow) {
        if ($_SESSION[$key]['count'] >= $limit) {
            http_response_code(429); // 429 Too Many Requests
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "error", 
                "message" => "Too Many Requests. Cảnh báo spam API! Vui lòng thử lại sau " . ($timeWindow - $timePassed) . " giây."
            ]);
            exit;
        } else {
            $_SESSION[$key]['count']++;
        }
    } else {
        // Đã qua khoảng thời gian, reset lại chu kỳ đếm
        $_SESSION[$key] = [
            'count' => 1,
            'start_time' => $currentTime
        ];
    }
}
?>
