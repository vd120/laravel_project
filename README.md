# Nexus

A modern, real-time social networking platform built with Laravel.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat)
![Performance](https://img.shields.io/badge/Performance-Optimized-brightgreen?style=flat)
![Last Updated](https://img.shields.io/badge/Updated-March%202026-blue?style=flat)

**Nexus** is a production-ready social networking platform featuring real-time chat, stories, posts, groups, and more. Built with performance and scalability in mind.

---

## Quick Start

### Requirements
- PHP 8.3+
- Composer 2.x
- Node.js 18+ (LTS recommended)
- Git

### Installation

**Linux/macOS:**
```bash
git clone <repository-url>
cd laravel_project
chmod +x setup.sh
./setup.sh
php artisan serve
```

**Windows (PowerShell):**
```powershell
git clone <repository-url>
cd laravel_project
.\setup.ps1
php artisan serve
```

**Windows (CMD):**
```cmd
git clone <repository-url>
cd laravel_project
setup.bat
php artisan serve
```

> **Tip:** The setup script automatically creates an admin user and configures everything for you.

### Default Login Credentials
- **URL:** `http://localhost:8000`
- **Email:** `admin@example.com`
- **Password:** `admin123`

> **Security Notice:** Change the default password immediately after installation!

---

## Features

### Content & Engagement
- **Posts** - Text, images, and videos (up to 30 files per post)
- **Comments** - Nested replies with mentions
- **Reactions** - Like, save, and share functionality
- **Follow System** - Follow/unfollow users with private accounts
- **Stories** - 24-hour ephemeral content with reactions and view tracking

### Communication
- **Real-time Chat** - One-on-one and group conversations
- **Groups** - Create and manage communities
- **Notifications** - Real-time updates for all activities
- **AI Assistant** - Menu-based AI chatbot

### Privacy & Safety
- **Privacy Controls** - Private accounts and posts
- **Block Users** - User blocking system
- **Admin Panel** - Content moderation and user management
- **Email Verification** - 6-digit code verification

### User Experience
- **Dark/Light Mode** - Theme toggle with persistence
- **Mobile Responsive** - Optimized for all devices
- **Multilingual** - English and Arabic (with RTL support)
- **Performance Optimized** - 71% faster page loads

---

## Setup Script

The automated setup script (`setup.sh`, `setup.ps1`, `setup.bat`) handles everything:

1. Checks system requirements (PHP, Composer, Node.js)
2. Installs PHP dependencies via Composer
3. Installs JavaScript dependencies via npm
4. Creates `.env` file from `.env.example`
5. Generates unique application key
6. Sets up SQLite database
7. Runs database migrations
8. Creates admin user with default credentials
9. Builds frontend assets with Vite
10. Creates storage symbolic link
11. Clears all Laravel caches

> **Estimated Time:** 2-5 minutes depending on your internet connection

---

## Public Tunnel

Share your local development site with the world using Cloudflare Tunnel:

**Linux/macOS:**
```bash
./start-tunnel.sh
```

**Windows (Git Bash):**
```bash
./start-tunnel.sh
```

**What You Get:**
- Instant public HTTPS URL
- Real-time visitor tracking
- Location detection (city, country)
- Device & browser information

> **Use Case:** Perfect for testing, demos, or sharing with remote team members.

---

## Documentation

| Document | Description |
|----------|-------------|
| [Authentication](docs/AUTHENTICATION.md) | Complete auth system with OAuth, email verification, and password reset |
| [Architecture](docs/ARCHITECTURE.md) | System architecture, directory structure, and database schema |
| [Features](docs/FEATURES.md) | Detailed feature documentation with API endpoints |
| [API Reference](docs/API.md) | RESTful API documentation with examples |
| [Frontend](docs/FRONTEND.md) | Vue.js 3 & Inertia.js 2 architecture guide |
| [Multilingual](docs/MULTILINGUAL.md) | English/Arabic language support with RTL layout |
| [Performance](docs/PERFORMANCE.md) | Performance optimizations (71% faster page loads) |
| [Changelog](CHANGELOG.md) | Version history and upcoming features |

> **Recommendation:** Start with [Architecture](docs/ARCHITECTURE.md) for system overview, then [Features](docs/FEATURES.md) for capabilities.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 11, PHP 8.3+, SQLite/MySQL |
| **Frontend** | Blade Templates, Tailwind CSS, Alpine.js, Vite |
| **Real-time** | Laravel Reverb, Laravel Echo, Pusher |
| **JavaScript** | Vue.js 3, Inertia.js 2, TypeScript |
| **Authentication** | Laravel Sanctum, Google OAuth |
| **Services** | Cloudflare Tunnel, FFmpeg (video processing) |
| **DevOps** | Git, Composer, npm, Vite |

---

## Troubleshooting

### Permission Issues (Linux/macOS)
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # If using Apache/Nginx
```

### Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear
php artisan optimize:clear
```

### Database Issues (SQLite)
```bash
# Reset database
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --seed
```

### Node.js / Frontend Issues
```bash
# Clean reinstall
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run build
```

### Still Having Issues?
1. Check `storage/logs/laravel.log` for errors
2. Enable debug mode in `.env`: `APP_DEBUG=true`
3. Check [Performance Documentation](docs/PERFORMANCE.md) for optimization tips
4. Review [Changelog](CHANGELOG.md) for recent fixes

---

## Contributing

We welcome contributions! Here's how you can help:

### How to Contribute
1. **Fork** the repository
2. **Create a feature branch** (`git checkout -b feature/YourFeature`)
3. **Commit your changes** (`git commit -m 'Add: YourFeature'`)
4. **Push to the branch** (`git push origin feature/YourFeature`)
5. **Open a Pull Request** on GitHub

### What We're Looking For
- Bug fixes
- New features
- Documentation improvements
- Performance optimizations
- Translations (especially Arabic improvements)
- Test coverage

### Development Setup
```bash
# After cloning
git checkout -b feature/YourFeature
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
```

> For more details, see [CONTRIBUTING.md](CONTRIBUTING.md) (coming soon)

---

## License

This project is open-source and available under the [MIT License](LICENSE).

---

<div align="center">

**Made with Laravel & Vue.js**

[Documentation](docs/README.md) - [Changelog](CHANGELOG.md) - [Performance](docs/PERFORMANCE.md)

</div>
