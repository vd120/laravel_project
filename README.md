# Nexus

A modern, real-time social networking platform built with Laravel.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat)

---

##  Quick Start

### Requirements
- PHP 8.3+
- Composer 2.x
- Node.js 16+
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

### Default Login
- **Email:** `admin@example.com`
- **Password:** `admin123`
- **URL:** `http://localhost:8000`

---

##  Features

-  User profiles with avatars & cover images
-  Posts with text, images, and videos (up to 30 files)
-  Comments with nested replies
-  Real-time chat (one-on-one & group)
-  Stories (24-hour expiration)
-  Like, save, and share functionality
-  Follow/unfollow system
-  Block users
-  Private accounts
-  Real-time notifications
-  Dark/Light mode
-  Mobile responsive
-  Admin panel for moderation
-  Public tunnel sharing (Cloudflare)

---

##  Setup Script

The setup script (`setup.sh`, `setup.ps1`, `setup.bat`) automatically:

1. ✅ Checks system requirements
2. ✅ Installs PHP dependencies (Composer)
3. ✅ Installs JavaScript dependencies (npm)
4. ✅ Creates `.env` file
5. ✅ Generates app key
6. ✅ Sets up SQLite database
7. ✅ Runs migrations
8. ✅ Creates admin user
9. ✅ Builds frontend assets
10. ✅ Creates storage link
11. ✅ Clears all caches

---

##  Public Tunnel

Share your local development site publicly:

**Linux/macOS:**
```bash
./start-tunnel.sh
```

**Windows (Git Bash):**
```bash
./start-tunnel.sh
```

**Features:**
- Instant public URL
- Real-time visitor tracking
- Location detection (city, country)
- Device & browser info
- Automatic cleanup on exit

---

##  Documentation

| Document | Description |
|----------|-------------|
| [Authentication](docs/AUTHENTICATION.md) | Auth system details |
| [Architecture](docs/ARCHITECTURE.md) | System overview |
| [Features](docs/FEATURES.md) | Feature documentation |
| [API](docs/API.md) | API endpoints |

---

##  Tech Stack

**Backend:** Laravel 11, PHP 8.3+, MySQL/SQLite  
**Frontend:** Blade, Tailwind CSS, Alpine.js, Vite  
**Real-time:** Laravel Reverb, Laravel Echo  
**Services:** Google OAuth, Cloudflare Tunnel

---

##  Troubleshooting

**Permission Issues:**
```bash
chmod -R 755 storage bootstrap/cache
```

**Clear Caches:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

**Database Issues:**
```bash
# SQLite
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate
```

**Node.js Issues:**
```bash
rm -rf node_modules package-lock.json
npm install
```

---

##  Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/YourFeature`)
3. Commit changes (`git commit -m 'Add YourFeature'`)
4. Push to branch (`git push origin feature/YourFeature`)
5. Open Pull Request

---

##  License

MIT License - see [LICENSE](LICENSE) file for details.

---

<div align="center">

**Made with  using Laravel**

</div>
