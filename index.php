<?php
require_once __DIR__ . '/config/bootstrap.php';

// Safely include CSRF functions if present, otherwise handle inline fallback
$csrfPath = __DIR__ . '/includes/csrf.php';
if (file_exists($csrfPath)) {
    require_once $csrfPath;
}

if (function_exists('generate_csrf_token')) {
    $csrfToken = generate_csrf_token();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrfToken = $_SESSION['csrf_token'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RemoveBG — AI Background Removal System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light text-dark">
    <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>RemoveBG
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="#upload-section" class="btn btn-primary px-4 rounded-pill">Upload Image</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="py-5 text-center bg-white border-bottom">
        <div class="container py-4">
            <h1 class="display-4 fw-bold mb-3">Remove Image Backgrounds in Seconds</h1>
            <p class="lead text-muted mb-4 mx-auto" style="max-width: 600px;">
                Upload an image, let AI remove the background, and download a clean transparent image in just a few clicks.
            </p>
            <div id="upload-section" class="card shadow-sm border-0 mx-auto p-4 rounded-4" style="max-width: 700px;">
                <div id="drop-zone" class="upload-drop-zone border-2 border-dashed rounded-4 p-5 text-center bg-light">
                    <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-3"></i>
                    <h5 class="fw-semibold mb-1">Drag & Drop your image here</h5>
                    <p class="text-muted small mb-3">Supports JPG, PNG, WEBP up to 10MB</p>
                    <label for="image-input" class="btn btn-primary px-4 rounded-pill cursor-pointer">
                        <i class="fa-solid fa-folder-open me-2"></i>Browse Image
                    </label>
                    <input type="file" id="image-input" class="d-none" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <!-- Loading State -->
                <div id="loading-state" class="d-none text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="fw-semibold" id="loading-text">Processing image with AI...</h5>
                    <p class="text-muted small">Please wait while we remove the background.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Editor Workspace (Hidden Initially) -->
    <section id="editor-section" class="container py-5 d-none">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 p-4 rounded-4 text-center bg-white">
                    <h4 class="fw-bold mb-3 text-start">Preview Result</h4>
                    <div class="preview-container position-relative d-inline-block mx-auto rounded-3 overflow-hidden border" id="preview-wrapper" style="background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                        <canvas id="output-canvas" class="img-fluid d-block max-w-100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 p-4 rounded-4 bg-white">
                    <h4 class="fw-bold mb-3">Customize Background</h4>
                    <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-outline-secondary bg-option active" data-bg="transparent">
                            <i class="fa-solid fa-chess-board me-2"></i>Transparent
                        </button>
                        <button class="btn btn-outline-secondary bg-option" data-bg="#ffffff">
                            <i class="fa-solid fa-square text-white border me-2"></i>White Background
                        </button>
                        <button class="btn btn-outline-secondary bg-option" data-bg="#000000">
                            <i class="fa-solid fa-square text-dark me-2"></i>Black Background
                        </button>
                        <div class="input-group mt-2">
                            <span class="input-group-text">Custom Color</span>
                            <input type="color" class="form-control form-control-color w-50" id="custom-color-picker" value="#3b82f6">
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button id="download-btn" class="btn btn-success btn-lg rounded-pill">
                            <i class="fa-solid fa-download me-2"></i>Download Image
                        </button>
                        <button id="reset-btn" class="btn btn-outline-danger rounded-pill">
                            <i class="fa-solid fa-rotate-left me-2"></i>Process Another Image
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5 bg-white border-top">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">How It Works</h2>
                <p class="text-muted">Remove your image background in 3 simple steps.</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-4 bg-light">
                        <div class="text-primary mb-3"><i class="fa-solid fa-cloud-arrow-up fa-2x"></i></div>
                        <h5 class="fw-semibold">1. Upload Image</h5>
                        <p class="text-muted small mb-0">Drag and drop or select your photo from your local device.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-4 bg-light">
                        <div class="text-primary mb-3"><i class="fa-solid fa-wand-magic-sparkles fa-2x"></i></div>
                        <h5 class="fw-semibold">2. AI Cutout Process</h5>
                        <p class="text-muted small mb-0">Our connected AI engine isolates subjects and strips away backgrounds in seconds.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-4 bg-light">
                        <div class="text-primary mb-3"><i class="fa-solid fa-download fa-2x"></i></div>
                        <h5 class="fw-semibold">3. Export & Download</h5>
                        <p class="text-muted small mb-0">Keep the cutout transparent or customize with solid colors before saving your file.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Overview -->
    <section id="features" class="py-5 bg-light border-top">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Choose RemoveBG?</h2>
                <p class="text-muted">Built for speed, privacy, and state-of-the-art accuracy.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center p-3">
                    <div class="text-primary mb-3"><i class="fa-solid fa-bolt fa-2x"></i></div>
                    <h5 class="fw-semibold">Lightning Fast</h5>
                    <p class="text-muted small">Powered by cutting-edge AI engines to deliver pristine cutouts in seconds.</p>
                </div>
                <div class="col-md-4 text-center p-3">
                    <div class="text-primary mb-3"><i class="fa-solid fa-shield-halved fa-2x"></i></div>
                    <h5 class="fw-semibold">Secure & Private</h5>
                    <p class="text-muted small">Stateless architecture with automated temporary file cleanup. No data retention.</p>
                </div>
                <div class="col-md-4 text-center p-3">
                    <div class="text-primary mb-3"><i class="fa-solid fa-palette fa-2x"></i></div>
                    <h5 class="fw-semibold">Custom Backgrounds</h5>
                    <p class="text-muted small">Instantly apply solid colors or keep transparent background upon export.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top text-center text-muted small">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> RemoveBG. All rights reserved. Zero database architecture.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html>