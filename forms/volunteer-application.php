<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust the path to where you place the PHPMailer library
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and collect basic input
    $name = htmlspecialchars($_POST['fullName'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $locations = isset($_POST['location']) ? implode(", ", $_POST['location']) : 'None Selected';
    $signature = htmlspecialchars($_POST['signature'] ?? '');
    $date = htmlspecialchars($_POST['sigDate'] ?? '');

    $mail = new PHPMailer(true);

    try {
        // GoDaddy SMTP Server Settings
        $mail->isSMTP();
        $mail->Host       = 'mail.yourdomain.com'; // UPDATE THIS (or 'localhost' depending on GoDaddy setup)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'volunteer@yourdomain.com'; // UPDATE THIS
        $mail->Password   = 'YourEmailPasswordHere';    // UPDATE THIS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usually SMTPS (465) or STARTTLS (587) for GoDaddy
        $mail->Port       = 465; // UPDATE THIS (465 or 587)

        // Recipients
        $mail->setFrom('volunteer@yourdomain.com', 'Bread of Life Website'); // UPDATE THIS
        $mail->addAddress('admin@breadoflife.org', 'Volunteer Coordinator'); // UPDATE THIS (Where to send applications)
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Volunteer Application: ' . $name;
        
        // Build the Email Body
        $body = "<h2>New Volunteer Application</h2>";
        $body .= "<strong>Name:</strong> {$name}<br>";
        $body .= "<strong>Email:</strong> {$email}<br>";
        $body .= "<strong>Phone:</strong> {$phone}<br>";
        $body .= "<strong>Locations Interested:</strong> {$locations}<br><br>";
        
        $body .= "<strong>Education:</strong> " . htmlspecialchars($_POST['education'] ?? '') . "<br>";
        $body .= "<strong>Occupation:</strong> " . htmlspecialchars($_POST['occupation'] ?? '') . "<br>";
        $body .= "<strong>Why Volunteer?:</strong> " . htmlspecialchars($_POST['whyVolunteer'] ?? '') . "<br><br>";
        
        $body .= "<strong>Emergency Contact:</strong> " . htmlspecialchars($_POST['emergName'] ?? '') . " (" . htmlspecialchars($_POST['emergPhone'] ?? '') . ")<br><br>";
        
        $body .= "<strong>Signed By:</strong> {$signature}<br>";
        $body .= "<strong>Date:</strong> {$date}<br>";
        $body .= "<hr><p><small>All 6 waivers were checked and accepted via the web form.</small></p>";

        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace("<br>", "\n", $body));

        $mail->send();
        // Redirect to a thank you page
        echo "<script>alert('Application Submitted Successfully!'); window.location.href='index.html';</script>";
        
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Direct access not allowed.";
}
?>