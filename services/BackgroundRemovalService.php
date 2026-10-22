<?php
require_once __DIR__ . '/providers/PixelcutProvider.php';

class BackgroundRemovalService {
    private $provider;

    public function __construct() {
        $this->provider = new PixelcutProvider();
    }

    public function process(string $imagePath): array {
        return $this->provider->removeBackground($imagePath);
    }
}