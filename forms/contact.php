<?php
// contact.php — BootstrapMade helper + modern security hardening
declare(strict_types=1);
session_start();

/*
  SECURITY OVERVIEW
  - CSRF: token generated on page render, verified here (one-time optional).
  - Bot protection: Cloudflare Turnstile server-side verification (POST).
  - Input validation: length + type checks; sanitize header-bound fields.
  - File uploads: extension + MIME allow-list + per-file size limit; attach safely.
*/

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
// Optional: rotate one-time token
unset($_SESSION['csrf_token']);

// ------------- 2) Verify Cloudflare Turnstile token (server-side POST required) -------------
$turnstileToken = $_POST['cf-turnstile-response'] ?? '';
if (!is_string($turnstileToken) || $turnstileToken === '') {
  http_response_code(400);
  exit('Missing bot verification token');
}
try {
  $payload = json_encode([
    'secret'   => 'YOUR_TURNSTILE_SECRET_KEY',
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
  if ($raw === false) {
    throw new RuntimeException('Turnstile verification request failed');
  }
  $ts = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

  if (empty($ts['success'])) {
    http_response_code(403);
    exit('Bot verification failed');
  }
  // Optional: if you set action/hostname on the widget, verify here:
  // if (($ts['hostname'] ?? '') !== 'yourdomain.org') { ... }
} catch (Throwable $e) {
  http_response_code(502);
  exit('Bot verification error');
}

// ------------- 3) Validate & sanitize inputs -------------
$name    = trim((string)($_POST['name']    ?? ''));
$email   = trim((string)($_POST['email']   ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) > 100) {
  http_response_code(400); exit('Invalid name');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
  http_response_code(400); exit('Invalid email');
}
if ($subject === '' || mb_strlen($subject) > 150) {
  http_response_code(400); exit('Invalid subject');
}
if ($message === '' || mb_strlen($message) > 5000) {
  http_response_code(400); exit('Invalid message');
}

// Strip CR/LF from any header-bound fields to prevent header injection
$stripCRLF = static fn(string $s): string => preg_replace('/\r|\n|%0A|%0D/i', '', $s) ?? '';
$emailHeaderSafe   = $stripCRLF($email);
$nameHeaderSafe    = $stripCRLF($name);
$subjectHeaderSafe = $stripCRLF($subject);

// ------------- 4) Load BootstrapMade helper -------------
$receiving_email_address = 'you@yourdomain.org'; // <-- CHANGE THIS

$helperPath = __DIR__ . '/../assets/vendor/php-email-form/php-email-form.php';
if (file_exists($helperPath)) {
  include $helperPath;
} else {
  http_response_code(500);
  exit('Unable to load the "PHP Email Form" Library!');
}

// ------------- 5) Build message via helper -------------
$contact = new PHP_Email_Form;
$contact->ajax = true;                       // echoes a short response for AJAX usage
$contact->to   = $receiving_email_address;

$contact->from_name  = $nameHeaderSafe;
$contact->from_email = $emailHeaderSafe;
$contact->subject    = $subjectHeaderSafe;

// RECOMMENDED: Send via SMTP for deliverability (configure and uncomment)
/*
$contact->smtp = array(
  Host: smtpout.secureserver.net
  Port: 587 (TLS)  or 465 (SSL)   // 587 tends to be more reliable
  Auth: required
  User: you@yourdomain.com
  Pass: your mailbox password
  // Some helper versions support: 'encryption' => 'tls'
);
*/

$contact->add_message($name,    'From');
$contact->add_message($email,   'Email');
$contact->add_message($message, 'Message', 10);

// ------------- 6) Safe attachment handling (allow-list + MIME + size) -------------
$allowedExt  = ['pdf','doc','docx','jpg','jpeg','png','zip'];
$allowedMime = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'image/jpeg',
  'image/png',
  'application/zip',
  'application/x-zip-compressed',
];
$maxBytesPerFile = 5 * 1024 * 1024; // 5MB per file

if (!empty($_FILES['attachment']) && is_array($_FILES['attachment']['name'])) {
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  for ($i = 0, $n = count($_FILES['attachment']['name']); $i < $n; $i++) {
    $err = $_FILES['attachment']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_NO_FILE) continue;
    if ($err !== UPLOAD_ERR_OK)      continue; // or handle specifically

    $size     = (int)($_FILES['attachment']['size'][$i] ?? 0);
    $tmpPath  = $_FILES['attachment']['tmp_name'][$i] ?? '';
    $original = (string)($_FILES['attachment']['name'][$i] ?? '');

    if ($size > $maxBytesPerFile) {
      http_response_code(400); exit('One or more files exceed 5MB');
    }

    $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $mime = $finfo->file($tmpPath) ?: 'application/octet-stream';

    if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
      http_response_code(400); exit('Unsupported file type');
    }

    if (method_exists($contact, 'add_attachment')) {
      $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $original) ?: ("file_$i.$ext");
      $contact->add_attachment($tmpPath, $safeName);
    }
  }
}

// ------------- 7) Send & echo helper response -------------
echo $contact->send();