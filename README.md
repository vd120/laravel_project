# Nexus

A modern, real-time social networking platform built with Laravel.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-16+-339933?style=flat&logo=nodedotjs&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat)

---

##  Features

### Core Features
-  **Authentication System**
  - Email/Password login
  - Google OAuth integration
  - Email verification
  - Password reset

-  **Social Features**
  - User profiles with avatars
  - Friend system
  - Posts and comments
  - Like and save functionality
  - Real-time notifications

-  **Real-time Chat**
  - One-on-one conversations
  - Group chats
  - Message read receipts
  - Online status indicators
  - Typing indicators
  - File sharing

-  **Stories**
  - Photo/video stories
  - 24-hour expiration
  - Story reactions
  - View tracking

-  **Modern UI/UX**
  - Responsive design
  - Dark mode support
  - Mobile-friendly
  - Intuitive navigation

### Technical Features
-  **Performance**
  - Real-time visitor tracking with geolocation
  - Cloudflare tunnel integration
  - Optimized asset loading
  - Database query optimization

-  **Security**
  - CSRF protection
  - XSS prevention
  - SQL injection protection
  - Secure file uploads
  - Rate limiting

-  **Developer Tools**
  - One-command setup script
  - Cross-platform support (Linux, macOS, Windows)
  - Real-time request logging
  - Comprehensive error handling

---

##  Quick Start

### Prerequisites

- PHP 8.1 or higher
- Composer 2.x
- Node.js 16.x or higher
- npm 8.x or higher
- Git

### Installation

#### Linux / macOS

```bash
# Clone the repository
git clone <repository-url>
cd laravel_project

# Run setup script
chmod +x setup.sh
./setup.sh

# Start development server
php artisan serve
```

#### Windows (PowerShell - Recommended)

```powershell
# Clone the repository
git clone <repository-url>
cd laravel_project

# Run setup script
.\setup.ps1

# Start development server
php artisan serve
```

If you get an execution policy error, run:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\setup.ps1
```

#### Windows (Command Prompt)

```cmd
REM Clone the repository
git clone <repository-url>
cd laravel_project

REM Run setup script
setup.bat

REM Start development server
php artisan serve
```

#### Windows (Git Bash / WSL)

```bash
# Clone the repository
git clone <repository-url>
cd laravel_project

# Run setup script
./setup.sh

# Start development server
php artisan serve
```

---

##  What the Setup Script Does

### Step 1: System Requirements Check
- PHP (with version check)
- Composer
- Node.js
- npm
- Git

### Step 2: PHP Dependencies
- Installs all PHP packages via `composer install`

### Step 3: JavaScript Dependencies
- Installs all Node.js packages via `npm install`

### Step 4: Environment Setup
- Creates `.env` from `.env.example`
- Generates application encryption key

### Step 5: Database Setup
- **SQLite** (recommended for development)
  - Automatic setup
  - Creates `database/database.sqlite`
- **MySQL/MariaDB** (optional)
  - Asks for database credentials
  - Configures `.env` file

### Step 6: Database Migrations
- Runs all database migrations
- Creates tables and structure

### Step 7: Admin User
- Creates admin account automatically
- **Email:** `admin@example.com`
- **Password:** `admin123`

### Step 8: Frontend Assets
- Builds CSS and JavaScript files
- Optimizes for production

### Step 9: Storage Link
- Creates symbolic link for file uploads
- Enables public access to storage

### Step 10: Cache Clearing
- Clears all Laravel caches
- Ensures fresh configuration

---

##  After Setup

### Start Development Server
```bash
php artisan serve
```

Then visit: `http://localhost:8000`

### Login with Admin Account
- **URL:** `http://localhost:8000/login`
- **Email:** `admin@example.com`
- **Password:** `admin123`

### Start Public Tunnel (Optional)
For sharing your development site publicly:

**Linux/macOS:**
```bash
./start-tunnel.sh
```

**Windows (Git Bash/WSL):**
```bash
./start-tunnel.sh
```

---

##  Manual Installation (If Scripts Don't Work)

### 1. Install PHP Dependencies
```bash
composer install
```

