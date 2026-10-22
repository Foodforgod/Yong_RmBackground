<?php
/**
 * Application Bootstrap and Configuration
 * Zero database dependencies. Environment and filesystem-driven.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookies
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Base Application Constants (defined safely)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', APP_ROOT . '/storage');
}
if (!defined('ORIGINALS_PATH')) {
    define('ORIGINALS_PATH', STORAGE_PATH . '/originals');
}
if (!defined('PROCESSED_PATH')) {
    define('PROCESSED_PATH', STORAGE_PATH . '/processed');
}
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', STORAGE_PATH . '/logs');
}
if (!defined('RATE_LIMIT_PATH')) {
    define('RATE_LIMIT_PATH', STORAGE_PATH . '/rate_limits');
}

// Ensure required directories exist
foreach ([ORIGINALS_PATH, PROCESSED_PATH, LOGS_PATH, RATE_LIMIT_PATH] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Application Settings
return [
    'app_name' => 'RemoveBG',
    'env' => 'production', // production or development
    'background_removal' => [
        'provider' => 'pixelcut',
        'api_key' => 'sk_6b3a6a06fb824f6986347c23461317ba',
        'api_url' => 'https://api.developer.pixelcut.ai/v1/remove-background',
        'timeout' => 30
    ],
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'max_width' => 4096,
        'max_height' => 4096,
        'max_pixels' => 25 * 1000000, // 25 Megapixels
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp']
    ],
    'security' => [
        'retention_minutes' => 30,
        'rate_limit_max_requests' => 10,
        'rate_limit_window' => 60 // seconds
    ]
];