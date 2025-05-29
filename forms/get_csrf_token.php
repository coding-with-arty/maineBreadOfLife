<?php
// Load configuration first
require_once(__DIR__ . '/../config.php');

// CORS headers
header('Access-Control-Allow-Origin: https://mainebreadoflife.org'); // Replace with your actual domain
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Initialize response
$response = [
    'success' => false,
    'token' => null,
    'timestamp' => time(),
    'message' => ''
];

try {
    // Start or resume session with secure settings
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true
        ]);
    }

    // Rate limiting: max 5 requests per 10 minutes
    if (!isset($_SESSION['csrf_rate_limit'])) {
        $_SESSION['csrf_rate_limit'] = [];
    }

    // Remove timestamps older than 10 minutes
    $_SESSION['csrf_rate_limit'] = array_filter(
        $_SESSION['csrf_rate_limit'],
        fn($timestamp) => $timestamp > (time() - 600)
    );

    // Check if limit exceeded
    if (count($_SESSION['csrf_rate_limit']) >= 5) {
        http_response_code(429);
        $response['message'] = 'Rate limit exceeded. Please try again later.';
        echo json_encode($response);
        exit;
    }

    // Log this request
    $_SESSION['csrf_rate_limit'][] = time();

    // Token expiration: regenerate if older than 15 minutes
    if (
        empty($_SESSION['csrf_token']) ||
        empty($_SESSION['csrf_token_time']) ||
        time() - $_SESSION['csrf_token_time'] > 900
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Set response data
    $response['success'] = true;
    $response['token'] = $_SESSION['csrf_token'];

    // Log successful token generation
    error_log('CSRF token generated/retrieved for session: ' . session_id());

} catch (Exception $e) {
    // Log the error
    error_log('CSRF Token Error: ' . $e->getMessage());

    // Provide a generic error message
    $response['message'] = 'An error occurred while generating security token';

    // Set appropriate HTTP status
    http_response_code(500);
}

// Output JSON response
echo json_encode($response);