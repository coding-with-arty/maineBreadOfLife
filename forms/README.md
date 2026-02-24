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
