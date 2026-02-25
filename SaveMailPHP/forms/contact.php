<?php
declare(strict_types=1);
session_start();

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    http_response_code(500);
    exit('Configuration missing.');
}

// ------------- 0) Allow only POST -------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

// ------------- 1) Verify CSRF token -------------
$csrf = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !is_string($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
  http_response_code(403);
  exit('CSRF validation failed');
}
unset($_SESSION['csrf_token']);

// ------------- 2) Verify Cloudflare Turnstile -------------
$turnstileToken = $_POST['cf-turnstile-response'] ?? '';
if (!is_string($turnstileToken) || $turnstileToken === '') {
  http_response_code(400);
  exit('Missing bot verification token');
}

try {
  $payload = json_encode([
    'secret'   => $env['TURNSTILE_SECRET'], // Replaced!
    'response' => $turnstileToken,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
  ], JSON_THROW_ON_ERROR);

  $ctx = stream_context_create([
    'http' => [
      'method'  => 'POST',
      'header'  => "Content-Type: application/json\r\n",
      'content' => $payload,
      'timeout' => 5,
    ],
  ]);

  $raw = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);
  $ts = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

  if (empty($ts['success'])) {
    http_response_code(403);
    exit('Bot verification failed');
  }
} catch (Throwable $e) {
  http_response_code(502);
  exit('Bot verification error');
}

// ------------- 3) Validate & Sanitize -------------
$name    = trim((string)($_POST['name']    ?? ''));
$email   = trim((string)($_POST['email']   ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) > 100) { http_response_code(400); exit('Invalid name'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); exit('Invalid email'); }
if ($subject === '' || mb_strlen($subject) > 150) { http_response_code(400); exit('Invalid subject'); }

// ------------- 4) Load PHPMailer -------------
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host       = $env['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['SMTP_USER'];
    $mail->Password   = $env['SMTP_PASS'];
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('receptionist@mainebreadoflife.org', 'BOL Website');
    $mail->addAddress('receptionist@mainebreadoflife.org');
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "Contact Form: " . $subject;
    $mail->Body    = "<strong>Name:</strong> $name<br><strong>Email:</strong> $email<br><br><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message));
    $mail->AltBody = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    // ------------- 5) Safe Attachment Handling -------------
    if (!empty($_FILES['attachment']['name'][0])) {
        $allowedExt = ['pdf','doc','docx','jpg','jpeg','png','zip'];
        $maxBytes   = 5 * 1024 * 1024;

        foreach ($_FILES['attachment']['tmp_name'] as $i => $tmpPath) {
            if ($_FILES['attachment']['error'][$i] !== UPLOAD_ERR_OK) continue;
            
            $originalName = $_FILES['attachment']['name'][$i];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $size = $_FILES['attachment']['size'][$i];

            if (in_array($ext, $allowedExt) && $size <= $maxBytes) {
                $mail->addAttachment($tmpPath, $originalName);
            }
        }
    }

    $mail->send();
    echo "OK"; // Required for BootstrapMade 'validate.js' to show success

} catch (Exception $e) {
    http_response_code(500);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
// Note: The extra closing brace that was here has been removed.
?>