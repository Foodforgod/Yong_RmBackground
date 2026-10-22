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
require_once __DIR__ . '/services/BackgroundRemovalService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['file_id'] ?? '';
$csrfToken = $input['csrf_token'] ?? '';

if (!verify_csrf_token($csrfToken)) {
    json_response(['success' => false, 'error' => 'Invalid security token.'], 403);
}

if (!preg_match('/^[a-f0-9]{32}$/', $fileId)) {
    json_response(['success' => false, 'error' => 'Invalid file identifier.'], 400);
}

// Find original file across allowed extensions
$originalPath = '';
$foundExt = '';
foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    $path = ORIGINALS_PATH . '/' . $fileId . '_orig.' . $ext;
    if (file_exists($path)) {
        $originalPath = $path;
        $foundExt = $ext;
        break;
    }
}

if (!$originalPath) {
    json_response(['success' => false, 'error' => 'Original image session has expired or does not exist.'], 404);
}

$service = new BackgroundRemovalService();
$result = $service->process($originalPath);

if (!$result['success']) {
    json_response(['success' => false, 'error' => $result['error']], 502);
}

$processedFilename = $fileId . '_processed.png';
$processedPath = PROCESSED_PATH . '/' . $processedFilename;

if (file_put_contents($processedPath, $result['data']) === false) {
    json_response(['success' => false, 'error' => 'Failed to store processed image.'], 500);
}

json_response([
    'success' => true,
    'processed_url' => 'download.php?file=' . $fileId . '&type=processed'
]);