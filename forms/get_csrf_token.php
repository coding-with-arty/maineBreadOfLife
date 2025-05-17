<?php
// Load configuration first
require_once __DIR__ . '/config.php';

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

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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