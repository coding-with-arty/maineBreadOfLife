<?php
/**
 * Newsletter Subscription Handler
 * Optimized for GoDaddy hosting
 * Author: Arthur Belanger
 * Email: arthur@example.com
 * Date: 2025-06-04
 * Description: This script handles user login functionality.
 */

// Start output buffering to prevent header issues on GoDaddy
ob_start();

// Ensure session works on GoDaddy
// Sometimes GoDaddy has issues with session path
if (ini_get('session.save_path') == '') {
    // If no session path is set, use a custom one
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
require_once(__DIR__ . '/../config.php');

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set recipient email
$receiving_email_address = 'receptionist@mainebreadoflife.org';

// Function to log errors (useful for debugging on GoDaddy)
function logError($message) {
  $logFile = __DIR__ . '/newsletter_errors.log';

  // Create the log directory if it doesn't exist
  $logDir = dirname($logFile);
  if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
  }

  // Try to write to the log file
  error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $logFile);

  // Store in session for user feedback
  $_SESSION['error'] = $message;
}

  // Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
  logError('Security validation failed. Please try again.');
  $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.html';
  $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'status=error&message=' . urlencode('Security validation failed. Please try again.');
  header('Location: ' . $redirect_url);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify reCAPTCHA if enabled in config and provided in the form
  if (defined('RECAPTCHA_SECRET_KEY') && isset($_POST['g-recaptcha-response'])) {
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = RECAPTCHA_SECRET_KEY;
    $verifyResponse = false;

      // Try using cURL first (more reliable on GoDaddy)
    if (function_exists('curl_version')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
      ]));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      curl_close($ch);
      $responseKeys = json_decode($result, true);
      $verifyResponse = true;
    } else {
        // Fallback to file_get_contents
      $url = 'https://www.google.com/recaptcha/api/siteverify';
      $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
      ];

      $options = [
        'http' => [
          'header' => "Content-type: application/x-www-form-urlencoded\r\n",
          'method' => 'POST',
          'content' => http_build_query($data)
        ]
      ];

      $context = stream_context_create($options);
      $result = @file_get_contents($url, false, $context);
      if ($result !== false) {
        $responseKeys = json_decode($result, true);
        $verifyResponse = true;
      }
    }

      // Verify Response
    if ($verifyResponse && intval($responseKeys["success"]) !== 1) {
      logError('reCAPTCHA verification failed. Please try again.');
      $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.html';
      $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'status=error&message=' . urlencode('reCAPTCHA verification failed. Please try again.');
      header('Location: ' . $redirect_url);
      exit();
    }
  }

    // Validate and sanitize email input
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logError('Please enter a valid email address.');
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.html';
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'status=error&message=' . urlencode('Please enter a valid email address.');
    header('Location: ' . $redirect_url);
    exit();
  }

    // Check for duplicate submissions (optional)
  if (isset($_SESSION['last_email_submission']) && $_SESSION['last_email_submission'] === $email && time() - $_SESSION['last_submission_time'] < 3600) {
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.html';
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'status=success&message=' . urlencode('You are already subscribed with this email address.');
    header('Location: ' . $redirect_url);
    exit();
  }

    // Prepare email content
  $to = $receiving_email_address;
  $subject = "New Newsletter Subscription";

    // Headers optimized for GoDaddy
  $headers = "MIME-Version: 1.0\r\n";
  $headers .= "From: Bread of Life <no-reply@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'mainebreadoflife.org') . ">\r\n";
  $headers .= "Reply-To: $email\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
  $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Email body
  $message = "New newsletter subscription from: $email\r\n";
  $message .= "Submitted on: " . date('Y-m-d H:i:s') . "\r\n";
  $message .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\r\n";

    // Send email - GoDaddy optimized
    // Add a small delay before sending to prevent rate limiting
    usleep(100000); // 100ms delay

    // Set additional parameters for GoDaddy mail servers
    $additional_params = null;

    // On GoDaddy, sometimes you need to specify the sendmail path
    if (ini_get('sendmail_path')) {
      $additional_params = '-f' . 'no-reply@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'mainebreadoflife.org');
    }

    // Send the email with proper parameters
    $mail_sent = mail($to, $subject, $message, $headers, $additional_params);

    // Store submission info to prevent duplicates
    $_SESSION['last_email_submission'] = $email;
    $_SESSION['last_submission_time'] = time();

    if ($mail_sent) {
      // Send confirmation to subscriber
      $subscriber_subject = "Thank you for subscribing to Bread of Life Newsletter";
      $subscriber_message = "Dear Subscriber,\r\n\r\n";
      $subscriber_message .= "Thank you for subscribing to the Bread of Life newsletter. ";
      $subscriber_message .= "You will now receive updates about our events, programs, and opportunities to help.\r\n\r\n";
      $subscriber_message .= "If you did not subscribe to this newsletter, we apologize and please disregard this email.\r\n\r\n";
      $subscriber_message .= "Warm regards,\r\n";
      $subscriber_message .= "The Bread of Life Team\r\n";
      $subscriber_message .= "https://mainebreadoflife.org";

      // Send confirmation email
      @mail($email, $subscriber_subject, $subscriber_message, $headers, $additional_params);

      $status = 'success';
      $message = 'Thank you for subscribing! You will receive a confirmation email shortly.';
    } else {
      // Log the mail error for debugging
      $error_message = "Unable to process subscription.";
      if (function_exists('error_get_last') && error_get_last() && isset(error_get_last()['message'])) {
        $error_message .= " Mail system error: " . error_get_last()['message'];
      }
      logError($error_message);
      $status = 'error';
      $message = 'Unable to process your subscription. Please try again later.';
    }

    // Redirect back to the referring page with status parameters
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.html';
    $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . "status=$status&message=" . urlencode($message);
    header('Location: ' . $redirect_url);
    exit();
  }

// Flush output buffer before ending script
  ob_end_flush();