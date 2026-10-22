<?php
function check_rate_limit(): bool {
    $config = require APP_ROOT . '/config/bootstrap.php';
    $limit = $config['security']['rate_limit_max_requests'];
    $window = $config['security']['rate_limit_window'];
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $filename = RATE_LIMIT_PATH . '/' . md5($ip) . '.json';
    
    $now = time();
    $data = ['count' => 0, 'reset_time' => $now + $window];
    
    if (file_exists($filename)) {
        $content = @file_get_contents($filename);
        if ($content) {
            $decoded = json_decode($content, true);
            if ($decoded && isset($decoded['count'], $decoded['reset_time'])) {
                if ($now < $decoded['reset_time']) {
                    $data = $decoded;
                }
            }
        }
    }
    
    if ($now >= $data['reset_time']) {
        $data['count'] = 1;
        $data['reset_time'] = $now + $window;
    } else {
        $data['count']++;
    }
    
    @file_put_contents($filename, json_encode($data), LOCK_EX);
    
    return $data['count'] <= $limit;
}