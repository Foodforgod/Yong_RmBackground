<?php
function cleanup_expired_files(): void {
    $config = require __DIR__ . '/../config/bootstrap.php';
    $retentionMinutes = $config['security']['retention_minutes'] ?? 30;
    $threshold = time() - ($retentionMinutes * 60);

    foreach ([ORIGINALS_PATH, PROCESSED_PATH] as $dir) {
        if (!is_dir($dir)) continue;
        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getFilename() !== '.htaccess') {
                if ($fileInfo->getMTime() < $threshold) {
                    @unlink($fileInfo->getPathname());
                }
            }
        }
    }
}