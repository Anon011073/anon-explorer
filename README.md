# Zipply-Drive

A modern, self-hosted file explorer, file manager, and secure file sharing platform.

## Features
- Isolated User Spaces with Storage Quotas
- Public Files Repository
- Admin Root Browser with System Protection
- Secure Share Links (Password/Expiration)
- Integrated Monaco Code Editor
- Professional Dark/Light Themes
- Drag & Drop Uploads & ZIP Support
- Modular Plugin Architecture

## Installation (WAMP/XAMPP/Laragon)
1. **Requirements:** PHP 8.2+, PDO SQLite extension enabled.
2. **Download:** Drop the project files into your web root (e.g., `C:/laragon/www/zipply-drive`).
3. **Permissions:** Ensure the `storage/` and `public/uploads/` directories are writable by the web server.
4. **Setup:** Visit the URL in your browser (e.g., `http://localhost/zipply-drive`).
5. **Installer:** Follow the Zipply-Drive installer wizard to create your admin account.

## Troubleshooting
- **"Could not find driver":** Enable `extension=pdo_sqlite` in your `php.ini` file and restart your web server.
- **404 Not Found:** Ensure Apache's `mod_rewrite` is enabled and `.htaccess` files are being read (`AllowOverride All`).
