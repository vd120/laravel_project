# Nexus - Troubleshooting Guide

Comprehensive troubleshooting guide for common issues and errors in Nexus social networking platform.

---

## Table of Contents

1. [Installation Issues](#installation-issues)
2. [Database Issues](#database-issues)
3. [Authentication Issues](#authentication-issues)
4. [File Upload Issues](#file-upload-issues)
5. [Email Issues](#email-issues)
6. [Frontend Issues](#frontend-issues)
7. [Chat & Messaging Issues](#chat--messaging-issues)
8. [Performance Issues](#performance-issues)
9. [Server Issues](#server-issues)
10. [Common Errors](#common-errors)

---

## Installation Issues

### 1. Composer Install Fails

**Error:** `composer install` fails with dependency errors

**Solutions:**

```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Install with platform requirements check
composer install --ignore-platform-reqs

# Or install without dev dependencies (production)
composer install --no-dev
```

### 2. Node.js Dependencies Fail

**Error:** `npm install` fails

**Solutions:**

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and package-lock.json
rm -rf node_modules package-lock.json

# Reinstall
npm install

# If using older Node.js, try legacy peer deps
npm install --legacy-peer-deps
```

### 3. Application Key Missing

**Error:** `No application encryption key has been specified`

**Solution:**

```bash
php artisan key:generate
```

### 4. Storage Symlink Error

**Error:** `Target [public/storage] already exists`

**Solution:**

```bash
# Remove existing symlink
rm public/storage

# Create new symlink
php artisan storage:link
```

### 5. Permission Denied

**Error:** `Permission denied: storage/` or `bootstrap/cache/`

**Solution (Linux/Mac):**

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

**Solution (Windows):**
- Right-click folder → Properties → Security
- Give full control to your user account

---

## Database Issues

### 1. Database Connection Error

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solutions:**

1. **Check database is running:**
```bash
# MySQL
sudo systemctl status mysql
sudo systemctl start mysql

# SQLite - ensure file exists
ls -la database/database.sqlite
```

2. **Verify `.env` credentials:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. **Clear config cache:**
```bash
php artisan config:clear
```

### 2. Migration Errors

**Error:** `Migration table already exists`

**Solution:**

```bash
# Rollback last migration
php artisan migrate:rollback

# Or reset all migrations
php artisan migrate:reset

# Re-run migrations
php artisan migrate
```

**Error:** `Duplicate column name` or `Table already exists`

**Solution:**

```bash
# Fresh migrate (WARNING: deletes all data)
php artisan migrate:fresh

# Or migrate with seed
php artisan migrate:fresh --seed
```

### 3. SQLite Database Locked

**Error:** `database is locked`

**Solutions:**

1. **Check file permissions:**
```bash
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite
```

2. **Close other connections:**
- Stop all running processes
- Clear cache: `php artisan cache:clear`

3. **Use MySQL for production:**
```env
DB_CONNECTION=mysql
```

### 4. Foreign Key Constraint Error

**Error:** `Cannot add foreign key constraint`

**Solutions:**

1. **Check table engine:**
```sql
-- Ensure tables use InnoDB
SHOW TABLE STATUS WHERE Name = 'your_table';
```

2. **Check column types match:**
- Foreign key columns must have same type
- Both must be UNSIGNED or neither

3. **Run migrations in correct order:**
```bash
php artisan migrate:refresh
```

---

## Authentication Issues

### 1. Login Not Working

**Error:** `These credentials do not match our records`

**Solutions:**

1. **Verify password:**
- Ensure Caps Lock is off
- Try password reset

2. **Check user exists:**
```bash
php artisan tinker
>>> App\Models\User::where('email', 'your@email.com')->first();
```

3. **Clear sessions:**
```bash
php artisan session:flush
```

### 2. Email Verification Not Sending

**Error:** Verification email not received

**Solutions:**

1. **Check mail configuration:**
```env
MAIL_MAILER=log  # Check logs in storage/logs/laravel.log
# OR
MAIL_MAILER=smtp  # Check SMTP credentials
```

2. **Test email:**
```bash
php artisan send-test-email your@email.com
```

3. **Check spam folder**

4. **Resend verification:**
- Click "Resend Verification Code"
- Check rate limit (3 attempts/hour)

### 3. Google OAuth Not Working

**Error:** `invalid_client` or `redirect_uri_mismatch`

**Solutions:**

1. **Verify credentials:**
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

2. **Check Google Console:**
- Verify redirect URI matches exactly
- Ensure Google People API is enabled
- Check OAuth consent screen is configured

3. **Clear config cache:**
```bash
php artisan config:clear
php artisan config:cache
```

### 4. Session Issues

**Error:** `Session started in middleware` or session not persisting

**Solutions:**

1. **Check session driver:**
```env
SESSION_DRIVER=database
```

2. **Ensure sessions table exists:**
```bash
php artisan session:table
php artisan migrate
```

3. **Clear session files:**
```bash
rm -rf storage/framework/sessions/*
```

---

## File Upload Issues

### 1. Upload Fails

**Error:** `The file may not be greater than X kilobytes`

**Solutions:**

1. **Check PHP upload limits:**
```ini
; php.ini
upload_max_filesize = 50M
post_max_size = 52M
```

2. **Check validation rules:**
```php
'media.*' => ['max:51200']  // 50MB in validation
```

3. **Restart web server:**
```bash
sudo systemctl restart apache2
# or
sudo systemctl restart php8.2-fpm
```

### 2. File Not Found After Upload

**Error:** Uploaded files return 404

**Solutions:**

1. **Ensure storage symlink exists:**
```bash
php artisan storage:link
```

2. **Check file permissions:**
```bash
chmod -R 775 storage/app/public
```

3. **Verify `.env` configuration:**
```env
FILESYSTEM_DISK=public
```

### 3. Video Thumbnail Generation Fails

**Error:** FFmpeg errors or no thumbnail generated

**Solutions:**

1. **Install FFmpeg:**
```bash
# Ubuntu/Debian
sudo apt-get install ffmpeg

# Windows - download from ffmpeg.org
```

2. **Verify installation:**
```bash
ffmpeg -version
```

3. **Check command execution:**
```php
// Test FFmpeg command
exec('ffmpeg -version', $output);
dd($output);
```

---

## Email Issues

### 1. Emails Not Sending

**Error:** No emails received

**Solutions:**

1. **Check mail configuration:**
```env
MAIL_MAILER=log  # Logs to storage/logs/laravel.log
# OR
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

2. **Check logs:**
```bash
tail -f storage/logs/laravel.log
```

3. **Test email:**
```bash
php artisan send-test-email your@email.com
```

### 2. SMTP Connection Error

**Error:** `Connection could not be established`

**Solutions:**

1. **Verify SMTP credentials:**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
```

2. **Check firewall:**
```bash
telnet smtp.gmail.com 587
```

3. **Use app-specific password (Gmail):**
- Enable 2FA on Google account
- Generate app-specific password
- Use app password in `.env`

---

## Frontend Issues

### 1. Vite Assets Not Loading

**Error:** `Vite manifest not found` or 404 on assets

**Solutions:**

1. **Build assets:**
```bash
npm run build
```

2. **Check Vite is running (dev):**
```bash
npm run dev
```

3. **Clear cache:**
```bash
php artisan view:clear
php artisan cache:clear
```

### 2. Styles Not Applying

**Error:** Page looks unstyled

**Solutions:**

1. **Ensure Tailwind is built:**
```bash
npm run build
```

2. **Check CSS import in `app.js`:**
```javascript
import './css/app.css';
```

3. **Verify `@vite` directive in Blade:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### 3. JavaScript Errors

**Error:** Console shows JavaScript errors

**Solutions:**

1. **Check browser console:**
- Open DevTools (F12)
- Check Console tab for errors

2. **Rebuild assets:**
```bash
npm install
npm run build
```

3. **Clear browser cache:**
- Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)

### 4. Real-time Features Not Working

**Error:** Chat messages not appearing, notifications not updating

**Solutions:**

1. **Check JavaScript is loaded:**
```javascript
// Browser console
console.log(window.isAuthenticated);
```

2. **Verify polling is running:**
```javascript
// Browser console - check for polling timers
```

3. **Check backend endpoints:**
```bash
# Test messages endpoint
curl -X GET http://localhost/chat/1/messages \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Chat & Messaging Issues

### 1. Messages Not Sending

**Error:** Messages fail to send

**Solutions:**

1. **Check conversation exists:**
```bash
php artisan tinker
>>> App\Models\Conversation::find(1);
```

2. **Verify user permissions:**
- Ensure user is participant in conversation

3. **Check database:**
```sql
SELECT * FROM messages WHERE conversation_id = 1 ORDER BY created_at DESC LIMIT 10;
```

### 2. Typing Indicators Not Working

**Error:** "User is typing" not showing

**Solutions:**

1. **Check cache is working:**
```bash
php artisan cache:clear
```

2. **Verify JavaScript:**
```javascript
// Browser console - check for typing indicator function
```

3. **Check polling interval:**
- Typing indicators poll every 1 second
- Cache TTL is 5 seconds

### 3. Online Status Not Updating

**Error:** Users always show offline

**Solutions:**

1. **Check status update endpoint:**
```bash
POST /user/online-status
```

2. **Verify polling:**
- Online status polls every 10 seconds
- Check JavaScript console for errors

3. **Update user status manually:**
```bash
php artisan tinker
>>> App\Models\User::find(1)->update(['is_online' => true]);
```

---

## Performance Issues

### 1. Slow Page Loads

**Symptoms:** Pages take >3 seconds to load

**Solutions:**

1. **Enable caching:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Check N+1 queries:**
```php
// In app/Providers/AppServiceProvider.php
use Illuminate\Database\Eloquent\Model;
Model::preventLazyLoading(!app()->isProduction());
```

3. **Optimize images:**
- Compress uploaded images
- Use WebP format

4. **Enable query logging (dev only):**
```env
LOG_LEVEL=debug
```

### 2. High Memory Usage

**Symptoms:** Server runs out of memory

**Solutions:**

1. **Increase PHP memory:**
```ini
; php.ini
memory_limit = 512M
```

2. **Optimize queries:**
```php
// Use eager loading
Post::with('user', 'comments')->get();
```

3. **Clear cache:**
```bash
php artisan cache:clear
```

### 3. Queue Backlog

**Symptoms:** Jobs not processing, emails delayed

**Solutions:**

1. **Start queue listener (development):**
```bash
php artisan queue:listen --tries=1
```

2. **Start queue worker (production):**
```bash
php artisan queue:work --sleep=3 --tries=3
```

3. **Check failed jobs:**
```bash
php artisan queue:failed
```

4. **Retry failed jobs:**
```bash
php artisan queue:retry all
```

5. **Use Supervisor (production):**
```ini
[program:nexus-queue]
command=php /path/to/artisan queue:work
autostart=true
autorestart=true
```

**Note:** Use `queue:listen` for development and `queue:work` for production (Supervisor).

---

## Server Issues

### 1. 500 Internal Server Error

**Error:** Generic server error

**Solutions:**

1. **Check logs:**
```bash
tail -f storage/logs/laravel.log
# or
tail -f /var/log/nginx/error.log
```

2. **Enable debug (development only):**
```env
APP_DEBUG=true
```

3. **Common causes:**
- Permission issues
- Missing dependencies
- Database connection error
- Syntax errors in code

### 2. 502 Bad Gateway

**Error:** Nginx/Apache returns 502

**Solutions:**

1. **Check PHP-FPM is running:**
```bash
sudo systemctl status php8.2-fpm
sudo systemctl restart php8.2-fpm
```

2. **Verify socket path:**
```nginx
# Nginx config
fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
```

3. **Check error logs:**
```bash
tail -f /var/log/nginx/error.log
```

### 3. 403 Forbidden

**Error:** Access denied

**Solutions:**

1. **Check file permissions:**
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

2. **Verify `.htaccess` exists:**
```bash
ls -la public/.htaccess
```

3. **Check directory listing:**
```nginx
# Nginx - ensure index.php is set
index index.php;
```

### 4. 404 Not Found

**Error:** Routes return 404

**Solutions:**

1. **Check URL rewriting:**
```bash
# Apache - enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. **Verify `.htaccess`:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

3. **Clear route cache:**
```bash
php artisan route:clear
php artisan route:cache
```

---

## Common Errors

### 1. Class Not Found

**Error:** `Class 'App\Models\User' not found`

**Solution:**

```bash
composer dump-autoload
```

### 2. Method Not Found

**Error:** `Call to undefined method`

**Solution:**

1. **Check method exists:**
```bash
grep -r "function methodName" app/
```

2. **Clear cache:**
```bash
php artisan cache:clear
```

### 3. Trait Not Found

**Error:** `Trait 'App\Traits\SomeTrait' not found`

**Solution:**

1. **Check file exists:**
```bash
ls -la app/Traits/
```

2. **Verify namespace:**
```php
namespace App\Traits;

trait SomeTrait
{
    // ...
}
```

### 4. View Not Found

**Error:** `View [posts.index] not found`

**Solution:**

1. **Check file exists:**
```bash
ls -la resources/views/posts/index.blade.php
```

2. **Clear view cache:**
```bash
php artisan view:clear
```

### 5. Route Not Defined

**Error:** `Route [login] not defined`

**Solution:**

1. **Check routes:**
```bash
php artisan route:list | grep login
```

2. **Clear route cache:**
```bash
php artisan route:clear
php artisan route:cache
```

### 6. Service Container Error

**Error:** `Target class [something] does not exist`

**Solution:**

1. **Clear config cache:**
```bash
php artisan config:clear
php artisan config:cache
```

2. **Check service providers:**
```bash
grep -r "SomethingServiceProvider" config/app.php
```

---

## Debugging Tools

### Laravel Debugging

```bash
# Enable debug mode (development only)
APP_DEBUG=true

# View logs
tail -f storage/logs/laravel.log

# Debug queries
DB::enableQueryLog();
// ... your code
dd(DB::getQueryLog());

# Debug variable
dd($variable);
dump($variable);

# Laravel debug bar (install first)
composer require barryvdh/laravel-debugbar --dev
```

### Browser Debugging

```javascript
// Browser console
console.log('Debug message');
console.table(data);

// Network tab
// Check XHR requests and responses

// Application tab
// Check localStorage, sessionStorage, cookies
```

### Database Debugging

```bash
# MySQL query log
mysql -u root -p -e "SET GLOBAL general_log = 'ON';"

# SQLite
sqlite3 database/database.sqlite ".schema"
```

---

## Getting Help

### Resources

- **Laravel Documentation:** https://laravel.com/docs
- **Laravel Community:** https://laracasts.com/discuss
- **Stack Overflow:** https://stackoverflow.com/questions/tagged/laravel
- **GitHub Issues:** Report bugs on project repository

### Before Asking for Help

1.  Check logs for errors
2.  Search existing issues
3.  Try suggested solutions
4.  Document steps to reproduce
5.  Include error messages
6.  Include environment details

### Information to Include

```
- PHP version: php -v
- Laravel version: php artisan --version
- Node.js version: node -v
- npm version: npm -v
- Database: SQLite/MySQL version
- Operating System: Windows/Mac/Linux
- Browser: Chrome/Firefox/Safari version
```

---

<div align="center">

**Nexus - Troubleshooting Guide**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
