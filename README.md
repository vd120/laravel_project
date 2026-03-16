# Nexus - Social Networking Platform

<div align="center">

![Nexus Banner](https://img.shields.io/badge/Nexus-Social_Network-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)

**A modern, production-ready social networking platform built with Laravel 12**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat&logo=vue.js)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-Proprietary-FF2D20?style=flat)]()

[Features](#features) • [Quick Start](#quick-start) • [Documentation](#documentation) • [API Reference](docs/API.md)

</div>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Quick Start](#quick-start)
- [Documentation](#documentation)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

**Nexus** is a production-ready social networking platform that enables users to connect, share, and communicate in real-time. Built with modern web technologies, it offers a seamless experience for creating posts, sharing stories, messaging, and building communities.

### Key Statistics

| Metric | Count |
|--------|-------|
| **Controllers** | 31 (30 + 1 base class) |
| **Models** | 19 Eloquent models |
| **Middleware** | 8 middleware classes |
| **Migrations** | 58 database migrations |
| **Tables** | 24 database tables |
| **Services** | 4 service classes |
| **Console Commands** | 6 Artisan commands |
| **JavaScript Modules** | 16 legacy modules |
| **Languages** | 2 (English & Arabic with RTL) |

### Core Features

- **Content:** Posts with 30 media attachments (50MB each), Comments with threading, 24-hour Stories
- **Social:** Follow system, Private accounts, User blocking, Mentions
- **Communication:** Real-time chat (polling-based), Group conversations,online indicator, Typing indicators, Read receipts
- **Communities:** Groups with roles, Member management, Invite links
- **Safety:** Admin panel, Content moderation, Account suspension, Email verification
- **UX:** Dark/Light themes, Mobile responsive, Multilingual (EN/AR with RTL)
- **AI:** Menu-based AI assistant chatbot

### Platform Preview

<div align="center">

![Nexus Landing Page - English](Images/landingPage-en.png?v=1)

*English Landing Page*

![Nexus Landing Page - Arabic](Images/landingPage-ar.png?v=2)

*Arabic Landing Page (RTL Support)*

</div>

---

## Features

### Content Management
- **Posts** — Create text posts (280 chars) with up to 30 images/videos (50MB each)
- **Comments** — Nested replies with @mentions and likes
- **Reactions** — Like, save, and share posts
- **Stories** — Ephemeral 24-hour content with view tracking and reaction
- **Media Processing** — Auto-compression, video thumbnails via FFmpeg
- **Slug URLs** — SEO-friendly 24-character unique slugs for posts and stories

### Real-Time Communication (Polling-Based)
- **Direct Messages** — One-on-one conversations with media sharing
- **Group Chat** — Multi-user conversations via groups
- **Typing Indicators** — Real-time typing status (5-second cache, 2s polling)
- **Read Receipts** — Track message delivery and read status
- **Message Actions** — Delete for me/everyone, edit, reply

### Social Network
- **Follow System** — Follow/unfollow users with private account support
- **User Profiles** — Customizable avatars, cover photos, bio, social links
- **Privacy Controls** — Private accounts, post-level privacy settings
- **Block Users** — Block unwanted interactions
- **Explore Users** — Discover new users
- **Username System** — Unique usernames with 3-day cooldown between changes

### Groups
- **Create Groups** — Public or private communities
- **Member Roles** — Admin and member permissions
- **Invite Links** — Shareable unique links for quick joining
- **Group Chat** — Dedicated conversation for each group
- **Member Management** — Add/remove members, promote to admin

### AI Assistant
- **Menu-Based Chat** — Interactive AI chatbot interface
- **Context Aware** — Remembers conversation history
- **Quick Actions** — Pre-defined prompts for common tasks

### Admin Panel
- **Dashboard** — Platform statistics and analytics
- **User Management** — View, edit, suspend, or delete users
- **Content Moderation** — Delete posts, comments, and stories
- **Admin Creation** — Create new admin accounts

### Authentication & Security
- **Email Verification** — 6-digit code system (10-minute expiry)
- **Google OAuth** — Single sign-on via Google
- **Password Reset** — Email-based password recovery
- **Account Suspension** — Admin-controlled suspension
- **Rate Limiting** — API protection (5/min auth, 30/min posts, 20/min comments)
- **Reserved Usernames** — 40+ blocked names (admin, moderator, etc.)
- **Disposable Email Blocking** — 16+ temporary email domains blocked
- **Password Strength** — Requires 3 of 5 criteria (length, lowercase, uppercase, digit, special)

---

## Tech Stack

### Backend
- **Laravel 12.x** — Web application framework
- **PHP 8.2+** — Server-side scripting
- **SQLite** — Default database (development)
- **MySQL 8.0+** — Production database (optional)
- **Laravel Sanctum 4.x** — API authentication
- **Laravel Socialite 5.24** — OAuth (Google)
- **Intervention Image 3.11** — Image processing
- **FFmpeg** — Video processing (thumbnails, compression)

### Frontend
- **Blade Templates** — Server-side rendering (primary UI)
- **Vue.js 3.4** — Component framework (available for components)
- **Tailwind CSS 3.2** — Utility-first CSS
- **Alpine.js** — Lightweight interactivity
- **Vite 6.4** — Build tool
- **Axios 1.11** — HTTP client for AJAX

### Services & Tools
- **Google OAuth** — Social authentication
- **Cloudflare Tunnel** — Public URL sharing for development
- **JavaScript Obfuscator/terser/uglifyJS** — Code protection (custom scripts)

> **Note:** For a complete list of all technologies with versions, see [Technologies Documentation](docs/TECHNOLOGIES.md)

---

## Quick Start

### Prerequisites

Ensure you have the following installed:

- **PHP** 8.3 or higher
- **Composer** 2.x
- **Node.js** 18+ (LTS recommended)
- **Git**

### Installation

#### Linux/macOS

```bash
# Clone the repository
git clone https://github.com/vd120/nexus.git
cd laravel_project

# Make setup script executable
chmod +x setup.sh

# Run automated setup
./setup.sh

# Start development server
php artisan serve
```

#### Windows (PowerShell)

```powershell
# Clone the repository
git clone https://github.com/vd120/nexus.git
cd laravel_project

# Run setup script
.\setup.ps1

# Start development server
php artisan serve
```

#### Windows (Command Prompt)

```cmd
REM Clone the repository
git clone https://github.com/vd120/nexus.git
cd laravel_project

REM Run setup script
setup.bat

REM Start development server
php artisan serve
```

### Default Credentials

After setup, login with:
- **URL:** `http://localhost:8000`
- **Email:** `admin@example.com`
- **Password:** `admin123`

> **Security Notice:** Change the default password immediately after installation!

---

## Documentation

Comprehensive documentation is available in the [`docs/`](docs/) directory:

### Getting Started
- [Installation Guide](docs/INSTALLATION.md) — Detailed setup instructions
- [Troubleshooting](docs/TROUBLESHOOTING.md) — Common issues and solutions

### Technical Documentation
- [Architecture](docs/ARCHITECTURE.md) — System design, directory structure, data flow
- [Technologies](docs/TECHNOLOGIES.md) — Complete tech stack with versions
- [Database Schema](docs/DATABASE.md) — Entity relationships, table definitions
- [API Reference](docs/API.md) — RESTful API endpoints with examples

### Feature Documentation
- [Features](docs/FEATURES.md) — Complete feature documentation with flow diagrams
- [Real-Time Features](docs/REALTIME.md) — Polling-based real-time implementation
- [Security Report](docs/SECURITY.md) — Security audit, vulnerabilities, best practices

### Frontend & UI
- [Frontend Guide](docs/FRONTEND.md) — Blade templates, JavaScript modules
- [Multilingual](docs/MULTILINGUAL.md) — English/Arabic support, RTL layout

### UML Diagrams

For visual representations of the system architecture and design, see [UML.md](UML.md):

- **Class Diagram** — Eloquent models and their relationships
- **ERD** — Database table relationships
- **Sequence Diagrams** — Authentication, posts, messaging, and more
- **Use Case Diagram** — User interactions with the system

---

## Project Structure

```
laravel_project/
├── app/
│   ├── Console/Commands/      # Artisan commands (6 commands)
│   ├── Http/
│   │   ├── Controllers/       # Controllers (31 files: 30 controllers + 1 base class)
│   │   │   ├── Api/           # API controllers (6 controllers)
│   │   │   ├── Auth/          # Auth controllers (13 controllers)
│   │   │   └── (Main)         # Main controllers (11 controllers)
│   │   ├── Middleware/        # Middleware (8 classes)
│   │   └── Requests/          # Form validation requests
│   ├── Mail/                  # Mail classes (1: VerificationCodeMail)
│   ├── Models/                # Eloquent models (19 models)
│   ├── Providers/             # Service providers (2 providers)
│   └── Services/              # Business logic (4 services)
├── bootstrap/                 # Application bootstrap
├── config/                    # Configuration files (10 configs)
├── database/
│   ├── factories/             # Model factories for testing
│   ├── migrations/            # Database migrations (58 migrations)
│   └── seeders/               # Database seeders
├── public/                    # Web root (index.php, assets)
├── resources/
│   ├── css/                   # Stylesheets (Tailwind CSS)
│   ├── js/
│   │   ├── Components/        # Vue 3 components
│   │   ├── Pages/             # Inertia pages
│   │   ├── Composables/       # Vue composables
│   │   ├── Layouts/           # Vue layouts
│   │   ├── legacy/            # JavaScript modules (16 files)
│   │   └── app.js             # Application entry point
│   └── views/                 # Blade templates (primary UI)
│       ├── admin/             # Admin panel views
│       ├── auth/              # Authentication views
│       ├── chat/              # Chat/messaging views
│       ├── groups/            # Group views
│       ├── posts/             # Post views
│       ├── stories/           # Story views
│       ├── users/             # User profile views
│       └── ai/                # AI assistant views
├── routes/
│   ├── web.php                # Web routes (main routing)
│   ├── api.php                # API routes ( Sanctum auth)
│   └── console.php            # Console routes
├── storage/
│   ├── app/public/            # User uploads (posts, avatars, stories)
│   ├── framework/             # Framework cache
│   └── logs/                  # Application logs
├── tests/                     # PHPUnit/Pest tests
├── lang/                      # Language files (en/ar)
│   ├── en/                    # English translations (12 files)
│   └── ar/                    # Arabic translations (12 files)
├── .env.example               # Environment template
├── artisan                    # Laravel CLI
├── composer.json              # PHP dependencies
├── package.json               # Node.js dependencies
└── vite.config.js             # Vite build configuration
```

---

## Setup Scripts

The automated setup scripts handle the entire installation process with full MySQL support.

### What It Does

1. Checks system requirements (PHP, Composer, Node.js, extensions)
2. Installs PHP dependencies via Composer
3. Installs JavaScript dependencies via npm
4. Creates `.env` from `.env.example`
5. Generates unique application key
6. **Database Setup** - Choose SQLite or MySQL
7. **MySQL Options:**
   - Create new database with user
   - Use existing database
   - Automatic privilege configuration
8. Runs database migrations (58 migrations)
9. Creates admin user with default credentials
10. Builds frontend assets with Vite
11. Creates storage symbolic link
12. Clears all Laravel caches

**Estimated Time:** 3-5 minutes (depending on internet connection)

### Running Setup

**Linux/macOS:**
```bash
chmod +x setup.sh
./setup.sh
```

**Windows PowerShell:**
```powershell
.\setup.ps1
```

**Windows CMD:**
```cmd
setup.bat
```

### Manual Database Setup (Optional)

If you prefer to set up the database manually:

```bash
# MySQL
mysql -u root -p < database/setup_database.sql

# Then run migrations
php artisan migrate
```

---

## Public Tunnel

Share your local development environment via Cloudflare Tunnel:

### Linux/macOS
```bash
./start-tunnel.sh
```

### Windows (Git Bash)
```bash
./start-tunnel.sh
```

**Features:**
- Instant public HTTPS URL
- Real-time visitor tracking
- Location detection (city, country)
- Device & browser information
- OAuth callback support

---

## Troubleshooting

Having setup issues? See the complete [Troubleshooting Guide](docs/TROUBLESHOOTING.md) for:

- Common setup errors and solutions
- Database connection issues
- PHP extension problems
- Permission issues
- Migration failures
- Build errors

---

## Contributing

We welcome contributions! Here's how to help:

### How to Contribute

1. **Fork** the repository
2. **Create a feature branch** (`git checkout -b feature/YourFeature`)
3. **Commit your changes** (`git commit -m 'Add: YourFeature'`)
4. **Push to the branch** (`git push origin feature/YourFeature`)
5. **Open a Pull Request** on GitHub

### What We Need

- Bug fixes
- New features
- Documentation improvements
- Performance optimizations
- Translations (especially Arabic)
- Test coverage
- UI/UX enhancements

### Development Setup

```bash
git checkout -b feature/YourFeature
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
```

### Code Standards

- **PHP:** Follow PSR-12 coding standards (Laravel Pint)
- **JavaScript:** ESLint + Prettier configuration
- **Testing:** Write tests for new features (Pest PHP)

---

## License

This project is proprietary software. All rights reserved.

> **Note:** This project is for demonstration/educational purposes. If you intend to use it commercially, please ensure you have proper licensing for all dependencies and comply with their respective licenses.

---

<div align="center">

**Built with Laravel 12 & Vue.js 3**

[Documentation](docs/) • [API Reference](docs/API.md) • [Features](docs/FEATURES.md) • [Real-Time](docs/REALTIME.md)

**Last Updated:** March 16, 2026

</div>
