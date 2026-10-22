document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('drop-zone');
    const imageInput = document.getElementById('image-input');
    const uploadSection = document.getElementById('upload-section');
    const loadingState = document.getElementById('loading-state');
    const editorSection = document.getElementById('editor-section');
    const loadingText = document.getElementById('loading-text');
    const csrfToken = document.getElementById('csrf_token').value;
    
    let currentProcessedImage = null;
    let currentBgColor = 'transparent';
    let canvas = document.getElementById('output-canvas');
    let ctx = canvas.getContext('2d');

    // Drag and Drop Events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) handleUpload(files[0]);
    });

    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) handleUpload(e.target.files[0]);
    });

    function handleUpload(file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('csrf_token', csrfToken);

        showLoading('Uploading and validating image...');

        fetch('upload.php', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.error);
            processImage(data.file_id);
        })
        .catch(err => {
            hideLoading();
            alert('Error: ' + err.message);
        });
    }

    function processImage(fileId) {
        showLoading('Removing background with AI...');

        fetch('process.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ file_id: fileId, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.error);
            loadProcessedImage(data.processed_url);
        })
        .catch(err => {
            hideLoading();
            alert('Error: ' + err.message);
        });
    }

    function loadProcessedImage(url) {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            currentProcessedImage = img;
            renderCanvas();
            hideLoading();
            
            // Safely hide only the upload card and show the editor workspace
            uploadSection.classList.add('d-none');
            editorSection.classList.remove('d-none');
            editorSection.scrollIntoView({ behavior: 'smooth' });
        };
        img.onerror = () => {
            hideLoading();
            alert('Failed to load processed image.');
        };
        img.src = url;
    }

    function renderCanvas() {
        if (!currentProcessedImage) return;
        canvas.width = currentProcessedImage.naturalWidth;
        canvas.height = currentProcessedImage.naturalHeight;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        if (currentBgColor !== 'transparent') {
            ctx.fillStyle = currentBgColor;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        
        ctx.drawImage(currentProcessedImage, 0, 0);
    }

    // Background selection
    document.querySelectorAll('.bg-option').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.bg-option').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentBgColor = btn.getAttribute('data-bg');
            renderCanvas();
        });
    });

    document.getElementById('custom-color-picker').addEventListener('input', (e) => {
        document.querySelectorAll('.bg-option').forEach(b => b.classList.remove('active'));
        currentBgColor = e.target.value;
        renderCanvas();
    });

    // Download
    document.getElementById('download-btn').addEventListener('click', () => {
        const link = document.createElement('a');
        link.download = 'removebg_result.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });

    // Reset
    document.getElementById('reset-btn').addEventListener('click', () => {
        editorSection.classList.add('d-none');
        uploadSection.classList.remove('d-none');
        imageInput.value = '';
        currentProcessedImage = null;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function showLoading(text) {
        loadingText.textContent = text;
        dropZone.classList.add('d-none');
        loadingState.classList.remove('d-none');
    }

    function hideLoading() {
        loadingState.classList.add('d-none');
        dropZone.classList.remove('d-none');
    }
});