### 2. Install JavaScript Dependencies
```bash
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Setup Database
Create `database/database.sqlite` or configure MySQL in `.env`

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Admin User
```bash
php artisan db:seed --class=AdminUserSeeder
```

### 7. Build Assets
```bash
npm run build
```

### 8. Create Storage Link
```bash
php artisan storage:link
```

### 9. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

##  Troubleshooting

### Permission Issues (Linux/macOS)
```bash
chmod -R 755 storage bootstrap/cache
```

### Node.js Version Issues
Make sure you're using Node.js 16.x or higher:
```bash
node -v
```

### Composer Issues
Clear Composer cache:
```bash
composer clear-cache
composer install
```

### Database Migration Errors
For SQLite, make sure the file exists:
```bash
touch database/database.sqlite
php artisan migrate
```

For MySQL, create the database first:
```sql
CREATE DATABASE laravel;
```

---

##  Requirements

### Minimum Requirements
- PHP 8.1 or higher
- Composer 2.x
- Node.js 16.x or higher
- npm 8.x or higher
- Git

### PHP Extensions Required
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

### Default Admin Account

After setup, login with:
- **Email:** `admin@example.com`
- **Password:** `admin123`
- **URL:** `http://localhost:8000/login`

---

##  Documentation

| Topic | Description |
|-------|-------------|
| [Authentication](docs/AUTHENTICATION.md) | Auth system documentation |
| [Architecture](docs/ARCHITECTURE.md) | System architecture overview |
| [Testing](docs/TESTING.md) | Testing guidelines |

---

##  Public Tunnel (Share Your Development Site)

Share your local development site publicly with Cloudflare tunnel:

### Linux/macOS
```bash
./start-tunnel.sh
```

### Windows (Git Bash/WSL)
```bash
./start-tunnel.sh
```

**Features:**
-  Instant public URL
-  Real-time visitor tracking
-  Visitor location (city, country, coordinates)
-  Device and browser detection
-  Automatic cleanup on exit

**Example Output:**
```
Cloudflared Tunnel Launcher

  Starting Laravel server..... ✓
  Starting Cloudflared tunnel..... ✓
  Configuring .env..... ✓

Tunnel is running!

  Public URL:     https://yoursite.trycloudflare.com
  OAuth Callback: https://yoursite.trycloudflare.com/auth/google/callback

Visitor Logs:

[2026-03-05 14:00:00]  102.190.255.150 - GET - /login
   Cairo, Egypt (30.0507, 31.2489)
   Mobile | Chrome 145.0
   Country: EG
────────────────────────────────────────
```

---

##  Tech Stack

### Backend
- **Laravel 11** - PHP Framework
- **PHP 8.3+** - Server-side language
- **MySQL/SQLite** - Database
- **Redis** - Caching (optional)

### Frontend
- **Blade Templates** - Templating engine
- **Tailwind CSS** - Utility-first CSS
- **Alpine.js** - Lightweight JavaScript
- **Vite** - Build tool

### Real-time Features
- **Laravel Reverb** - WebSocket server
- **Laravel Echo** - Event broadcasting

### Third-Party Services
- **Google OAuth** - Social authentication
- **Cloudflare Tunnel** - Public URL sharing
- **ipapi.co** - IP geolocation

---

##  Project Structure

```
laravel_project/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # Request handlers
│   │   └── Middleware/     # Request filters
│   ├── Models/             # Database models
│   └── Providers/          # Service providers
├── database/
│   ├── migrations/         # Database schema
│   ├── seeders/            # Test data
│   └── factories/          # Model factories
├── resources/
│   ├── views/              # Blade templates
│   └── js/                 # JavaScript files
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
├── public/                 # Public assets
├── storage/                # File uploads, logs
├── tests/                  # Automated tests
├── setup.sh                # Linux/macOS setup
├── setup.bat               # Windows CMD setup
├── setup.ps1               # Windows PowerShell setup
└── start-tunnel.sh         # Tunnel launcher
```

---

##  Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ChatTest.php

# Run with coverage
php artisan test --coverage
```

---

##  Development

### Start Development Server
```bash
php artisan serve
```

### Watch for Changes
```bash
# Terminal 1: Backend
php artisan serve

# Terminal 2: Frontend
npm run dev

# Terminal 3: Queue (optional)
php artisan queue:work
```

### Build for Production
```bash
npm run build
```

---

##  Troubleshooting

### Permission Issues (Linux/macOS)
```bash
chmod -R 755 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Database Issues
```bash
# SQLite: Recreate database
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate

# MySQL: Recreate database
DROP DATABASE laravel;
CREATE DATABASE laravel;
php artisan migrate
```

### Node.js Issues
```bash
rm -rf node_modules package-lock.json
npm install
```

---

##  Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

##  License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

##  Acknowledgments

- Laravel community
- Tailwind CSS team
- All contributors

---

##  Support

For issues and questions:
- Create an issue on GitHub
- Check existing documentation
- Review troubleshooting guide

---

<div align="center">

**Made with  using Laravel**

[⬆ Back to Top](#nexus)

</div>
