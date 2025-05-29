<?php
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

// Set error reporting for debugging (enabled for GoDaddy troubleshooting)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to log errors (useful for debugging on GoDaddy)
function logError($message) {
    $logFile = __DIR__ . '/form_errors.log';

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

// Check if the request is a POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        logError('Security validation failed. Please try again.');
        $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../contact-us.html';
        $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'status=error&message=' . urlencode('Security validation failed. Please try again.');
        header('Location: ' . $redirect_url);
        exit();
    }
    // Initialize variables for error handling and input sanitization
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : null;
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : null;
    $topic = isset($_POST['topic']) ? htmlspecialchars(trim($_POST['topic'])) : null;
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : null;
    $attachments = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;
    $uploadedFiles = []; // Initialize array for uploaded files

    // Check if all required fields are filled
    if (!$name || !$email || !$topic || !$message) {
        logError("All fields are required. Please fill out the form completely.");
        $redirect_url = '../contact-us.html';
        $redirect_url .= '?status=error&message=' . urlencode('All fields are required. Please fill out the form completely.');
        header('Location: ' . $redirect_url);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logError("Invalid email format.");
        $redirect_url = '../contact-us.html';
        $redirect_url .= '?status=error&message=' . urlencode('Invalid email format.');
        header('Location: ' . $redirect_url);
        exit();
    }

    // Verify reCAPTCHA if enabled in config
    if (defined('RECAPTCHA_SECRET_KEY') && isset($_POST['g-recaptcha-response'])) {
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $secretKey = RECAPTCHA_SECRET_KEY;

        // GoDaddy sometimes has issues with file_get_contents for external URLs
        // Try using cURL first, fallback to file_get_contents
        $verifyResponse = false;

        if (function_exists('curl_version')) {
            // Use cURL if available (more reliable on GoDaddy)
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

        if ($verifyResponse && intval($responseKeys["success"]) !== 1) {
            logError('reCAPTCHA verification failed. Please try again.');
            $redirect_url = '../contact-us.html';
            $redirect_url .= '?status=error&message=' . urlencode('reCAPTCHA verification failed. Please try again.');
            header('Location: ' . $redirect_url);
            exit();
        }
    } else if (defined('RECAPTCHA_SECRET_KEY')) {
        logError('reCAPTCHA response not provided. Please try again.');
        $redirect_url = '../contact-us.html';
        $redirect_url .= '?status=error&message=' . urlencode('reCAPTCHA response not provided. Please try again.');
        header('Location: ' . $redirect_url);
        exit();
    }

    // Process uploaded files if any
    if ($attachments && $attachments['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
            'application/zip',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        // GoDaddy typically has a lower upload limit
        $maxFileSize = 8 * 1024 * 1024; // 8 MB is safer for GoDaddy

        // Create upload directory if it doesn't exist (important for GoDaddy)
        $uploadDir = __DIR__ . '/uploads/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                logError("Failed to create upload directory. Please check permissions.");
                header("Location: ../contact-us.html");
                exit();
            }
        }

        foreach ($attachments['name'] as $key => $fileName) {
            $fileType = $attachments['type'][$key];
            $fileSize = $attachments['size'][$key];
            $fileTmp = $attachments['tmp_name'][$key];

            // Skip empty files
            if (empty($fileName) || $fileSize <= 0) {
                continue;
            }

            // Validate file size
            if ($fileSize > $maxFileSize) {
                logError("File '$fileName' exceeds the maximum allowed size (8MB).");
                $redirect_url = '../contact-us.html';
                $redirect_url .= '?status=error&message=' . urlencode("File '$fileName' exceeds the maximum allowed size (8MB).");
                header('Location: ' . $redirect_url);
                exit();
            }

            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                logError("File type for '$fileName' is not allowed. Please upload PDF, Word, Excel, or image files only.");
                $redirect_url = '../contact-us.html';
                $redirect_url .= '?status=error&message=' . urlencode("File type for '$fileName' is not allowed. Please upload PDF, Word, Excel, or image files only.");
                header('Location: ' . $redirect_url);
                exit();
            }

            // Get file extension for extra security
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'xls', 'xlsx'];

            if (!in_array($fileExt, $allowedExts)) {
                logError("File extension '.$fileExt' is not allowed for '$fileName'.");
                $redirect_url = '../contact-us.html';
                $redirect_url .= '?status=error&message=' . urlencode("File extension '.$fileExt' is not allowed for '$fileName'.");
                header('Location: ' . $redirect_url);
                exit();
            }

            // Create a unique filename to prevent overwriting
            $newFileName = time() . '_' . md5($fileName) . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmp, $destination)) {
                logError("Failed to upload file '$fileName'. Please check folder permissions.");
                $redirect_url = '../contact-us.html';
                $redirect_url .= '?status=error&message=' . urlencode("Failed to upload file '$fileName'. Please check folder permissions.");
                header('Location: ' . $redirect_url);
                exit();
            }

            // Store the file path for email attachment
            $uploadedFiles[] = [
                'path' => $destination,
                'name' => $fileName
            ];
        }
    }

    // If everything is valid, send email - GoDaddy optimized version
    $to = "receptionist@mainebreadoflife.org"; // Replace with your recipient email address
    $subject = "New Contact Form Submission: $topic";

    // Create a boundary for the multipart message
    $boundary = md5(time());

    // Headers optimized for GoDaddy
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: Bread of Life <no-reply@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'mainebreadoflife.org') . ">\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Email body
    $message_body = "--$boundary\r\n";
    $message_body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message_body .= "You have received a new message from your website's contact form:\r\n\r\n";
    $message_body .= "Name: $name\r\n";
    $message_body .= "Email: $email\r\n";
    $message_body .= "Topic: $topic\r\n";
    $message_body .= "Message:\r\n$message\r\n\r\n";

    // Add attachments if any
    if (!empty($uploadedFiles)) {
        foreach ($uploadedFiles as $file) {
            if (file_exists($file['path'])) {
                $message_body .= "--$boundary\r\n";
                $message_body .= "Content-Type: application/octet-stream; name=\"" . $file['name'] . "\"\r\n";
                $message_body .= "Content-Transfer-Encoding: base64\r\n";
                $message_body .= "Content-Disposition: attachment; filename=\"" . $file['name'] . "\"\r\n\r\n";
                $message_body .= chunk_split(base64_encode(file_get_contents($file['path']))) . "\r\n";
            }
        }
    }

    // Close the message boundary
    $message_body .= "--$boundary--";

    // Send the email - GoDaddy specific handling
    // Add a small delay before sending to prevent rate limiting
    usleep(100000); // 100ms delay

    // Set additional parameters for GoDaddy mail servers
    $additional_params = null;

    // On GoDaddy, sometimes you need to specify the sendmail path
    if (ini_get('sendmail_path')) {
        $additional_params = '-f' . 'no-reply@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'mainebreadoflife.org');
    }

    // Send the email with proper parameters
    $mail_sent = mail($to, $subject, $message_body, $headers, $additional_params);

    if ($mail_sent) {
        // Send auto-response to the person who submitted the form
        $auto_subject = "Thank you for contacting Bread of Life";
        $auto_headers = "MIME-Version: 1.0\r\n";
        $auto_headers .= "From: Bread of Life <no-reply@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'mainebreadoflife.org') . ">\r\n";
        $auto_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $auto_message = "Dear $name,\r\n\r\n";
        $auto_message .= "Thank you for contacting Bread of Life. We have received your message regarding '$topic'.\r\n\r\n";
        $auto_message .= "Our team will review your message and get back to you as soon as possible.\r\n\r\n";
        $auto_message .= "Feed - Shelter - Empower,\r\n";
        $auto_message .= "The Bread of Life Team\r\n";
        $auto_message .= "https://mainebreadoflife.org";

        // Don't worry if auto-response fails
        @mail($email, $auto_subject, $auto_message, $auto_headers);

        // Use URL parameters instead of session
        $redirect_url = '../contact-us.html';
        $redirect_url .= '?status=success&message=' . urlencode('Thank you for contacting us. Your message has been sent successfully.');
        header('Location: ' . $redirect_url);
        exit();
    } else {
        // Log the mail error for debugging
        $error_message = "Unable to send the email.";
        if (function_exists('error_get_last') && error_get_last() && isset(error_get_last()['message'])) {
            $error_message .= " Mail system error: " . error_get_last()['message'];
        }
        logError($error_message);
        $redirect_url = '../contact-us.html';
        $redirect_url .= '?status=error&message=' . urlencode('Unable to send the email. Please try again later or contact us directly.');
        header('Location: ' . $redirect_url);
        exit();
    }
} else {
    logError("Invalid request method. Please use the form to submit your request.");
    $redirect_url = '../contact-us.html';
    $redirect_url .= '?status=error&message=' . urlencode('Invalid request method. Please use the form to submit your request.');
    header('Location: ' . $redirect_url);
    exit();
}

// Flush output buffer before ending script
ob_end_flush();
?>