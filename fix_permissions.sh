#!/bin/bash
# Bread of Life Website - Permissions Fix Script
# Save this as fix_permissions.sh

echo "=== Setting up file permissions for Bread of Life website ==="
echo "This script will set secure permissions for your website files."
echo "------------------------------------------------------------"

# Navigate to the website root (public_html or subdirectory)
# Uncomment and modify the following line if your site is in a subdirectory
# cd ~/public_html/your-subdirectory

# Set directory permissions (755)
echo "Setting directory permissions to 755..."
find . -type d -exec chmod 0755 {} \;

# Set file permissions (644)
echo "Setting file permissions to 644..."
find . -type f -exec chmod 0644 {} \;

# Set special permissions for writable directories
echo "Setting special permissions for writable directories..."
for dir in logs uploads tmp forms/uploads; do
    if [ -d "$dir" ]; then
        echo "Setting 775 permissions for $dir"
        find "$dir" -type d -exec chmod 0775 {} \;
        find "$dir" -type f -exec chmod 0664 {} \;
    fi
done

# Set execute permissions for specific files
echo "Setting execute permissions for scripts..."
for file in *.sh *.pl *.cgi; do
    if [ -f "$file" ]; then
        chmod +x "$file"
    fi
done

# Set ownership (uncomment and modify if you have SSH access with appropriate permissions)
# echo "Setting ownership..."
# chown -R username:group .

echo "------------------------------------------------------------"
echo "Permission settings complete!"
echo "Please verify the changes using:"
echo "  ls -la"
echo "  ls -la logs/ uploads/ tmp/"
echo "------------------------------------------------------------"