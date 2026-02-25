<?php
declare(strict_types=1);
session_start();

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
// Load environment variables early so we can use the Secret Key
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    http_response_code(500);
    exit('Configuration missing.');
}

$turnstileToken = $_POST['cf-turnstile-response'] ?? '';
if (!is_string($turnstileToken) || $turnstileToken === '') {
  http_response_code(400);
  exit('Missing bot verification token');
}

try {
  $payload = json_encode([
    'secret'   => $env['TURNSTILE_SECRET'], 
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

// ------------- 3) Collect & Sanitize Form Data -------------
$name = htmlspecialchars($_POST['fullName'] ?? '');
$dob = htmlspecialchars($_POST['dob'] ?? '');
$address = htmlspecialchars($_POST['address'] ?? '');
$city = htmlspecialchars($_POST['city'] ?? '');
$state = htmlspecialchars($_POST['state'] ?? '');
$zip = htmlspecialchars($_POST['zip'] ?? '');
$phone = htmlspecialchars($_POST['phone'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$referral = htmlspecialchars($_POST['referral'] ?? '');

$education = htmlspecialchars($_POST['education'] ?? '');
$occupation = htmlspecialchars($_POST['occupation'] ?? '');
$prevVolunteer = htmlspecialchars($_POST['prevVolunteer'] ?? '');

$skill1 = htmlspecialchars($_POST['skill1'] ?? '');
$skill1_level = htmlspecialchars($_POST['skill1_level'] ?? '');
$skill2 = htmlspecialchars($_POST['skill2'] ?? '');
$skill2_level = htmlspecialchars($_POST['skill2_level'] ?? '');

$locations = isset($_POST['location']) ? implode(", ", $_POST['location']) : 'None Selected';
$needHours = htmlspecialchars($_POST['needHours'] ?? 'No');
$hoursNeeded = htmlspecialchars($_POST['hoursNeeded'] ?? '');
$whyVolunteer = htmlspecialchars($_POST['whyVolunteer'] ?? '');
$ongoing = htmlspecialchars($_POST['ongoing'] ?? 'No');
$availability = htmlspecialchars($_POST['availability'] ?? '');
$transportation = htmlspecialchars($_POST['transportation'] ?? '');

$criminalHistory = htmlspecialchars($_POST['criminalHistory'] ?? 'None provided');
$criminalSteps = htmlspecialchars($_POST['criminalSteps'] ?? 'None provided');

$emergName = htmlspecialchars($_POST['emergName'] ?? '');
$emergRel = htmlspecialchars($_POST['emergRel'] ?? '');
$emergPhone = htmlspecialchars($_POST['emergPhone'] ?? '');
$emergCity = htmlspecialchars($_POST['emergCity'] ?? '');

$waivers = isset($_POST['waivers']) ? implode(", ", $_POST['waivers']) : 'None Selected';
$signature = htmlspecialchars($_POST['signature'] ?? '');
$date = htmlspecialchars($_POST['sigDate'] ?? '');

// Build the Comprehensive Email Body
$body = "<h2>New Volunteer Application</h2>";

$body .= "<h3>Personal Information</h3>";
$body .= "<strong>Name:</strong> {$name}<br>";
$body .= "<strong>DOB:</strong> {$dob}<br>";
$body .= "<strong>Address:</strong> {$address}, {$city}, {$state} {$zip}<br>";
$body .= "<strong>Phone:</strong> {$phone}<br>";
$body .= "<strong>Email:</strong> {$email}<br>";
$body .= "<strong>Referral Source:</strong> {$referral}<br><br>";

$body .= "<h3>Experience & Skills</h3>";
$body .= "<strong>Education:</strong> {$education}<br>";
$body .= "<strong>Occupation:</strong> {$occupation}<br>";
$body .= "<strong>Previous Volunteer Experience:</strong> {$prevVolunteer}<br>";
$body .= "<strong>Skill 1:</strong> {$skill1} ({$skill1_level})<br>";
$body .= "<strong>Skill 2:</strong> {$skill2} ({$skill2_level})<br><br>";

$body .= "<h3>Preferences & Availability</h3>";
$body .= "<strong>Locations Interested:</strong> {$locations}<br>";
$body .= "<strong>Needs Volunteer Hours?:</strong> {$needHours} (Hours needed: {$hoursNeeded})<br>";
$body .= "<strong>Why Volunteer?:</strong> {$whyVolunteer}<br>";
$body .= "<strong>Add to ongoing list?:</strong> {$ongoing}<br>";
$body .= "<strong>Availability:</strong> {$availability}<br>";
$body .= "<strong>Transportation:</strong> {$transportation}<br><br>";

$body .= "<h3>Background & Emergency Contact</h3>";
$body .= "<strong>Criminal History:</strong> {$criminalHistory}<br>";
$body .= "<strong>Steps/Supports Taken:</strong> {$criminalSteps}<br><br>";

$body .= "<strong>Emergency Contact Name:</strong> {$emergName}<br>";
$body .= "<strong>Relationship:</strong> {$emergRel}<br>";
$body .= "<strong>Emergency Phone:</strong> {$emergPhone}<br>";
$body .= "<strong>Emergency City/State/Zip:</strong> {$emergCity}<br><br>";

$body .= "<h3>Agreements & Waivers</h3>";
$body .= "<strong>Waivers Accepted:</strong> {$waivers}<br>";
$body .= "<strong>Electronic Signature:</strong> {$signature}<br>";
$body .= "<strong>Date Signed:</strong> {$date}<br>";

// ------------- 4) Load PHPMailer -------------
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Instantiate the object FIRST!
$mail = new PHPMailer(true);

try {
    // SMTP Settings (Using your hidden .env file)
    $mail->isSMTP();
    $mail->Host       = $env['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['SMTP_USER'];
    $mail->Password   = $env['SMTP_PASS'];
    
    // Check if you need port 465 or 587 based on your GoDaddy setup. 
    // GoDaddy Microsoft 365 often prefers 587 with ENCRYPTION_STARTTLS.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587; 

    // Recipients
    $mail->setFrom($env['SMTP_USER'], 'Bread of Life Website');
    $mail->addAddress($env['SMTP_USER']);
    $mail->addReplyTo($email, $name); 

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Volunteer Application: " . $name;
    
    // Now that $mail exists, we can attach the body you built in Step 3
    $mail->Body    = $body; 
    $mail->AltBody = strip_tags(str_replace(array("<br>", "<h3>"), array("\n", "\n\n--- "), $body));

    $mail->send();
    echo "OK"; // Validate.js needs this exact string

} catch (Exception $e) {
    http_response_code(500);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>