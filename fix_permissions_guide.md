# Key Features of the Guide:
# Visual, Click-by-Click Instructions - Perfect for cPanel's graphical interface
# Manual Permission Settings - Explains how to set permissions without running the script directly
# Troubleshooting Section - Covers common issues and their solutions
# Alternative Methods - Includes instructions if you find Terminal/SSH access in your cPanel
# Verification Steps - Shows how to confirm permissions were set correctly
# How to Use the Guide:
# Upload the fix_permissions_guide.md to your GoDaddy hosting's public_html directory
# Follow the instructions in the guide using cPanel's File Manager
# The guide maintains all the security considerations from your original script


# How to Run fix_permissions.sh in cPanel (Without Shell Access)

This guide will walk you through running the `fix_permissions.sh` script using only cPanel's File Manager interface.

## Prerequisites
- cPanel access to your GoDaddy hosting account
- The `fix_permissions.sh` file already uploaded to your `public_html` directory

## Step-by-Step Instructions

### 1. Log in to cPanel
1. Go to your GoDaddy account and log in
2. Navigate to your hosting dashboard
3. Click on "cPanel Admin" or "cPanel"

### 2. Open File Manager
1. In cPanel, find and click on "File Manager" (usually under the "Files" section)
2. If prompted, select "Document Root for [yourdomain.com]" and check "Show Hidden Files"
3. Click "Go"

### 3. Locate and Edit the Script
1. In File Manager, navigate to `public_html`
2. Find `fix_permissions.sh` in the file list
3. Right-click on it and select "Code Edit" or "Edit"
4. If prompted about encoding, choose "UTF-8" and click "Edit"

### 4. Run the Script Manually

Since you can't execute shell scripts directly in cPanel, we'll run the commands manually:

1. First, set directory permissions to 755:
   - In File Manager, select all directories (click the first, hold Shift, click the last)
   - Right-click and select "Change Permissions"
   - Enter `755` and check "Recurse into subdirectories"
   - Click "Change Permissions"

2. Set file permissions to 644:
   - In File Manager, click on a directory
   - Click "Select All" at the top
   - Click "Change Permissions"
   - Enter `644` and ensure "Recurse into subdirectories" is checked
   - Click "Change Permissions"

3. For writable directories (logs, uploads, tmp, forms/uploads):
   - Navigate to each directory
   - Set permissions to `775` for the directory
   - Inside each, select all files and set to `664`

### 5. Set Execute Permissions for Scripts
1. In File Manager, locate any `.sh`, `.pl`, or `.cgi` files
2. For each file:
   - Right-click and select "Change Permissions"
   - Check the "Execute" boxes for Owner, Group, and Public
   - Click "Change Permissions"

## Verifying the Changes

1. Check a few key files to ensure permissions are set correctly:
   - `.htaccess` should be 644
   - `index.php`/`index.html` should be 644
   - Directories should be 755
   - Writable directories should be 775

## Important Notes

- Always back up your site before making permission changes
- Some directories/files might require different permissions based on your application
- If you encounter 500 errors after changing permissions, check your error logs
- For WordPress sites, wp-content/uploads typically needs 755 for directories and 644 for files

## Troubleshooting

- **403 Forbidden errors**: Usually means files aren't readable (needs 644) or directories aren't executable (needs 755)
- **500 Internal Server Error**: Check error logs in cPanel > Error Log
- **Upload issues**: Ensure upload directories are writable (775)

## Alternative: Using cPanel's "Terminal"

If your hosting has Terminal access in cPanel:

1. Go to cPanel and find "Terminal" or "SSH Access"
2. Run these commands one at a time:
   ```bash
   cd ~/public_html
   chmod -R 755 ./
   find . -type f -exec chmod 644 {} \;
   find . -name '*.sh' -exec chmod +x {} \;
   ```

---
*Note: These instructions are specifically tailored for the Bread of Life website structure and GoDaddy hosting environment.*
