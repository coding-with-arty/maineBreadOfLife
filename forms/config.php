<?php
/**
 * Configuration file for Bread of Life forms - Production Environment
 */

// Define constant to prevent direct access
define('BREAD_OF_LIFE_LOADED', true);

// reCAPTCHA Configuration
define('RECAPTCHA_SECRET_KEY', '6LcjtgUrAAAAAG24c6JvqsfnuCinNYVVaCRUMHvn');
define('RECAPTCHA_SITE_KEY', '6LcjtgUrAAAAAIOqKQc6Txmusc4zoxPqkBMt5vwS');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Force HTTPS
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 1800); // 30 minutes

// Error handling for production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Set timezone
date_default_timezone_set('America/New_York');

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://www.google.com https://www.gstatic.com; style-src \'self\' https://fonts.googleapis.com; img-src \'self\' data: https:; font-src \'self\' https://fonts.gstatic.com; frame-src https://www.google.com;');
