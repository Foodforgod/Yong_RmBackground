<?php
function json_response(array $data, int $statusCode = 200): void {
    header_remove();
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function write_log(string $message, string $level = 'INFO'): void {
    $logFile = LOGS_PATH . '/' . date('Y-m-d') . '.log';
    $entry = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
    @error_log($entry, 3, $logFile);
}