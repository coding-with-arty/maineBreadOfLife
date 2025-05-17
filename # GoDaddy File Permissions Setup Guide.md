# GoDaddy File Permissions Setup Guide

## 1. Create the Permissions Script

## 2. Upload the Script to GoDaddy
Using cPanel File Manager:
Log in to your GoDaddy cPanel
Open "File Manager"
Navigate to your website's root directory (usually public_html)
Click "Upload" and select the fix_permissions.sh file
3. Run the Script
Method 1: Using cPanel File Manager
In File Manager, right-click on fix_permissions.sh
Select "Code Edit" or "Edit"
Click "Edit" if prompted
Verify the script looks correct, then save and close
Right-click on the file and select "Permissions"
Set permissions to 700 (rwx------) and click "Change Permissions"
Method 2: Using SSH (Recommended)
Enable SSH Access in cPanel if not already enabled
Connect using Terminal (Mac/Linux) or PuTTY (Windows):
bash
CopyInsert in Terminal
ssh username@yourdomain.com -p 22
Run these commands:
bash
CopyInsert
## 3. Navigate to your website directory
cd ~/public_html

## 4. Make the script executable
chmod +x fix_permissions.sh

## 5. Run the script
./fix_permissions.sh

## 6. Verify the Changes
After running the script, verify the permissions:

bash
CopyInsert
## 7. Check directory permissions
ls -la

## 8. Check writable directories
ls -la logs/ uploads/ tmp/

## 9. Check a sample of file permissions
find . -type f | xargs ls -l | head -10

## 10. Troubleshooting
Common Issues:
Permission Denied:
Make sure the script is executable: chmod +x fix_permissions.sh
Try running with bash: bash fix_permissions.sh
SSH Access Not Available:
Use cPanel's File Manager to manually set permissions
Or contact GoDaddy support to enable SSH access
Ownership Issues:
Contact GoDaddy support to fix ownership if needed
6. Post-Execution Security
After running the script, secure it by either:

Making it read-only:
bash
CopyInsert in Terminal
chmod 400 fix_permissions.sh
Or moving it outside the web root
7. Required Directory Structure
Ensure these directories exist with proper permissions:

logs/ - For error logs
uploads/ - For user uploads
tmp/ - For temporary files
forms/uploads/ - For form uploads
8. Final Checks
Verify forms can write to upload directories
Check that sensitive files are not accessible
Confirm the website functions correctly
📅 Last Updated: May 15, 2025
🔒 Security Level: Production
👤 Technical Contact: [Your Name]
📧 Support Email: [Your Email]

