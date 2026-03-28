# Nexus - Complete Setup & Troubleshooting Guide

**Last Updated:** March 27, 2026  
**Platform Support:** Windows (CMD/PowerShell), Linux, macOS

---

## Table of Contents

1. [Setup Files Overview](#setup-files-overview)
2. [System Requirements](#system-requirements)
3. [Quick Start](#quick-start)
4. [Detailed Setup Process](#detailed-setup-process)
5. [Database Configuration](#database-configuration)
6. [Troubleshooting](#troubleshooting)
7. [Common Errors & Solutions](#common-errors--solutions)
8. [Post-Setup Verification](#post-setup-verification)
9. [Useful Commands](#useful-commands)

---

## 1. Setup Files Overview

Nexus provides **3 setup scripts** for different platforms:

- **setup.sh** (Linux/macOS): Full MySQL/SQLite support
- **setup.bat** (Windows CMD): Full MySQL/SQLite support
- **setup.ps1** (Windows PowerShell): Full MySQL/SQLite support

### What Setup Scripts Do

All three scripts perform the same operations:

1.  Check system requirements (PHP, Node.js, Composer, etc.)
2.  Verify PHP extensions
3.  Install PHP dependencies (Composer)
4.  Install JavaScript dependencies (npm)
5.  Create .env from .env.example
6.  Generate application key
7.  Configure database (SQLite or MySQL)
8.  Run database migrations
9.  Create admin user
10.  Build frontend assets
11.  Setup storage and permissions
12.  Clear all caches
13.  Optional production optimization

---

## 2. System Requirements

### Required Software

- **PHP** (8.2 minimum, 8.3+ recommended): Server-side runtime
- **Composer** (2.x minimum, Latest recommended): PHP dependency manager
- **Node.js** (18.x LTS minimum, 20.x LTS recommended): JavaScript runtime
- **npm** (9.x minimum, Latest recommended): JavaScript package manager
- **Git** (Any, Latest recommended): Version control
- **Database** (SQLite 3.x minimum, MySQL 8.0+ recommended): Database storage

### Required PHP Extensions

The setup script checks for these extensions:

```
 mbstring     - Multibyte string handling
 xml          - XML parsing
 curl         - HTTP requests
 zip          - ZIP archive handling
 openssl      - Encryption/SSL
 pdo          - Database abstraction
 json         - JSON handling
 tokenizer    - Code tokenization
 bcmath       - Arbitrary precision math
 mysql        - MySQL database driver
```

### Optional Software

- **MySQL Client**: Automatic database creation
- **FFmpeg**: Video processing (thumbnails, trimming)
- **Redis**: Cache/sessions (production)

---

## 3. Quick Start

### Linux/macOS

```bash
# Make script executable
chmod +x setup.sh

# Run setup
./setup.sh
```

### Windows CMD

```cmd
# Double-click setup.bat
# OR run from command prompt
setup.bat
```

### Windows PowerShell

```powershell
# Allow script execution (first time only)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Run setup
./setup.ps1
```

### Alternative: Composer Setup

```bash
# Use Composer's built-in setup script
composer run setup
```

This runs a simplified setup without database configuration options.

---

## 4. Detailed Setup Process

### Step 1: System Requirements Check

The script automatically checks:

```bash
# PHP version check
PHP 8.2+ required
✓ PHP 8.3.5 installed

# PHP extensions check
✓ PHP extension: mbstring
✓ PHP extension: xml
✓ PHP extension: curl
... (10 extensions total)

# Tool checks
✓ Composer installed
✓ Node.js v20.11.0 installed
✓ npm 10.2.4 installed
✓ Git installed
```

### Step 2: Install Dependencies

```bash
# PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# JavaScript dependencies
npm install
```

### Step 3: Environment Setup

```bash
# Creates .env from .env.example
cp .env.example .env

# Generates application key
php artisan key:generate
```

### Step 4: Database Configuration

**Choose database type:**

```
Select database type:
1) SQLite (recommended for development/testing)
2) MySQL/MariaDB (recommended for production)

Enter choice [1-2]:
```

#### Option 1: SQLite (Easiest)

-  No configuration needed
-  Single file database
-  Perfect for development
-  Not recommended for production

**What happens:**
```bash
✓ Creates database/database.sqlite
✓ Updates .env for SQLite
✓ Sets proper permissions
```

#### Option 2: MySQL (Production)

**For new database:**
```
Database Setup:
1) Create new database
2) Use existing database

Enter choice [1-2]: 1

New database name: nexus
Database username (for creating DB): root
Database password: ******
Database user to create: nexus
Password for new database user: ******
```

**For existing database:**
```
Enter choice [1-2]: 2

Existing database name: nexus
Database username: nexus_user
Database password: ******
```

**What happens:**
```bash
✓ Tests MySQL connection
✓ Creates database (if new)
✓ Creates user and grants privileges
✓ Updates .env with credentials
```

### Step 5: Run Migrations

```bash
# Clear config cache
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force
```

**What happens:**
- Creates all database tables (24+ tables)
- Runs 79 migration files
- Sets up schema

### Step 6: Create Admin User

```bash
# Creates default admin account
Email: admin@example.com
Password: admin123
Username: admin
```

**Security Note:** Change the default password after first login!

### Step 7: Build Frontend

```bash
# Build assets with Vite
npm run build
```

**What happens:**
- Compiles Vue.js components
- Bundles JavaScript modules
- Compiles Tailwind CSS
- Obfuscates production code

### Step 8: Storage Setup

```bash
# Create storage symlink
php artisan storage:link

# Set permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Step 9: Final Optimization

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear

# Optional: Production optimization
php artisan optimize
```

---

## 5. Database Configuration

### SQLite Configuration

**.env settings:**
```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=database/database.sqlite
# DB_USERNAME=root
# DB_PASSWORD=
```

**File location:**
```
nexus/
└── database/
    └── database.sqlite  (created automatically)
```

### MySQL Configuration

**.env settings:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=your_secure_password
```

**Database creation SQL:**
```sql
CREATE DATABASE IF NOT EXISTS `nexus` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'nexus_user'@'%' 
IDENTIFIED BY 'your_secure_password';

GRANT ALL PRIVILEGES ON `nexus`.* TO 'nexus_user'@'%';
FLUSH PRIVILEGES;
```

---

## 6. Troubleshooting

### General Troubleshooting Steps

1. **Check logs**
   ```bash
   # Laravel logs
   tail -f storage/logs/laravel.log
   
   # Composer logs
   composer install -vvv
   
   # npm logs
   npm install --verbose
   ```

2. **Clear all caches**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   php artisan event:clear
   ```

3. **Verify requirements**
   ```bash
   php -v
   composer --version
   node -v
   npm -v
   ```

---

## 7. Common Errors & Solutions

### PHP Errors

#### Error: PHP version too low

```
✗ PHP 8.2 or higher is required! You have 8.1.2
```

**Solution:**
```bash
# Ubuntu/Debian
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql php8.3-bcmath

# Windows
# Download from https://windows.php.net/download/
# Ensure PHP 8.2+ and add to PATH
```

#### Error: Missing PHP extensions

```
✗ PHP extension: mbstring (MISSING)
✗ PHP extension: curl (MISSING)
```

**Solution:**
```bash
# Ubuntu/Debian
sudo apt install php-mbstring php-curl php-xml php-zip php-mysql php-bcmath
sudo systemctl restart apache2  # or php8.3-fpm

# Windows (php.ini)
; Uncomment these lines:
extension=mbstring
extension=curl
extension=xml
extension=zip
extension=pdo_mysql
```

#### Error: Composer not found

```
✗ Composer is not installed!
```

**Solution:**
```bash
# Linux/macOS
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows
# Download from https://getcomposer.org/download/
```

### Node.js Errors

#### Error: Node.js not found

```
✗ Node.js is not installed!
```

**Solution:**
```bash
# Linux
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Windows
# Download from https://nodejs.org/
```

#### Error: npm install fails

```
npm ERR! code EACCES
npm ERR! permission denied
```

**Solution:**
```bash
# Fix npm permissions
mkdir ~/.npm-global
npm config set prefix '~/.npm-global'
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.bashrc
source ~/.bashrc

# OR use sudo (not recommended)
sudo npm install
```

#### Error: npm build fails

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

### Database Errors

#### Error: SQLite database locked

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

#### Error: MySQL connection refused

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

#### Error: Database does not exist

```
SQLSTATE[HY000] [1049] Unknown database 'nexus'
```

**Solution:**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# OR use setup script to create automatically
./setup.sh  # Choose option 1 (Create new database)
```

#### Error: Access denied for user

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

### Migration Errors

#### Error: Migration table already exists

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

#### Error: Foreign key constraint fails

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

### Storage Errors

#### Error: Permission denied

```
Permission denied: storage/logs/laravel.log
```

**Solution:**
```bash
# Linux/macOS
chmod -R 775 storage/
chown -R www-data:www-data storage/

# Windows
# Right-click storage folder → Properties → Security
# Give full control to your user account
```

#### Error: Storage link not working

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

### Frontend Errors

#### Error: Vite manifest not found

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

#### Error: Mixed content (HTTPS)

```
Mixed Content: The page was loaded over HTTPS, but requested an insecure resource
```

**Solution:**
```env
# .env
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIES=true

# Force HTTPS
# Add to app/Http/Middleware/ForceHttps.php
```

### Authentication Errors

#### Error: 419 Page Expired

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

#### Error: CSRF token mismatch

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

### Email Errors

#### Error: Email not sending

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

### Queue Errors

#### Error: Queue not processing

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
# See docs/INSTALLATION.md
```

**Note:** Use `queue:listen` for development and `queue:work` for production (Supervisor).

---

## 8. Post-Setup Verification

### Verify Installation

```bash
# Check application
php artisan about

# Check database
php artisan migrate:status

# Check routes
php artisan route:list --columns=method,uri,name

# Check config
php artisan config:cache
php artisan config:clear
```

### Test Login

1. Start server: `php artisan serve`
2. Visit: `http://localhost:8000/login`
3. Login with:
   - Email: `admin@example.com`
   - Password: `admin123`
4. Change password after login!

### Test Features

```bash
# Create test post
# Visit /posts and create a post

# Test chat
# Visit /chat and send a message

# Test stories
# Visit /stories/create and upload a story
```

---

## 9. Useful Commands

### Development

```bash
# Start development server
php artisan serve

# Start all services (server, queue, logs, vite)
composer run dev

# Start Vite only
npm run dev

# Start queue listener (development)
php artisan queue:listen --tries=1

# Monitor logs
php artisan pail
```

### Database

```bash
# Run migrations
php artisan migrate

# Fresh migrate (WARNING: deletes all data)
php artisan migrate:fresh

# Fresh migrate with seed
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Seed database
php artisan db:seed
```

### Cache

```bash
# Clear all caches
php artisan optimize:clear

# Clear config cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:clear

# Optimize for production
php artisan optimize
```

### Testing

```bash
# Run tests
composer run test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test tests/Feature/PostTest.php
```

### Maintenance

```bash
# Enable maintenance mode
php artisan down

# Disable maintenance mode
php artisan up

# Clear compiled files
php artisan clear-compiled

# Generate IDE helper files
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
```

---

## Getting Help

### Log Files

```bash
# Laravel logs
storage/logs/laravel.log

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

### Community Resources

- **Laravel Documentation:** https://laravel.com/docs
- **Laravel Community:** https://laracasts.com/discuss
- **Stack Overflow:** https://stackoverflow.com/questions/tagged/laravel
- **GitHub Issues:** Report bugs on project repository

---

<div align="center">

**Nexus - Complete Setup & Troubleshooting Guide**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
