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
session_start();

// Define security constant before including config
define('BREAD_OF_LIFE_LOADED', true);
require_once(__DIR__ . '/config.php');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// CSRF token check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    logError('Security validation failed.');
    redirectWithError('Security validation failed.');
}

// reCAPTCHA check
if (defined('RECAPTCHA_SECRET_KEY') && isset($_POST['g-recaptcha-response'])) {
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET_KEY . "&response=" . $recaptchaResponse);
    $captchaSuccess = json_decode($verify);
    if (!$captchaSuccess->success) {
        logError('reCAPTCHA verification failed.');
        redirectWithError('reCAPTCHA verification failed.');
    }
}

// Sanitize input
$firstName = htmlspecialchars(trim($_POST['firstName']));
$lastName = htmlspecialchars(trim($_POST['lastName']));
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone']));
$interest = htmlspecialchars(trim($_POST['interest']));
$message = htmlspecialchars(trim($_POST['message']));

// Validate required fields
if (!$firstName || !$lastName || !$email || !$interest || !$message) {
    logError('All required fields must be filled.');
    redirectWithError('All required fields must be filled.');
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logError('Invalid email format.');
    redirectWithError('Invalid email format.');
}

// Handle file uploads
$uploadedFiles = [];
if (!empty($_FILES['attachment']['name'][0])) {
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

    foreach ($_FILES['attachment']['name'] as $i => $name) {
        $tmp = $_FILES['attachment']['tmp_name'][$i];
        $type = $_FILES['attachment']['type'][$i];
        $size = $_FILES['attachment']['size'][$i];

        if ($size > 8 * 1024 * 1024) {
            logError("File $name exceeds size limit.");
            redirectWithError("File $name exceeds size limit.");
        }

        if (!in_array($type, $allowedTypes)) {
            logError("File type $type not allowed.");
            redirectWithError("File type $type not allowed.");
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = time() . '_' . md5($name) . '.' . $ext;
        $dest = $uploadDir . $newName;

        if (!move_uploaded_file($tmp, $dest)) {
            logError("Failed to upload $name.");
            redirectWithError("Failed to upload $name.");
        }

        $uploadedFiles[] = ['path' => $dest, 'name' => $name];
    }
}

// Send email using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your@email.com';
    $mail->Password = 'yourpassword';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom($email, "$firstName $lastName");
    $mail->addAddress('volunteer@mainebreadoflife.org', 'Volunteer Coordinator');
    $mail->isHTML(true);
    $mail->Subject = 'New Volunteer Application';
    $mail->Body = "
        <h3>New Volunteer Application</h3>
        <p><strong>Name:</strong> $firstName $lastName</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Interest:</strong> $interest</p>
        <p><strong>Message:</strong><br>$message</p>
    ";

    foreach ($uploadedFiles as $file) {
        $mail->addAttachment($file['path'], $file['name']);
    }

    $mail->send();

    // Auto-response
    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = "Thank you for your volunteer application";
    $mail->Body = nl2br("Dear $firstName $lastName,\n\nThank you for your interest in volunteering with Bread of Life. We have received your application and will review it shortly.\n\nBest regards,\nThe Bread of Life Team\nhttps://mainebreadoflife.org");
    $mail->send();

    header('Location: ../volunteer.html?status=success&message=' . urlencode('Thank you for your application.'));
    exit();
} catch (Exception $e) {
    logError("Mailer Error: {$mail->ErrorInfo}");
    redirectWithError('Unable to send the email. Please try again later.');
}

// Helper functions
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, __DIR__ . '/form_errors.log');
}

function redirectWithError($msg) {
    $url = '../volunteer.html?status=error&message=' . urlencode($msg);
    header("Location: $url");
    exit();
}

ob_end_flush();
?>