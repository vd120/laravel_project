# Setup Troubleshooting Guide

Complete troubleshooting guide for Nexus setup issues.

---

## Table of Contents

- [Common Setup Issues](#common-setup-issues)
- [Database Issues](#database-issues)
- [PHP Issues](#php-issues)
- [Node.js/NPM Issues](#nodejsnpm-issues)
- [Permission Issues](#permission-issues)
- [Migration Issues](#migration-issues)
- [Build Issues](#build-issues)

---

## Common Setup Issues

### Script Won't Run

**Problem:** Setup script fails immediately or shows permission errors.

**Solutions:**

```bash
# Linux/macOS - Make script executable
chmod +x setup.sh
./setup.sh

# Windows PowerShell - Bypass execution policy
powershell -ExecutionPolicy Bypass -File setup.ps1

# Windows CMD - Run as Administrator
# Right-click Command Prompt → Run as Administrator
```

---

### Composer Install Fails

**Problem:** `composer install` fails with errors.

**Solutions:**

```bash
# Clear composer cache
composer clear-cache

# Try with verbose output
composer install -vvv

# If memory limit error
php -d memory_limit=512M /usr/local/bin/composer install

# If SSL certificate error
composer config -g disable-tls true
composer install
```

---

### NPM Install Fails

**Problem:** `npm install` fails or hangs.

**Solutions:**

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# If behind proxy
npm config set proxy http://proxy-server:port
npm config set https-proxy http://proxy-server:port

# Use legacy peer deps (for dependency conflicts)
npm install --legacy-peer-deps
```

---

## Database Issues

### SQLite Database Locked

**Problem:** `SQLiteDatabase is locked` or `database is locked`

**Solutions:**

```bash
# Check file permissions
ls -la database/database.sqlite
chmod 666 database/database.sqlite

# Close any other processes using the database
# Kill PHP processes if any
pkill -f "php artisan"

# Recreate database
rm database/database.sqlite
touch database/database.sqlite
chmod 666 database/database.sqlite
php artisan migrate --force
```

---

### MySQL Connection Failed

**Problem:** `SQLSTATE[HY000] [2002] Connection refused`

**Solutions:**

1. **Check if MySQL is running:**
```bash
# Linux
sudo systemctl status mysql
sudo systemctl start mysql

# Windows
# Check Services → MySQL

# macOS
brew services list
brew services start mysql
```

2. **Verify credentials in .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=your_password
```

3. **Test connection manually:**
```bash
mysql -h 127.0.0.1 -P 3306 -u nexus_user -p
```

4. **Check MySQL user privileges:**
```sql
mysql -u root -p
SELECT User, Host FROM mysql.user;
SHOW GRANTS FOR 'nexus_user'@'localhost';
```

---

### MySQL Database Doesn't Exist

**Problem:** `Unknown database 'nexus'`

**Solutions:**

```bash
# Create database manually
mysql -u root -p -e "CREATE DATABASE nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Or use the setup script
mysql -u root -p < database/setup_database.sql

# Grant privileges
mysql -u root -p -e "GRANT ALL PRIVILEGES ON nexus.* TO 'nexus_user'@'localhost'; FLUSH PRIVILEGES;"
```

---

### Access Denied for User

**Problem:** `Access denied for user 'username'@'localhost'`

**Solutions:**

```sql
-- Reset user password
mysql -u root -p
ALTER USER 'nexus_user'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;

-- Or recreate user
DROP USER IF EXISTS 'nexus_user'@'localhost';
CREATE USER 'nexus_user'@'localhost' IDENTIFIED BY 'new_password';
GRANT ALL PRIVILEGES ON nexus.* TO 'nexus_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## PHP Issues

### PHP Extension Missing

**Problem:** `could not find driver` or extension errors

**Solutions:**

```bash
# Linux - Install missing extensions
sudo apt install php-mbstring php-xml php-curl php-zip php-mysql php-bcmath php-sqlite3

# Check installed extensions
php -m

# Enable extension in php.ini
# Find your php.ini location
php --ini

# Edit php.ini and uncomment/add:
extension=mbstring
extension=curl
extension=zip
extension=pdo_mysql
```

---

### PHP Version Too Old

**Problem:** `PHP 8.2 or higher is required`

**Solutions:**

```bash
# Check current version
php -v

# Linux - Install PHP 8.2/8.3
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-mysql

# Set default PHP version
sudo update-alternatives --set php /usr/bin/php8.2

# Verify
php -v
```

---

### Memory Limit Exceeded

**Problem:** `Fatal error: Allowed memory size exhausted`

**Solutions:**

```bash
# Increase memory limit temporarily
php -d memory_limit=512M artisan migrate

# Or edit php.ini
# Find memory_limit and increase:
memory_limit = 512M

# Restart web server after changes
sudo systemctl restart apache2
# or
sudo systemctl restart php-fpm
```

---

## Permission Issues

### Storage Directory Permissions

**Problem:** `Permission denied` when writing to storage

**Solutions:**

```bash
# Linux/macOS - Set correct permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Set ownership (replace www-data with your web server user)
chown -R www-data:www-data storage/ bootstrap/cache/

# Or use current user
chown -R $USER:$USER storage/ bootstrap/cache/

# Windows - Run as Administrator or:
# Right-click folder → Properties → Security
# Add full control for your user
```

---

### Storage Link Issues

**Problem:** `storage link already exists` or broken link

**Solutions:**

```bash
# Remove existing link
rm public/storage

# Recreate link
php artisan storage:link

# If still fails, check permissions
ls -la public/
chmod 755 public/
```

---

## Migration Issues

### Migration Fails

**Problem:** Migrations fail with errors

**Solutions:**

```bash
# Check migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Fresh migration (WARNING: deletes all data)
php artisan migrate:fresh

# Fresh with seed
php artisan migrate:fresh --seed

# Check for syntax errors in migrations
php artisan migrate --pretend
```

---

### Duplicate Entry Error

**Problem:** `Duplicate entry for key 'PRIMARY'`

**Solutions:**

```bash
# Clear database and start fresh
php artisan migrate:fresh

# Or manually truncate tables
php artisan tinker
>>> DB::table('migrations')->delete();
>>> exit

php artisan migrate
```

---

### Foreign Key Constraint Fails

**Problem:** `Cannot add foreign key constraint`

**Solutions:**

1. **Check table engine:**
```sql
-- Tables must use InnoDB for foreign keys
SHOW TABLE STATUS WHERE Name = 'your_table';
```

2. **Check column types match:**
```sql
-- Both columns must have same type
DESCRIBE parent_table;
DESCRIBE child_table;
```

3. **Disable foreign key checks temporarily:**
```sql
SET FOREIGN_KEY_CHECKS=0;
-- Run migration
SET FOREIGN_KEY_CHECKS=1;
```

---

## Build Issues

### Vite Build Fails

**Problem:** `npm run build` fails

**Solutions:**

```bash
# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm cache clean --force
npm install

# Check Node.js version (need 18+)
node -v

# Update Node.js if needed
# https://nodejs.org/

# Try building with verbose output
npm run build -- --debug

# Check for syntax errors in JS files
npx eslint resources/js/
```

---

### Assets Not Loading

**Problem:** 404 errors on CSS/JS files after build

**Solutions:**

```bash
# Rebuild assets
npm run build

# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check manifest file exists
ls public/build/manifest.json

# Regenerate manifest
rm -rf public/build
npm run build
```

---

### Tailwind CSS Not Working

**Problem:** Styles not applying

**Solutions:**

```bash
# Check tailwind.config.js content paths
# Should include your blade files

# Rebuild CSS
npm run build

# Clear browser cache
# Ctrl+Shift+R (hard refresh)

# Check CSS is being loaded
# View page source and verify CSS link
```

---

## Admin User Issues

### Admin User Already Exists

**Problem:** `Duplicate entry for email`

**Solutions:**

```bash
# User already exists, use existing credentials
# Email: admin@example.com
# Password: admin123

# Or reset password
php artisan tinker
>>> $user = \App\Models\User::where('email', 'admin@example.com')->first();
>>> $user->password = bcrypt('newpassword123');
>>> $user->save();
>>> exit
```

---

### Cannot Login

**Problem:** Login fails with valid credentials

**Solutions:**

```bash
# Clear sessions
php artisan session:flush

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check user exists
php artisan tinker
>>> \App\Models\User::where('email', 'admin@example.com')->first();

# Verify password hash
>>> \App\Models\User::where('email', 'admin@example.com')->first()->password
```

---

## Email Verification Issues

### Verification Email Not Sending

**Problem:** No verification email received

**Solutions:**

1. **Check mail configuration:**
```env
MAIL_MAILER=log  # Logs to storage/logs/laravel.log
# OR
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

2. **For Gmail, use App Password:**
   - Go to Google Account → Security
   - Enable 2FA
   - Generate App Password
   - Use this password in .env

3. **Check logs:**
```bash
# If using log mailer
tail -f storage/logs/laravel.log
```

---

## Performance Issues

### Slow Page Loads

**Problem:** Application is slow

**Solutions:**

```bash
# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear caches if issues persist
php artisan optimize:clear

# Check query performance
# Enable query log in .env
LOG_LEVEL=debug

# Check storage/logs/laravel.log for slow queries
```

---

## Getting Help

### Collect Debug Information

```bash
# System information
php -v
node -v
npm -v
mysql --version

# Laravel version
php artisan --version

# Environment check
php artisan about

# Database status
php artisan migrate:status

# Route list
php artisan route:list

# Config check
php artisan config:clear
```

### Log Files to Check

```
storage/logs/laravel.log      # Application logs
storage/logs/                 # All log files
bootstrap/cache/              # Compiled files
```

### Useful Commands

```bash
# Clear everything
php artisan optimize:clear
rm -rf bootstrap/cache/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Regenerate autoload
composer dump-autoload --optimize

# Rebuild frontend
npm run build

# Start fresh
php artisan migrate:fresh --seed
```

---

## Contact Support

If issues persist:

1. Check [Laravel Documentation](https://laravel.com/docs)
2. Review [PHP Documentation](https://php.net)
3. Check project's GitHub issues
4. Review `storage/logs/laravel.log` for specific errors
