🪄 RemoveBG — AI Background Removal Web Application
RemoveBG is a lightweight, high-performance web application designed to automatically remove image backgrounds using artificial intelligence. Users can upload images, preview the background-removed results, customize background colors (transparent, white, black, or custom colors), and download high-quality PNGs.

⚡ Zero SQL Database Dependency: This application operates completely without MySQL, MariaDB, SQLite, or any other SQL database. It relies entirely on a secure, stateless filesystem architecture with temporary file retention and cleanup.

🛠️ Technology Stack
PHP: 8.3+ (Server-side API calls & request handling)

Frontend: HTML5, CSS3, Vanilla JavaScript (ES6+), Bootstrap 5, Font Awesome

Image Compositing: Browser Canvas API

API Engine: Pixelcut AI Background Removal API

Web Server: Apache (XAMPP for local development, cPanel shared hosting for production)

📂 Project Structure
Plaintext
Yong_RmBackground/
├── index.php                  # Main landing page & application interface
├── upload.php                 # Asynchronous file upload & validation handler
├── process.php                # AI API processing dispatch endpoint
├── download.php               # Secure temporary image download handler
├── config/
│   ├── config.example.php     # Configuration template
│   └── bootstrap.php          # Application bootstrap & safe constant definitions
├── includes/
│   ├── functions.php          # JSON helpers & logging functions
│   ├── image_validator.php    # Server-side MIME & dimension validation
│   ├── csrf.php               # Anti-CSRF token generator & verifier
│   ├── rate_limiter.php       # Filesystem-based rate limiter (No SQL)
│   └── cleanup.php            # Automated file cleanup utilities
├── services/
│   ├── BackgroundRemovalService.php  # Provider service wrapper
│   └── providers/
│       └── PixelcutProvider.php      # Pixelcut API cURL implementation
├── assets/
│   ├── css/
│   │   └── style.css          # Custom interface styles
│   └── js/
│       └── app.js             # Client-side UI workflow & Canvas editor logic
├── storage/
│   ├── .htaccess              # Direct file access security restrictions
│   ├── originals/             # Temporary folder for uploaded originals
│   ├── processed/             # Temporary folder for background-removed PNGs
│   ├── rate_limits/           # Short-lived IP tracking JSON files
│   └── logs/                  # Application runtime logs
├── cron/
│   └── cleanup.php            # Cron job script for scheduled file pruning
├── .htaccess                  # Root server rules & HTTPS enforcement
└── README.md                  # Project documentation & setup guide
⚙️ Step-by-Step Setup Guide
1. Requirements Checklist
PHP 8.3 or higher

Required PHP Extensions enabled in php.ini:

curl (for API communications)

fileinfo (for MIME type validation)

gd (for client/server image rendering support)

Active Pixelcut API Key (configured in config/bootstrap.php).

2. Local Installation (XAMPP on Windows)
Install XAMPP: Ensure PHP 8.3+ is installed via XAMPP.

Copy Project Directory: Copy the project folder into your XAMPP web root:

Plaintext
C:\xampp\htdocs\Yong_RmBackground
Verify PHP Extensions:

Open XAMPP Control Panel.

Click Config next to Apache -> select php.ini.

Ensure the following lines are not commented out (remove ; if present):

Ini, TOML
extension=curl
extension=fileinfo
extension=gd
Save php.ini and restart Apache.

Configure Storage Permissions:

Ensure the storage/ directory and its subdirectories (originals, processed, rate_limits, logs) are writable by the Apache process.

Run Application:
<img width="1916" height="1137" alt="image" src="https://github.com/user-attachments/assets/9d983fe5-74c8-431e-9d16-8df38426ea75" />

Do NOT start MySQL (it is not needed!).

Open your browser and navigate to:

Plaintext
http://localhost/Yong_RmBackground/
3. Production Deployment (cPanel Shared Hosting)
Upload Files: Compress the project folder into a .zip archive and upload it via cPanel File Manager to public_html (or your target subfolder). Extract the archive.

Set PHP Version:

In cPanel, go to Select PHP Version or MultiPHP Manager.

Set the domain's PHP version to 8.3+.

Enable PHP Extensions:

Go to Select PHP Version -> Extensions.

Ensure curl, fileinfo, and gd are checked.

Directory Permissions:

Set permissions for the storage/ folder and all its subfolders to 755 (or 777 if required by your host).

Configure Automated Cleanup Cron Job:

In cPanel, navigate to Cron Jobs.

Add a cron task to run every hour to purge expired temporary images:

Bash
php -q /home/YOUR_USERNAME/public_html/cron/cleanup.php
🔒 Security Architecture
Stateless & No Database: Eliminates SQL Injection risks completely.

Strict Server-Side Image Validation: Uses finfo MIME-type inspection, file signature checking, and image dimension/pixel caps to prevent malicious file execution.

CSRF Token Protection: All POST endpoints enforce cryptographic token verification.

Filesystem Rate Limiting: Protects endpoints against brute-force/abuse using temporary JSON counters without needing SQL tables.

Protected File Access: Direct HTTP access to private uploads is restricted via .htaccess. Files are served strictly through download.php token matching.
