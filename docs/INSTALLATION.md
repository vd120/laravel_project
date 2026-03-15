# Installation Guide

Complete installation instructions for Nexus on all platforms.

---

## Table of Contents

- [System Requirements](#system-requirements)
- [Quick Installation](#quick-installation)
- [Manual Installation](#manual-installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Frontend Build](#frontend-build)
- [Running the Application](#running-the-application)
- [Troubleshooting](#troubleshooting)

---

## System Requirements

### Required Software

| Software | Minimum Version | Recommended Version | Purpose |
|----------|-----------------|---------------------|---------|
| **PHP** | 8.2.0 | 8.2+ | Server-side runtime |
| **Composer** | 2.0.0 | 2.6+ | PHP dependency manager |
| **Node.js** | 18.0.0 | 20.x LTS | JavaScript runtime |
| **npm** | 9.0.0 | 10.x | Node package manager |
| **Git** | 2.0.0 | Latest | Version control |

### Optional Software

| Software | Purpose |
|----------|---------|
| **FFmpeg** | Video processing (thumbnails, trimming) |
| **MySQL 8.0+** | Production database (SQLite used by default) |
| **Redis** | Session/cache storage |

### PHP Extensions

Ensure the following PHP extensions are enabled:

```
php-bcmath
php-ctype
php-curl
php-fileinfo
php-gd
php-json
php-mbstring
php-mysql (if using MySQL)
php-openssl
php-pdo
php-tokenizer
php-xml
```

---

## Quick Installation

### Automated Setup (Recommended)

The project includes automated setup scripts for all platforms.

#### Linux/macOS

```bash
# Clone repository
git clone <repository-url>
cd laravel_project

# Make script executable
chmod +x setup.sh

# Run setup
./setup.sh
```

#### Windows PowerShell

```powershell
# Clone repository
git clone <repository-url>
cd laravel_project

# Run setup
.\setup.ps1
```

#### Windows Command Prompt

```cmd
REM Clone repository
git clone <repository-url>
cd laravel_project

REM Run setup
setup.bat
```

### What the Script Does

```
┌─────────────────────────────────────────────────────────┐
│                   Setup Script Flow                      │
├─────────────────────────────────────────────────────────┤
│  1. Check system requirements (PHP, Composer, Node.js)  │
│  2. Install PHP dependencies (composer install)         │
│  3. Install JavaScript dependencies (npm install)       │
│  4. Create .env from .env.example                       │
│  5. Generate application key (php artisan key:generate) │
│  6. Create SQLite database                              │
│  7. Run database migrations (php artisan migrate)       │
│  8. Create admin user (admin@example.com)               │
│  9. Build frontend assets (npm run build)               │
│  10. Create storage symbolic link                       │
│  11. Clear all caches                                   │
└─────────────────────────────────────────────────────────┘
```

---

## Manual Installation

For advanced users or custom configurations.

### Step 1: Clone Repository

```bash
git clone <repository-url>
cd laravel_project
```

### Step 2: Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

For development:
```bash
composer install
```

### Step 3: Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Database

#### SQLite (Default)

```bash
# Create database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

#### MySQL

1. Create database:
```sql
CREATE DATABASE nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. Run migrations:
```bash
php artisan migrate
```

### Step 5: Create Admin User

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('admin123'),
    'email_verified_at' => now(),
    'is_admin' => true,
]);
```

### Step 6: Install JavaScript Dependencies

```bash
npm install
```

### Step 7: Build Frontend Assets

```bash
# Production build
npm run build

# Development with hot-reload
npm run dev
```

### Step 8: Create Storage Link

```bash
php artisan storage:link
```

### Step 9: Set Permissions (Linux/macOS)

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # If using Apache/Nginx
```

---

## Environment Configuration

### Basic Configuration

Edit `.env` file with your settings:

```env
# Application
APP_NAME=Nexus
APP_ENV=local
APP_KEY=base64:...  # Auto-generated
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database (SQLite)
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite  # Comment out for SQLite

# Database (MySQL - Production)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nexus
# DB_USERNAME=root
# DB_PASSWORD=secret

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Google OAuth (Optional)
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### Production Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Use MySQL in production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=nexus_user
DB_PASSWORD=strong_password

# Use Redis for cache/sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Enable HTTPS
# FORCE_HTTPS=true
```

---

## Database Setup

### Migration Commands

```bash
# Run all migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh with seeders
php artisan migrate:fresh --seed
```

### Database Seeding

Create initial data:

```bash
# Run seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

---

## Frontend Build

### Development Mode

```bash
# Start Vite dev server with hot-reload
npm run dev
```

### Production Build

```bash
# Optimized production build
npm run build
```

### Build Troubleshooting

#### Clear Node Modules

```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run build
```

#### Clear Laravel Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

---

## Running the Application

### Development Server

```bash
# Start Laravel development server
php artisan serve

# Access at http://localhost:8000
```

### With Queue Worker

```bash
# In separate terminal
php artisan queue:work
```

### With Vite Dev Server

```bash
# In separate terminal
npm run dev
```

### Full Development Stack

```bash
# Run all services (server, queue, logs, vite)
composer run dev
```

---

## Troubleshooting

### Permission Issues (Linux/macOS)

**Problem:** Cannot write to storage or cache directories.

**Solution:**
```bash
chmod -R 755 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Database Connection Failed

**Problem:** SQLite database file not found or MySQL connection refused.

**Solution (SQLite):**
```bash
# Ensure database file exists
touch database/database.sqlite
chmod 666 database/database.sqlite

# Re-run migrations
php artisan migrate:fresh
```

**Solution (MySQL):**
```bash
# Verify credentials in .env
# Test connection
php artisan tinker
DB::connection()->getPdo();
```

### Class Not Found Errors

**Problem:** Autoloader issues after composer install.

**Solution:**
```bash
composer dump-autoload --optimize
```

### Frontend Assets Not Loading

**Problem:** 404 errors on CSS/JS files.

**Solution:**
```bash
# Rebuild assets
npm install
npm run build

# Clear cache
php artisan view:clear
php artisan config:clear
```

### Email Verification Not Working

**Problem:** Verification emails not sending.

**Solution:**
1. Check mail configuration in `.env`
2. For Gmail, use App Password (not regular password)
3. Test with mailtrap.io for development

### Google OAuth Not Working

**Problem:** OAuth callback returns error.

**Solution:**
1. Verify redirect URI in Google Console matches `.env`
2. Ensure credentials are correct in `.env`
3. Check that callback route is registered

### Memory Limit Exceeded

**Problem:** PHP memory limit during installation.

**Solution:**
```bash
# Increase memory limit temporarily
php -d memory_limit=512M artisan migrate

# Or update php.ini
memory_limit = 512M
```

### Node.js Version Mismatch

**Problem:** npm install fails with version errors.

**Solution:**
```bash
# Use Node Version Manager (nvm)
nvm install 20
nvm use 20

# Or update Node.js to LTS version
```

---

## Verification Checklist

After installation, verify:

- [ ] Application loads at `http://localhost:8000`
- [ ] Can login with `admin@example.com` / `admin123`
- [ ] Database migrations ran successfully
- [ ] Storage link created (`public/storage` exists)
- [ ] Frontend assets load (no 404 errors)
- [ ] Can create a new user account
- [ ] Email verification works (if mail configured)
- [ ] Can create posts with images
- [ ] Real-time chat works (if Reverb/Pusher configured)

---

## Next Steps

After successful installation:

1. **Change Default Password** - Login and update admin password
2. **Configure Email** - Set up SMTP for verification emails
3. **Set Up OAuth** - Configure Google OAuth (optional)
4. **Review Settings** - Check application configuration
5. **Read Documentation** - Review [Features](FEATURES.md) and [Architecture](ARCHITECTURE.md)

---

## Support

For additional help:

1. Check [Laravel Documentation](https://laravel.com/docs)
2. Review [Troubleshooting](../README.md#troubleshooting) in README
3. Examine `storage/logs/laravel.log` for errors
4. Enable debug mode: `APP_DEBUG=true` in `.env`
