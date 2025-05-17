<?php
// Start output buffering to prevent header issues on GoDaddy
ob_start();

// Ensure session works on GoDaddy
if (ini_get('session.save_path') == '') {
    $sessionPath = __DIR__ . '/sessions';
    if (!file_exists($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    ini_set('session.save_path', $sessionPath);
}

// Start the session
session_start();

// Define security constant before including config
define('BREAD_OF_LIFE_LOADED', true);

// Include configuration
require_once(__DIR__ . '/config.php');

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to log errors
function logError($message) {
    $logFile = __DIR__ . '/form_errors.log';
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $logFile);
    $_SESSION['error'] = $message;
}

// Check if the request is a POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        logError('Security validation failed. Please try again.');
        $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../volunteer.html';
        $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'status=error&message=' . urlencode('Security validation failed. Please try again.');
        header('Location: ' . $redirect_url);
        exit();
    }

    // Initialize variables for error handling and input sanitization
    $firstName = isset($_POST['firstName']) ? htmlspecialchars(trim($_POST['firstName'])) : null;
    $lastName = isset($_POST['lastName']) ? htmlspecialchars(trim($_POST['lastName'])) : null;
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : null;
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $interest = isset($_POST['interest']) ? htmlspecialchars(trim($_POST['interest'])) : null;
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : null;
    $terms = isset($_POST['terms']) ? true : false;

    // Check if all required fields are filled
    if (!$firstName || !$lastName || !$email || !$interest || !$message || !$terms) {
        logError("All required fields must be filled out and terms must be accepted.");
        $redirect_url = '../volunteer.html';
        $redirect_url .= '?status=error&message=' . urlencode('All required fields must be filled out and terms must be accepted.');
        header('Location: ' . $redirect_url);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logError("Invalid email format.");
        $redirect_url = '../volunteer.html';
        $redirect_url .= '?status=error&message=' . urlencode('Please enter a valid email address.');
        header('Location: ' . $redirect_url);
        exit();
    }

    // Verify reCAPTCHA if enabled in config
    if (defined('RECAPTCHA_SECRET_KEY') && isset($_POST['g-recaptcha-response'])) {
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $secretKey = RECAPTCHA_SECRET_KEY;
        $verifyResponse = false;
        
        if (function_exists('curl_version')) {
            // Use cURL if available (more reliable on GoDaddy)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $verifyResponse = json_decode($response);
            }
        } else {
            // Fallback to file_get_contents if cURL is not available
            $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}&remoteip={$_SERVER['REMOTE_ADDR']}");
            $verifyResponse = json_decode($verifyResponse);
        }

        if (!$verifyResponse || !$verifyResponse->success) {
            logError("reCAPTCHA verification failed.");
            $redirect_url = '../volunteer.html';
            $redirect_url .= '?status=error&message=' . urlencode('reCAPTCHA verification failed. Please try again.');
            header('Location: ' . $redirect_url);
            exit();
        }
    }

    // Prepare email content
    $to = 'volunteer@mainebreadoflife.org';
    $subject = "New Volunteer Application: $firstName $lastName";
    
    $email_body = "New volunteer application received:\n\n";
    $email_body .= "Name: $firstName $lastName\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Phone: " . ($phone ?: 'Not provided') . "\n";
    $email_body .= "Interested in: $interest\n\n";
    $email_body .= "Message:\n$message\n\n";
    $email_body .= "---\n";
    $email_body .= "This form was submitted from: " . $_SERVER['HTTP_REFERER'] . "\n";
    $email_body .= "User IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

    $headers = [
        'From' => 'noreply@mainebreadoflife.org',
        'Reply-To' => $email,
        'X-Mailer' => 'PHP/' . phpversion(),
        'Content-Type' => 'text/plain; charset=UTF-8'
    ];

    // Format headers
    $formatted_headers = [];
    foreach ($headers as $key => $value) {
        $formatted_headers[] = "$key: $value";
    }

    // Send email
    $mail_sent = mail($to, $subject, $email_body, implode("\r\n", $formatted_headers));

    if ($mail_sent) {
        // Log successful submission
        $logFile = __DIR__ . '/volunteer_submissions.log';
        $logEntry = date('[Y-m-d H:i:s] ') . "New volunteer application from $firstName $lastName ($email)\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Redirect to thank you page
        $redirect_url = '../volunteer.html';
        $redirect_url .= '?status=success&message=' . urlencode('Thank you for your interest in volunteering! We will be in touch soon.');
        header('Location: ' . $redirect_url);
        exit();
    } else {
        logError("Failed to send volunteer application email.");
        $redirect_url = '../volunteer.html';
        $redirect_url .= '?status=error&message=' . urlencode('Failed to send your application. Please try again later.');
        header('Location: ' . $redirect_url);
        exit();
    }
} else {
    // Not a POST request, redirect to form
    header('Location: ../volunteer.html');
    exit();
}
?>