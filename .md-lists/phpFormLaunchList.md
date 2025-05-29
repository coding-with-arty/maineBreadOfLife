✅ Launch Checklist for PHP Website on GoDaddy
1. 📁 File Preparation
Ensure the following files and folders are ready:

/public_html/
├── .env
├── config.php
├── volunteer.html
├── contact-us.html
├── forms/
│   ├── volunteer.php
│   └── contact.php
├── vendor/ (if using Composer/PHPMailer)

2. 🔐 .env File Setup
Create a .env file in the root (same level as config.php) with:

If using GoDaddy’s default relay, you may omit SMTP_USERNAME and SMTP_PASSWORD.

3. ⚙️ config.php Setup
Ensure config.php loads .env and defines constants:


4. 🔒 File Permissions
Set secure permissions:

.env: 600
config.php: 644
forms/: 755
uploads/ (if used): 755 and writable by PHP

5. 🚀 Upload to GoDaddy
Use cPanel File Manager or FTP to upload your files to /public_html/.

6. 🛡️ Security Hardening
.htaccess Rules (already included):
Block access to .env, config.php, and logs
Enable GZIP, caching, and security headers
Force HTTPS
Remove .html extensions from URLs

7. 📬 Test SMTP Email
Submit a test form
Check if email is received
Check reCAPTCHA validation
Confirm success message or redirect

8. 🧪 Final Testing
✅ Forms submit correctly
✅ Emails are delivered
✅ reCAPTCHA works
✅ No PHP errors
✅ HTTPS enforced
✅ SEO-friendly URLs work
