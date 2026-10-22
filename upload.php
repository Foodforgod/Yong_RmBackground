<?php
// Catch any unexpected PHP errors/warnings and output them as JSON instead of HTML
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false, 'error' => "PHP Error: $errstr in " . basename($errfile) . " on line $errline"]);
    exit;
});

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/rate_limiter.php';
require_once __DIR__ . '/includes/image_validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

if (!check_rate_limit()) {
    json_response(['success' => false, 'error' => 'Rate limit exceeded. Please wait a moment before trying again.'], 429);
}

$headers = function_exists('getallheaders') ? getallheaders() : [];
$csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrfToken)) {
    json_response(['success' => false, 'error' => 'Invalid security token (CSRF). Refresh the page.'], 403);
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    json_response(['success' => false, 'error' => 'No image file was uploaded.'], 400);
}

$validation = validate_uploaded_image($_FILES['image']);
if (!$validation['success']) {
    json_response(['success' => false, 'error' => $validation['error']], 400);
}

$fileId = bin2hex(random_bytes(16));
$extension = $validation['extension'];
$originalFilename = $fileId . '_orig.' . $extension;
$originalPath = ORIGINALS_PATH . '/' . $originalFilename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $originalPath)) {
    json_response(['success' => false, 'error' => 'Failed to save uploaded file on server.'], 500);
}

json_response([
    'success' => true,
    'file_id' => $fileId,
    'original_url' => 'download.php?file=' . $fileId . '&type=orig'
]);