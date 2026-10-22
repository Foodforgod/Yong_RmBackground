<?php
class PixelcutProvider {
    private string $apiKey;
    private string $apiUrl;
    private int $timeout;

    public function __construct() {
        $config = require APP_ROOT . '/config/bootstrap.php';
        $this->apiKey = $config['background_removal']['api_key'];
        $this->apiUrl = $config['background_removal']['api_url'];
        $this->timeout = $config['background_removal']['timeout'];
    }

    public function removeBackground(string $imagePath): array {
        if (!file_exists($imagePath)) {
            return ['success' => false, 'error' => 'Source image not found on server.'];
        }

        // Pixelcut accepts image upload or multipart/form-data or public URL. 
        // Here we post the binary or multipart/form-data directly using cURL for maximum reliability.
        $cfile = new CURLFile($imagePath, mime_content_type($imagePath), basename($imagePath));
        
        $postData = [
            'image' => $cfile,
            'format' => 'png'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $this->apiKey,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            write_log("Pixelcut cURL Error: {$curlError}", 'ERROR');
            return ['success' => false, 'error' => 'Connection to background removal provider failed.'];
        }

        if ($httpCode !== 200) {
            write_log("Pixelcut API HTTP {$httpCode}: {$response}", 'ERROR');
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['message'] ?? 'Background removal failed with status code ' . $httpCode;
            return ['success' => false, 'error' => $errorMsg];
        }

        // Pixelcut returns JSON containing base64 data or an image URL depending on endpoint version, 
        // or direct binary stream if requested. Let's handle JSON response with image URL or base64.
        $data = json_decode($response, true);
        
        if (isset($data['result_url'])) {
            $imageContent = @file_get_contents($data['result_url']);
            if ($imageContent === false) {
                return ['success' => false, 'error' => 'Failed to download processed image from provider.'];
            }
            return ['success' => true, 'data' => $imageContent];
        } elseif (isset($data['image_base64'])) {
            $imageContent = base64_decode($data['image_base64']);
            return ['success' => true, 'data' => $imageContent];
        }

        // If response is direct binary image data
        if ($httpCode === 200 && !empty($response) && substr($response, 0, 4) !== '{') {
            return ['success' => true, 'data' => $response];
        }

        return ['success' => false, 'error' => 'Invalid response received from background removal provider.'];
    }
}