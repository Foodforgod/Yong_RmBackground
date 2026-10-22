<?php
require_once __DIR__ . '/config/bootstrap.php';

$fileId = $_GET['file'] ?? '';
$type = $_GET['type'] ?? '';

if (!preg_match('/^[a-f0-9]{32}$/', $fileId)) {
    http_response_code(400);
    exit('Invalid file request.');
}

$filePath = '';
$downloadName = '';

if ($type === 'orig') {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $p = ORIGINALS_PATH . '/' . $fileId . '_orig.' . $ext;
        if (file_exists($p)) {
            $filePath = $p;
            $downloadName = 'original_' . $fileId . '.' . $ext;
            break;
        }
    }
} elseif ($type === 'processed') {
    $p = PROCESSED_PATH . '/' . $fileId . '_processed.png';
    if (file_exists($p)) {
        $filePath = $p;
        $downloadName = 'removed_background_' . $fileId . '.png';
    }
}

if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    exit('File not found or expired.');
}

$mime = mime_content_type($filePath);
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;