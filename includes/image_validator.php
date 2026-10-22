<?php
function validate_uploaded_image(array $file): array {
    $config = require APP_ROOT . '/config/bootstrap.php';
    $uploadConfig = $config['upload'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error code: ' . $file['error']];
    }

    if ($file['size'] > $uploadConfig['max_file_size']) {
        return ['success' => false, 'error' => 'File exceeds maximum allowed size of 10MB.'];
    }

    // Strict MIME / Real type verification using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $uploadConfig['allowed_types'], true)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPEG, PNG, and WebP are allowed.'];
    }

    // Verify image dimensions & corruption via getimagesize
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'error' => 'The uploaded file is not a valid or readable image.'];
    }

    [$width, $height] = $imageInfo;
    $pixels = $width * $height;

    if ($width > $uploadConfig['max_width'] || $height > $uploadConfig['max_height']) {
        return ['success' => false, 'error' => "Image dimensions exceed maximum allowed limit ({$uploadConfig['max_width']}x{$uploadConfig['max_height']}px)."];
    }

    if ($pixels > $uploadConfig['max_pixels']) {
        return ['success' => false, 'error' => 'Image resolution is too high.'];
    }

    return [
        'success' => true,
        'mime' => $mimeType,
        'width' => $width,
        'height' => $height,
        'extension' => image_type_to_extension($imageInfo[2], false)
    ];
}