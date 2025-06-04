<?php
/*--
 *  ======================================================================
 *   CONFIG.PHP | MAINE BREAD OF LIFE
 *   AUTHOR: ARTHUR DANIEL BELANGER JR.
 *   https://github.com/MusicalViking/maineBreadOfLife
 *  ======================================================================
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Define constants
define('BREAD_OF_LIFE_LOADED', true);
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_PORT', getenv('SMTP_PORT'));
define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY'));

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 1800);

// Error handling
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('America/New_York');

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com https://www.gstatic.com; style-src 'self' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; frame-src https://www.google.com;");
?>