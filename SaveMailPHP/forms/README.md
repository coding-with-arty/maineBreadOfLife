# ✅ GoDaddy + PHP Contact Form — Complete Setup Checklist

A fully‑structured guide for deploying a secure, self‑hosted PHP contact form on GoDaddy cPanel hosting with SMTP, Cloudflare Turnstile, CSRF protection, and optional file uploads.

### A) GoDaddy Hosting Settings (Server‑Side Requirements)

1. Confirm You Are on Linux cPanel Hosting
   Your PHP form requires Linux + cPanel.\ If you are already using GoDaddy cPanel, you’re good.
2. Make Sure SSL Is Active
   Ensure your SSL certificate is installed so your form loads via:
   <https://augustadowntownalliance.org>
3. Enable allow_url_fopen OR Prepare cURL
   Turnstile verification requires an external HTTPS request.

#### Many GoDaddy plans allow outbound HTTPS

    If file_get_contents() fails, switch to cURL.

4. Set PHP Upload Limits (Recommended)
   Create/edit .user.ini:
   upload_max_filesize = 5M
   post_max_size = 8M
   max_execution_time = 60

#### GoDaddy uses .user.ini for PHP directives

5. Set Up Your GoDaddy Domain Email
   You already have domain email service — perfect.
6. Choose the Correct SMTP Setup
   GoDaddy blocks outbound SMTP to external servers on shared plans.\ Use GoDaddy’s SMTP only, unless you are on VPS.
   Most common setup (Workspace / Professional / Office 365 email):
   Host: smtpout.secureserver.net Port: 587 (TLS) ← recommended Auth: YES Username: your full email Password: your email password
   If port 587 fails, try:
   Port: 80 Encryption: TLS
7. SPF Record (Mandatory)
   Add one SPF TXT record:
   v=spf1 include:secureserver.net -all
   Never create multiple SPF records — combine into one.
8. DKIM / DMARC (Recommended)
   Enable DKIM in GoDaddy’s email admin.
   Add DMARC:
   \_dmarc.yourdomain.com TXT "v=DMARC1; p=none; rua=mailto:you@yourdomain.com"

### B) Cloudflare Turnstile (Bot Protection)

9. Register Domain in Cloudflare Turnstile

#### Add your domain, Get Site Key (HTML), Get Secret Key (PHP)

10. Add Turnstile Widget Script to HTML

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

11. PHP Must POST the Token
    Turnstile requires POST, not GET.
    Your contact.php already handles this properly.

#### C) CSRF Token (Required for Security)

