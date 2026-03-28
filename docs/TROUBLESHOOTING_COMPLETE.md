# Nexus - Complete Troubleshooting Guide

**Every Possible Error with Solutions**

Last Updated: March 27, 2026

---

## Table of Contents

1. [System Requirements Errors](#1-system-requirements-errors)
2. [PHP Dependency Errors](#2-php-dependency-errors)
3. [Node.js/npm Errors](#3-nodejsnpm-errors)
4. [Environment Setup Errors](#4-environment-setup-errors)
5. [Database Errors](#5-database-errors)
6. [Migration Errors](#6-migration-errors)
7. [Frontend Build Errors](#7-frontend-build-errors)
8. [Storage & Permission Errors](#8-storage--permission-errors)
9. [Runtime Errors](#9-runtime-errors)
10. [Email Errors](#10-email-errors)
11. [Queue Errors](#11-queue-errors)
12. [Authentication Errors](#12-authentication-errors)

---

## 1. System Requirements Errors

### 1.1 PHP Not Installed

**Error:**
```
✗ PHP is not installed!
```

**Solution (Linux/macOS):**
```bash
sudo apt update
sudo apt install php php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 php-mysql php-bcmath php-gd
```

**Solution (Windows):**
1. Download from https://windows.php.net/download/
2. Extract to `C:\php`
3. Add to PATH: `System Properties → Environment Variables → Path → New → C:\php`
4. Copy `php.ini-development` to `php.ini`
5. Enable extensions in php.ini

**Verify:**
```bash
php -v
```

---

### 1.2 PHP Version Too Old

**Error:**
```
✗ PHP 8.2 or higher is required! You have 8.1.2
```

**Solution (Linux/macOS):**
```bash
# Ubuntu/Debian
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql php8.3-bcmath php8.3-gd

# macOS (Homebrew)
brew install php@8.3
```

**Solution (Windows):**
1. Download PHP 8.3 from https://windows.php.net/download/
2. Replace old PHP installation
3. Update PATH if needed

**Verify:**
```bash
php -v  # Should show 8.2 or higher
```

---

### 1.3 Missing PHP Extensions

**Error:**
```
✗ Missing PHP extensions: mbstring, xml, curl
```

**Solution (Linux/macOS):**
```bash
sudo apt install php-mbstring php-xml php-curl php-zip php-mysql php-bcmath php-gd
sudo systemctl restart apache2  # or php8.3-fpm
```

**Solution (Windows):**
Edit `php.ini`:
```ini
extension=mbstring
extension=curl
extension=xml
extension=zip
extension=pdo_mysql
extension=bcmath
extension=gd
```
Then restart web server.

**Verify:**
```bash
php -m | grep -E "mbstring|xml|curl|zip|mysql|bcmath|gd"
```

---

### 1.4 Composer Not Installed

**Error:**
```
✗ Composer is not installed!
```

**Solution (Linux/macOS):**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Solution (Windows):**
1. Download from https://getcomposer.org/download/
2. Run installer
3. Follow prompts

**Verify:**
```bash
composer --version
```

---

### 1.5 Node.js Not Installed

**Error:**
```
✗ Node.js is not installed!
```

**Solution (Linux/macOS):**
```bash
# Linux
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# macOS
brew install node@20
```

**Solution (Windows):**
1. Download from https://nodejs.org/
2. Run installer (LTS version recommended)

**Verify:**
```bash
node -v
npm -v
```

---

### 1.6 npm Not Installed

**Error:**
```
✗ npm is not installed!
```

**Solution:**
npm comes with Node.js. Reinstall Node.js from https://nodejs.org/

**Verify:**
```bash
npm -v
```

---

### 1.7 Git Not Installed

**Error:**
```
✗ Git is not installed!
```

**Solution (Linux/macOS):**
```bash
sudo apt install git  # Linux
brew install git      # macOS
```

**Solution (Windows):**
Download from https://git-scm.com/download/win

**Verify:**
```bash
git --version
```

---

### 1.8 MySQL Client Not Found (Optional)

**Warning:**
```
MySQL client not found (optional, for database creation)
```

**Solution (Linux/macOS):**
```bash
sudo apt install mysql-client  # Linux
brew install mysql             # macOS
```

**Solution (Windows):**
Download MySQL from https://dev.mysql.com/downloads/mysql/

**Note:** This is optional. You can still use SQLite or manually create MySQL database.

---

## 2. PHP Dependency Errors

### 2.1 Composer Install Fails

**Error:**
```
Failed to install PHP dependencies
```

**Possible Causes & Solutions:**

**Cause 1: composer.json missing**
```bash
ls -la composer.json
# If missing, re-clone repository
```

**Cause 2: PHP version mismatch**
```bash
php -v  # Check version
# Update PHP if needed
```

**Cause 3: Memory limit**
```bash
php -d memory_limit=-1 composer.phar install
```

**Cause 4: Network issues**
```bash
composer config --global disable-tls true
composer install
```

**Cause 5: Corrupted cache**
```bash
composer clear-cache
composer install
```

---

### 2.2 Composer Memory Error

**Error:**
```
PHP Fatal error: Allowed memory size exhausted
```

**Solution:**
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

Or edit composer.json:
```json
"config": {
    "memory-limit": "-1"
}
```

---

### 2.3 Composer Timeout

**Error:**
```
[Composer\Downloader\TransportException]
The "https://..." file could not be downloaded: timed out
```

**Solution:**
```bash
composer config --global process-timeout 2000
composer config --global disable-tls true
composer install
```

---

## 3. Node.js/npm Errors

### 3.1 npm Install Fails

**Error:**
```
npm ERR! code EACCES
npm ERR! permission denied
```

**Solution (Linux/macOS):**
```bash
# Fix npm permissions
mkdir ~/.npm-global
npm config set prefix '~/.npm-global'
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.bashrc
source ~/.bashrc

# OR use sudo (not recommended)
sudo npm install
```

**Solution (Windows):**
Run Command Prompt as Administrator

---

### 3.2 npm Install Fails - Network

**Error:**
```
npm ERR! code ENOTFOUND
npm ERR! errno ENOTFOUND
```

**Solution:**
```bash
# Clear npm cache
npm cache clean --force
npm cache verify

# Change registry
npm config set registry https://registry.npmjs.org/

# Retry
npm install
```

---

### 3.3 npm Build Fails

**Error:**
```
ERROR in Module not found
```

**Solution:**
```bash
# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

### 3.4 Vite Build Error

**Error:**
```
[vite]: Rollup failed to resolve import
```

**Solution:**
```bash
# Clear cache
rm -rf node_modules/.vite

# Reinstall dependencies
npm install

# Rebuild
npm run build
```

---

## 4. Environment Setup Errors

### 4.1 .env File Missing

**Error:**
```
.env file not found
```

**Solution:**
```bash
cp .env.example .env
php artisan key:generate
```

---

### 4.2 Application Key Missing

**Error:**
```
No application encryption key has been specified
```

**Solution:**
```bash
php artisan key:generate
```

---

### 4.3 .env Overwrite Prompt

**Prompt:**
```
Overwrite existing .env? (y/n) [n]:
```

**Response:**
- `y` - Overwrite with .env.example
- `n` or Enter - Keep existing .env

---

## 5. Database Errors

### 5.1 SQLite Database Locked

**Error:**
```
SQLSTATE[HY000]: General error: 5 database is locked
```

**Solution:**
```bash
# Check file permissions
chmod 666 database/database.sqlite

# Close other connections
# Clear cache
php artisan cache:clear

# Use MySQL for production
```

---

### 5.2 MySQL Connection Refused

**Error:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solution:**
```bash
# Check MySQL is running
sudo systemctl status mysql
sudo systemctl start mysql

# Check credentials in .env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=correct_password

# Test connection
mysql -h 127.0.0.1 -P 3306 -u nexus_user -p
```

---

### 5.3 Database Does Not Exist

**Error:**
```
SQLSTATE[HY000] [1049] Unknown database 'nexus'
```

**Solution:**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# OR use setup script
./setup.sh  # Choose option 1 (Create new database)
```

---

### 5.4 Access Denied for User

**Error:**
```
SQLSTATE[HY000] [1045] Access denied for user 'nexus_user'@'localhost'
```

**Solution:**
```bash
# Reset user privileges
mysql -u root -p -e "
CREATE USER IF NOT EXISTS 'nexus_user'@'localhost' IDENTIFIED BY 'new_password';
GRANT ALL PRIVILEGES ON nexus.* TO 'nexus_user'@'localhost';
FLUSH PRIVILEGES;
"

# Update .env with new password
```

---

### 5.5 MySQL Connection Test Fails

**Error:**
```
Cannot connect to MySQL with provided credentials!
Please check:
  1. MySQL server is running
  2. Host and port are correct
  3. Username and password are valid
```

**Solution:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Start MySQL
sudo systemctl start mysql

# Test connection manually
mysql -h 127.0.0.1 -P 3306 -u root -p

# If connection works, check credentials in setup script
```

---

## 6. Migration Errors

### 6.1 Migration Table Already Exists

**Error:**
```
SQLSTATE[42S01]: Base table or view already exists: Table 'migrations' already exists
```

**Solution:**
```bash
# Fresh migrate (WARNING: deletes all data)
php artisan migrate:fresh

# OR rollback and migrate again
php artisan migrate:rollback
php artisan migrate
```

---

### 6.2 Foreign Key Constraint Fails

**Error:**
```
SQLSTATE[HY000]: Foreign key constraint fails
```

**Solution:**
```bash
# Disable foreign key checks temporarily
mysql -u root -p -e "SET FOREIGN_KEY_CHECKS=0;"
php artisan migrate:fresh
mysql -u root -p -e "SET FOREIGN_KEY_CHECKS=1;"

# OR check migration order
php artisan migrate:status
```

---

### 6.3 Migration Failed - Duplicate Column

**Error:**
```
SQLSTATE[42S21]: Column already exists
```

**Solution:**
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# OR fresh migrate
php artisan migrate:fresh
```

---

### 6.4 Migration Timeout

**Error:**
```
max_execution_time exceeded
```

**Solution:**
```bash
# Increase PHP timeout
php -d max_execution_time=300 artisan migrate

# OR edit php.ini
max_execution_time = 300
```

---

## 7. Frontend Build Errors

### 7.1 Vite Manifest Not Found

**Error:**
```
Vite manifest not found at: public/build/manifest.json
```

**Solution:**
```bash
# Build assets
npm run build

# Clear view cache
php artisan view:clear
```

---

### 7.2 Mixed Content Error (HTTPS)

**Error:**
```
Mixed Content: The page was loaded over HTTPS, but requested an insecure resource
```

**Solution:**
```env
# .env
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIES=true
```

Add to middleware:
```php
// app/Http/Middleware/ForceHttps.php
```

---

### 7.3 Asset Build Fails

**Error:**
```
ERROR in Module not found
```

**Solution:**
```bash
# Delete and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## 8. Storage & Permission Errors

### 8.1 Permission Denied

**Error:**
```
Permission denied: storage/logs/laravel.log
```

**Solution (Linux/macOS):**
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

**Solution (Windows):**
1. Right-click storage folder
2. Properties → Security
3. Give full control to your user account

---

### 8.2 Storage Link Not Working

**Error:**
```
The "public/storage" directory does not exist
```

**Solution:**
```bash
# Create storage link
php artisan storage:link

# If error, remove existing and recreate
rm public/storage
php artisan storage:link
```

---

### 8.3 File Upload Fails

**Error:**
```
The file could not be uploaded
```

**Solution:**
```bash
# Check storage permissions
chmod -R 775 storage/app/public

# Check PHP upload limits in php.ini
upload_max_filesize = 50M
post_max_size = 52M
```

---

## 9. Runtime Errors

### 9.1 500 Internal Server Error

**Error:**
```
500 Internal Server Error
```

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Check .env
APP_DEBUG=true  # Enable to see error
```

---

### 9.2 404 Not Found

**Error:**
```
404 Not Found
```

**Solution:**
```bash
# Check URL
# Clear route cache
php artisan route:clear
php artisan route:cache

# Check .htaccess (Apache)
# Check nginx config (Nginx)
```

---

### 9.3 419 Page Expired

**Error:**
```
419 Page Expired
```

**Solution:**
```bash
# Clear session cache
php artisan session:flush

# Check SESSION_DRIVER in .env
SESSION_DRIVER=database

# Ensure sessions table exists
php artisan session:table
php artisan migrate
```

---

### 9.4 CSRF Token Mismatch

**Error:**
```
CSRF token mismatch
```

**Solution:**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check .env
APP_KEY=  # Should have a key

# Generate new key
php artisan key:generate
```

---

## 10. Email Errors

### 10.1 Email Not Sending

**Error:**
```
Swift_TransportException: Connection could not be established
```

**Solution:**
```env
# For development, use log driver
MAIL_MAILER=log

# Check logs
tail -f storage/logs/laravel.log

# For SMTP, verify credentials
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

---

### 10.2 Email Queue Not Processing

**Error:**
```
Emails queued but not sent
```

**Solution:**
```bash
# Start queue listener (development)
php artisan queue:listen --tries=1

# Start queue worker (production)
php artisan queue:work --sleep=3 --tries=3

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

**Note:** Use `queue:listen` for development and `queue:work` for production (Supervisor).

---

## 11. Queue Errors

### 11.1 Queue Not Processing

**Error:**
```
Jobs are not being processed
```

**Solution:**
```bash
# Start queue listener (development)
php artisan queue:listen --tries=1

# Start queue worker (production)
php artisan queue:work --sleep=3 --tries=3

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# For production, use Supervisor
```

**Note:** Use `queue:listen` for development and `queue:work` for production (Supervisor).

---

### 11.2 Queue Worker Stopped

**Error:**
```
Queue worker stopped unexpectedly
```

**Solution:**
```bash
# Restart worker
php artisan queue:restart

# Start listener (development)
php artisan queue:listen --tries=1

# Start worker (production)
php artisan queue:work --sleep=3 --tries=3

# Check logs
tail -f storage/logs/laravel.log
```

---

## 12. Authentication Errors

### 12.1 Login Failed

**Error:**
```
These credentials do not match our records
```

**Solution:**
```bash
# Check credentials
# Reset password
php artisan tinker
>>> User::where('email', 'admin@example.com')->first()->update(['password' => bcrypt('newpassword')])
```

---

### 12.2 Email Not Verified

**Error:**
```
Please verify your email address
```

**Solution:**
```bash
# Resend verification code
# Check email spam folder
# Check mail logs
tail -f storage/logs/laravel.log
```

---

### 12.3 Account Suspended

**Error:**
```
Your account has been suspended
```

**Solution:**
Contact administrator to unsuspend account:
```bash
php artisan tinker
>>> User::where('email', 'user@example.com')->first()->update(['is_suspended' => false])
```

---

## Additional Help

### Log Files

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# PHP error log
/var/log/php/error.log  # Linux
C:\php\logs\php_error_log  # Windows

# Web server logs
/var/log/nginx/error.log  # Nginx
/var/log/apache2/error.log  # Apache
```

### Debug Mode

```env
# Enable debug mode (development only!)
APP_DEBUG=true
LOG_LEVEL=debug
```

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Check application
php artisan about

# Check routes
php artisan route:list

# Check config
php artisan config:cache
php artisan config:clear

# Test email
php artisan send-test-email your-email@example.com
```

---

<div align="center">

**Nexus - Complete Troubleshooting Guide**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
