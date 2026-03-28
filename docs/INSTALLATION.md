# Nexus - Installation Guide

Complete installation and setup guide for Nexus social networking platform.

---

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Quick Start](#quick-start)
3. [Manual Installation](#manual-installation)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [Google OAuth Setup](#google-oauth-setup)
7. [Email Configuration](#email-configuration)
8. [File Storage Setup](#file-storage-setup)
9. [Development Server](#development-server)
10. [Production Deployment](#production-deployment)
11. [Troubleshooting](#troubleshooting)

---

## System Requirements

### Required Software

- **PHP** (8.2+): Server-side runtime
- **Composer** (2.x): PHP dependency manager
- **Node.js** (18+ LTS): JavaScript runtime
- **npm** (9+): JavaScript package manager
- **Git** (Latest): Version control
- **SQLite** (Built-in): Default database

### PHP Extensions

Required extensions:
```
bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, 
mysqli, openssl, pdo, pdo_mysql, pdo_sqlite, phar, 
tokenizer, xml, zip
```

Optional (recommended):
```
ext-ffmpeg (for video processing)
redis (for caching)
```

### Server Requirements (Production)

- **CPU**: 2 cores minimum, 4+ cores recommended
- **RAM**: 2GB minimum, 4GB+ recommended
- **Storage**: 10GB minimum, 50GB+ SSD recommended
- **PHP**: 8.2 minimum, 8.3+ recommended
- **Database**: SQLite for development, MySQL 8.0+ for production

---

## Quick Start

### Using Setup Script (Recommended)

**Windows (Git Bash):**
```bash
./setup.sh
```

**Windows (PowerShell):**
```powershell
./setup.ps1
```

**Windows (CMD):**
```cmd
setup.bat
```

**Linux/Mac:**
```bash
bash setup.sh
```

The setup script will:
1. Install PHP dependencies
2. Copy `.env.example` to `.env`
3. Generate application key
4. Run database migrations
5. Install Node.js dependencies
6. Build assets

---

## Manual Installation

### Step 1: Clone Repository

```bash
git clone https://github.com/your-org/nexus.git
cd nexus
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Copy Environment File

```bash
cp .env.example .env
```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

### Step 5: Create Database

**SQLite (Development):**
```bash
touch database/database.sqlite
```

**MySQL (Production):**
```sql
CREATE DATABASE nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nexus_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON nexus.* TO 'nexus_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 6: Configure Database

Update `.env` file:

**SQLite:**
```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

**MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=secure_password
```

### Step 7: Run Migrations

```bash
php artisan migrate
```

### Step 8: Install Node.js Dependencies

```bash
npm install
```

### Step 9: Build Assets

```bash
npm run build
```

### Step 10: Create Storage Symlink

```bash
php artisan storage:link
```

---

## Environment Configuration

### Basic Configuration

```env
# Application
APP_NAME="Nexus"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Locale
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_TIMEZONE=UTC

# Logging
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Database
DB_CONNECTION=sqlite

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Cache
CACHE_STORE=database

# Mail
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Production Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

SESSION_SECURE_COOKIES=true
SESSION_DOMAIN=your-domain.com

# Redis (optional)
CACHE_STORE=redis
SESSION_DRIVER=redis

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=secure_password
```

---

## Database Setup

### SQLite Setup (Development)

1. Create database file:
```bash
touch database/database.sqlite
```

2. Update `.env`:
```env
DB_CONNECTION=sqlite
```

3. Run migrations:
```bash
php artisan migrate
```

### MySQL Setup (Production)

1. Create database and user:
```sql
CREATE DATABASE nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nexus_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON nexus.* TO 'nexus_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=secure_password
```

3. Run migrations:
```bash
php artisan migrate
```

### Session & Cache Tables

For database session and cache drivers:

```bash
php artisan session:table
php artisan cache:table
php artisan migrate
```

---

## Google OAuth Setup

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Create Project"
3. Enter project name (e.g., "Nexus")
4. Click "Create"

### Step 2: Enable Google APIs

1. In your project, go to "APIs & Services" > "Library"
2. Search for "Google People API" or "Google+ API"
3. Click "Enable"

### Step 3: Create OAuth Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Select "Web application"
4. Add authorized JavaScript origins:
   - `http://localhost` (development)
   - `https://your-domain.com` (production)
5. Add authorized redirect URIs:
   - `http://localhost/auth/google/callback` (development)
   - `https://your-domain.com/auth/google/callback` (production)
6. Click "Create"

### Step 4: Configure Environment

Copy credentials to `.env`:

```env
GOOGLE_CLIENT_ID=your_client_id_here.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

### Step 5: Test OAuth

1. Visit `http://localhost/login`
2. Click "Login with Google"
3. You should be redirected to Google consent screen
4. After consent, redirected back to application

---

## Email Configuration

### Development (Mailtrap)

1. Create free account at [Mailtrap.io](https://mailtrap.io)
2. Create inbox
3. Copy SMTP credentials
4. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Production (SMTP)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Test Email Configuration

```bash
php artisan send-test-email your-email@example.com
```

---

## File Storage Setup

### Local Storage (Default)

Files are stored in `storage/app/public/`:

```bash
# Create symlink to public storage
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### AWS S3 (Production)

1. Create S3 bucket
2. Create IAM user with S3 permissions
3. Get access keys
4. Update `.env`:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false
```

---

## Development Server

### Start All Services

```bash
composer run dev
```

This starts:
- Laravel development server (port 8000)
- Queue listener
- Log monitor (Pail)
- Vite development server

### Start Individual Services

**Laravel Server:**
```bash
php artisan serve
```

**Vite Dev Server:**
```bash
npm run dev
```

**Queue Listener:**
```bash
php artisan queue:listen --tries=1
```

**Log Monitor:**
```bash
php artisan pail
```

### Access Application

Open browser and visit:
```
http://localhost:8000
```

---

## Production Deployment

### Pre-Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_URL=https://your-domain.com`
- [ ] Enable HTTPS/SSL
- [ ] Configure production database
- [ ] Set up Redis (optional)
- [ ] Configure queue worker
- [ ] Set up scheduled tasks
- [ ] Configure email (SMTP)
- [ ] Set up file storage (S3)
- [ ] Enable security headers
- [ ] Configure rate limiting
- [ ] Set up monitoring

### Deployment Steps

1. **Install dependencies:**
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

2. **Cache configuration:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Run migrations:**
```bash
php artisan migrate --force
```

4. **Create storage symlink:**
```bash
php artisan storage:link
```

5. **Set permissions:**
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### Queue Worker (Supervisor)

Create `/etc/supervisor/conf.d/nexus-worker.conf`:

```ini
[program:nexus-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/nexus/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/nexus/storage/logs/queue.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nexus-queue:*
```

**Note:** Use `queue:work` for production (Supervisor) and `queue:listen` for development.

### Cron Configuration

Add to crontab:
```bash
* * * * * cd /path/to/nexus && php artisan schedule:run >> /dev/null 2>&1
```

### Web Server Configuration

**Nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/nexus/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache:**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/nexus/public

    <Directory /path/to/nexus/public>
        Require all granted
        Options -Indexes +FollowSymLinks
        AllowOverride All
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nexus-error.log
    CustomLog ${APACHE_LOG_DIR}/nexus-access.log combined
</VirtualHost>
```

Enable `.htaccess` in `/path/to/nexus/public/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

---

## Troubleshooting

### Common Issues

#### 1. Permission Denied

**Error:** `Permission denied: storage/`

**Solution:**
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

#### 2. Database Connection Error

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solution:**
- Check database is running
- Verify `.env` database credentials
- For SQLite, ensure file exists: `touch database/database.sqlite`

#### 3. Class Not Found

**Error:** `Class 'App\Models\User' not found`

**Solution:**
```bash
composer dump-autoload
```

#### 4. Vite Manifest Not Found

**Error:** `Vite manifest not found`

**Solution:**
```bash
npm install
npm run build
```

#### 5. 404 on Routes

**Error:** All routes return 404

**Solution:**
- Check `.htaccess` exists in `public/`
- Enable mod_rewrite: `a2enmod rewrite`
- Restart Apache: `systemctl restart apache2`

#### 6. Session Error

**Error:** `SessionHandler::read(): open() failed`

**Solution:**
```bash
php artisan session:table
php artisan migrate
```

#### 7. Storage Link Error

**Error:** `Target [public/storage] already exists`

**Solution:**
```bash
rm public/storage
php artisan storage:link
```

#### 8. Email Not Sending

**Solution:**
- Check SMTP credentials in `.env`
- Use Mailtrap for testing
- Run: `php artisan send-test-email your-email@example.com`

#### 9. Google OAuth Error

**Error:** `invalid_client` or `redirect_uri_mismatch`

**Solution:**
- Verify `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`
- Check redirect URI matches exactly in Google Console
- Ensure `APP_URL` is correct

#### 10. Queue Not Processing

**Solution:**
- Start queue worker: `php artisan queue:work`
- Check `QUEUE_CONNECTION` in `.env`
- Use Supervisor for production

---

## Post-Installation

### Create Admin Account

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('your_secure_password'),
    'username' => 'admin',
    'email_verified_at' => now(),
    'is_admin' => true,
]);
```

### Seed Test Data (Optional)

```bash
php artisan db:seed
```

### Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Verify Installation

1. Visit `http://localhost:8000`
2. Register a test account
3. Verify email (check logs or Mailtrap)
4. Login
5. Create a test post
6. Test all features

---

## Next Steps

- [Features Documentation](FEATURES.md) - Learn about all features
- [Architecture Guide](ARCHITECTURE.md) - Understand system design
- [API Reference](API.md) - RESTful API documentation
- [Security Guide](SECURITY.md) - Security best practices

---

<div align="center">

**Nexus - Installation Guide**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