12. Generate CSRF Token on the Form Page
    session_start();
    if (empty($\_SESSION['csrf_token'])) {
    $\_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

13. Add Hidden Input to HTML Form
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

14. Validate CSRF Token in contact.php
    hash_equals($\_SESSION['csrf_token'], $\_POST['csrf_token'])

#### D) Your contact.php — Required Checks

15. Update the Recipient Email Address
    $receiving_email_address = '<you@yourdomain.com>';

16. Confirm Correct Path to the Helper
    Default:
    /assets/vendor/php-email-form/php-email-form.php
    Your file shows:
    ../assets/vendor/php-email-form/php-email-form.php
    Ensure the path is correct relative to contact.php.
    If wrong, you’ll get:

#### “Unable to load the PHP Email Form library”

17. Enable SMTP (Strongly Recommended)
    $contact->smtp = array(
    'host' => 'smtpout.secureserver.net',
    'username' => '<you@yourdomain.com>',
    'password' => 'YOUR_EMAIL_PASSWORD',
    'port' => '587',
    'encryption' => 'tls'
    );
    ``

#### If port 587 fails

    Port: 80    Encryption: tls

18. Attachments (Optional but Fully Supported)
    Your code already:

Validates file type
Limits file size
Sanitizes filenames
Matches OWASP file upload guidance.

19. Input Sanitization (Already Implemented)
    Your script:

trims input
validates email
limits length
prevents header injection
This aligns with OWASP input validation best practices.

#### E) Final Quick‑Test Checklist (Live Server)

20. Form Loads Over HTTPS

#### Padlock visible

No mixed‑content errors

21. CSRF Token Exists
    View page source → confirm hidden field.
22. Turnstile Widget Appears
    Must load without errors.
23. Submit WITHOUT Turnstile
    It should fail → confirms validation works.
24. Submit WITH Valid Token
    Should return success.
25. Check Email Inbox
    If not receiving mail:

Try SMTP ports: 587 → 80 → 465 → 25
Verify SPF
Enable DKIM
Check GoDaddy send limits (~500/day) 26. Test Attachments
Verify PDF/JPG ≤ 5MB sends correctly.


==============================================================

Phase 1: Local File Preparation
Before you open GoDaddy, double-check that you have these exact files ready on your computer:

[ ] contact.php (The visible webpage, previously contact.html)

[ ] volunteer-application.php (The visible webpage, previously volunteer-application.html)

[ ] forms/contact.php (The hidden processor script)

[ ] forms/volunteer-application.php (The hidden processor script)

[ ] forms/.env (Your secret keys file)

[ ] .htaccess (The updated version containing the .env block and .php routing)

[ ] The PHPMailer folder (Downloaded from GitHub, specifically the src folder)

Phase 2: Uploading to GoDaddy (cPanel / File Manager)
Log into GoDaddy, open your cPanel Admin, and open the File Manager. Navigate to your public_html directory (this is your root folder).

[ ] Upload the PHPMailer Library: Upload the PHPMailer folder directly into public_html. (Path should be public_html/PHPMailer/src/PHPMailer.php).

[ ] Upload the .htaccess File: Upload your new .htaccess file to the root public_html folder. (Note: You may need to click "Settings" in the top right of File Manager and check "Show Hidden Files (dotfiles)" to see it).

[ ] Delete the Old HTML Pages: Delete your old contact.html and volunteer-application.html files from the server. If you leave them, they might conflict with your new PHP files.

[ ] Upload the New PHP Pages: Upload the visible contact.php and volunteer-application.php files into the root public_html folder.

[ ] Upload the Processor Scripts: Open your forms folder. Upload the backend contact.php and volunteer-application.php files here.

[ ] Upload the .env file: Upload your .env file into the forms folder.

Phase 3: GoDaddy / Microsoft 365 Email Settings Check
Since you are using GoDaddy, your email is likely hosted on Microsoft 365. Microsoft has strict security rules that can sometimes block automated emails.

[ ] Verify SMTP Details in .env: Make sure your .env file uses the correct Microsoft 365 SMTP details:

SMTP_HOST="smtp.office365.com"

SMTP_USER="receptionist@mainebreadoflife.org"

SMTP_PASS="your_password"

[ ] Check SMTP Authentication: By default, Microsoft 365 disables "Authenticated SMTP".

Go to your Microsoft 365 Admin Center -> Users -> Active Users -> Click the receptionist user -> Click Mail -> Click Manage email apps.

Make sure Authenticated SMTP is checked/enabled.

[ ] App Passwords (If 2FA is on): If the receptionist email has Two-Factor Authentication (MFA) enabled, your regular email password will not work in the .env file. You must generate an "App Password" in your Microsoft account settings and put that App Password in your .env file instead.

Phase 4: Security Verification
Before testing the forms, ensure your secrets are actually protected.

[ ] Test .env Protection: Open your web browser and manually type in https://mainebreadoflife.org/forms/.env.

You should immediately get a 403 Forbidden error. If it downloads the file or shows the text, your .htaccess rule isn't working, and you must delete the .env file immediately until it is fixed!

Phase 5: Live Testing
It's time to test the flow exactly as a user would.

[ ] Clear your browser cache or open a private/incognito window.

[ ] Go to https://mainebreadoflife.org/contact/. Ensure the form loads and the Cloudflare Turnstile box appears.

[ ] Fill out the form with test data and hit Submit.

[ ] Look for the Success Message: The Javascript should say "Loading", and then show your success message. (If the page goes blank white and just says "OK", it means you forgot to add the php-email-form class to your <form> tag!).

[ ] Check the Inbox: Open the receptionist@mainebreadoflife.org inbox and verify the email arrived. Reply to it to ensure the Reply-To function works.

[ ] Repeat the test for the Volunteer Application.

Troubleshooting Cheat Sheet (Just in case!)
Error 500 / "Message could not be sent": Usually means your email password is wrong, SMTP Auth is disabled in Microsoft 365, or the .env file cannot be found by the script.

Error 403 / "CSRF validation failed": Means your visible page didn't generate the token correctly. Ensure session_start(); is on exactly Line 1 of the visible page with no blank spaces above it.

Error 400 / "Missing bot verification token": Means the Cloudflare Turnstile Javascript didn't load, or the user clicked submit before checking the box.