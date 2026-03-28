# Nexus - Project Summary

Complete project summary and overview for Nexus social networking platform.

---

## Quick Reference

- **Name**: Nexus
- **Version**: 1.0.0
- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Frontend**: Blade + Vue.js 3 + Alpine.js
- **Database**: SQLite/MySQL
- **License**: MIT

---

## What is Nexus?

Nexus is a modern, full-featured social networking platform built with Laravel 12 and Vue.js. It provides a comprehensive suite of social features including posts, stories, real-time messaging, groups, notifications, and an admin panel for content moderation.

### Key Features

- **Posts** - Text, image, and video posts with mentions and hashtags
- **Stories** - 24-hour ephemeral content with reactions
- **Chat** - Real-time direct and group messaging
- **Groups** - Community building with invite links
- **Notifications** - Real-time notifications for all interactions
- **Profiles** - Customizable user profiles with privacy controls
- **Admin Panel** - Complete moderation tools
- **AI Assistant** - Built-in AI chatbot for support
- **Push Notifications** - Browser-based push notifications

---

## Technology Stack

### Backend

- **Laravel** (12.x): Web framework
- **PHP** (8.2+): Server language
- **SQLite/MySQL** (Latest): Database
- **Eloquent ORM**: Database ORM

### Frontend

- **Blade**: Server-side templates
- **Vue.js** (3.4): Reactive components
- **Alpine.js**: Lightweight interactivity
- **Tailwind CSS** (3.2): Styling
- **Axios** (1.11): HTTP client

### Build Tools

- **Vite** (6.4): Build tool
- **Composer** (2.x): PHP packages
- **npm** (9+): JavaScript packages

---

## Project Statistics

- **Models**: 25 Eloquent models
- **Controllers**: 39 total (17 main + 9 API + 13 Auth)
- **Middleware**: 9 middleware classes
- **Services**: 9 service classes
- **Console Commands**: 11 Artisan commands
- **Blade Views**: 67 templates
- **Vue Components**: 27 components
- **JavaScript Modules**: 16 legacy modules
- **Database Tables**: 24+ tables
- **Migrations**: 79 migration files
- **Routes**: 100+ defined routes
- **Mail Classes**: 3 mailable classes

---

## Documentation Structure

### Core Documentation

- [README.md](../README.md): Project overview and quick start
- [UML.md](../UML.md): UML diagrams and visual documentation
- [docs/FEATURES.md](docs/FEATURES.md): Complete feature documentation
- [docs/TECHNOLOGIES.md](docs/TECHNOLOGIES.md): Technology stack details
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): System architecture
- [docs/DATABASE.md](docs/DATABASE.md): Database schema
- [docs/API.md](docs/API.md): RESTful API reference
- [docs/SECURITY.md](docs/SECURITY.md): Security documentation
- [docs/INSTALLATION.md](docs/INSTALLATION.md): Installation guide
- [docs/TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md): Troubleshooting guide
- [docs/REALTIME.md](docs/REALTIME.md): Real-time features
- [docs/PUSH_NOTIFICATIONS.md](docs/PUSH_NOTIFICATIONS.md): Push notifications

---

## Quick Start

### Installation

```bash
# Clone repository
git clone https://github.com/your-org/nexus.git
cd nexus

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start development
composer run dev
```

### First Steps

1. Visit `http://localhost:8000`
2. Register a new account
3. Verify your email
4. Create your first post
5. Explore features

---

## Feature Highlights

### Authentication

-  Email/password registration with 6-digit verification
-  Google OAuth single sign-on
-  Password strength validation (3 of 5 criteria)
-  Reserved username blocking (50 names)
-  Disposable email blocking (16 domains)
-  Username change cooldown (3 days)

### Posts

-  Text (280 chars) + up to 30 media files
-  50MB per file, multiple formats
-  Video thumbnails via FFmpeg
-  @mentions with notifications
-  #hashtags with trending
-  Public/private privacy controls
-  Pin posts to profile
-  Soft deletes

### Stories

-  24-hour auto-expiry
-  Image, video, or text-only
-  View tracking
-  Emoji reactions
-  Multiple active stories

### Chat

-  Direct and group conversations
-  Real-time messaging (1-second polling)
-  Typing indicators
-  Read receipts
-  Message status (sent, delivered, read)
-  Voice messages
-  Media sharing

### Groups

-  Public/private groups
-  Member roles (admin/member)
-  Invite links
-  Group chat
-  Member management

### Social

-  Follow/unfollow system
-  User blocking
-  Private accounts
-  Online status
-  Profile customization
-  QR code sharing

### Admin

-  Dashboard with statistics
-  User management
-  Content moderation
-  Report management
-  Admin creation

---

## Security Features

- **CSRF Protection**: Automatic on all forms
- **SQL Injection**: Eloquent ORM (parameterized)
- **XSS Prevention**: Blade auto-escaping
- **Rate Limiting**: Auth (5/min), Posts (30/min)
- **Password Hashing**: Bcrypt (12 rounds)
- **Email Verification**: 6-digit code (10-min expiry)
- **Session Security**: HTTP-only, secure cookies
- **Input Validation**: Comprehensive rules

---

## Performance

### Caching Strategy

- Config cache
- Route cache
- View cache
- Query cache (database driver)
- Typing indicators (5s TTL)
- Online status (10s TTL)

### Database Optimization

- Foreign key indexes
- Composite indexes
- Eager loading
- Query optimization
- Pagination

### Frontend Optimization

- Vite bundling
- Code splitting
- Minification
- Obfuscation
- Lazy loading

---

## Real-Time Architecture

Nexus uses **polling-based real-time** instead of WebSockets:

**Polling Intervals:**
- Chat Messages: 1 second
- Notifications: 2 seconds
- Online Status: 10 seconds
- Typing Indicators: 1 second (5s cache)

**Advantages:**
- No WebSocket server required
- Works with all hosting providers
- Easy to scale
- Firewall-friendly

---

## API Endpoints

### Authentication

```
POST   /login                    - User login
POST   /logout                   - User logout
POST   /register                 - User registration
GET    /auth/google              - Google OAuth
POST   /forgot-password          - Request reset link
```

### Posts

```
GET    /api/posts                - Get feed
POST   /api/posts                - Create post
GET    /api/posts/{slug}         - Get post
DELETE /api/posts/{slug}         - Delete post
POST   /api/posts/{id}/like      - Like post
POST   /api/posts/{id}/save      - Save post
```

### Chat

```
GET    /api/conversations        - Get conversations
POST   /api/conversations/{id}/messages - Send message
GET    /api/conversations/{id}/messages - Get messages
POST   /api/conversations/{id}/read     - Mark as read
```

### Users

```
GET    /api/users/{username}     - Get profile
POST   /api/users/{username}/follow - Follow user
POST   /api/users/{username}/block  - Block user
GET    /api/explore              - Explore users
GET    /api/search-users         - Search users
```

---

## Development Workflow

### Available Commands

```bash
# Development
composer run dev        # Start all services
npm run dev            # Vite dev server
php artisan serve      # Laravel server

# Building
npm run build          # Build assets
npm run build:no-obf   # Build without obfuscation

# Testing
composer run test      # Run tests
php artisan test       # PHPUnit tests

# Code Quality
composer run pint      # Format PHP code
npm run lint           # Lint JavaScript

# Database
php artisan migrate    # Run migrations
php artisan db:seed    # Seed database
php artisan migrate:fresh --seed  # Fresh migrate
```

---

## File Structure

```
nexus/
├── app/
│   ├── Console/Commands/    (11 commands)
│   ├── Http/
│   │   ├── Controllers/     (39 controllers)
│   │   ├── Middleware/      (9 middleware)
│   │   └── Requests/        (Form requests)
│   ├── Models/              (25 models)
│   ├── Services/            (9 services)
│   └── Mail/                (3 mail classes)
│
├── database/
│   ├── migrations/          (79 migrations)
│   ├── factories/           (Model factories)
│   └── seeders/             (Database seeders)
│
├── resources/
│   ├── views/               (67 Blade templates)
│   ├── js/
│   │   ├── Components/      (27 Vue components)
│   │   ├── legacy/          (16 JS modules)
│   │   └── push-notifications.js
│   └── css/
│       └── app.css
│
├── routes/
│   ├── web.php              (Main routes)
│   ├── api.php              (API routes)
│   └── console.php          (Console routes)
│
├── public/
│   ├── sw.js                (Service worker)
│   ├── css/                 (Compiled CSS)
│   └── images/              (Default assets)
│
└── docs/                    (Documentation)
    ├── FEATURES.md
    ├── TECHNOLOGIES.md
    ├── ARCHITECTURE.md
    ├── DATABASE.md
    ├── API.md
    ├── SECURITY.md
    ├── INSTALLATION.md
    ├── TROUBLESHOOTING.md
    ├── REALTIME.md
    └── PUSH_NOTIFICATIONS.md
```

---

## Configuration

### Environment Variables

```env
# Application
APP_NAME="Nexus"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database

# Mail
MAIL_MAILER=log

# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

# Push Notifications
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

---

## Testing

### Run Tests

```bash
# All tests
composer run test

# Specific test file
php artisan test tests/Feature/PostTest.php

# With coverage
php artisan test --coverage
```

### Test Coverage

Tests cover:
- Authentication flows
- Post CRUD operations
- Comment system
- Chat messaging
- User relationships
- Admin functions
- API endpoints

---

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Enable HTTPS
- [ ] Configure database (MySQL)
- [ ] Set up Redis (optional)
- [ ] Configure queue worker
- [ ] Set up cron for scheduler
- [ ] Build assets (`npm run build`)
- [ ] Cache configuration
- [ ] Set up monitoring

### Deployment Commands

```bash
# Optimize
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrate
php artisan migrate --force

# Permissions
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

---

## Support & Contributing

### Getting Help

- **Documentation:** See [docs/](docs/) folder
- **Issues:** GitHub Issues
- **Discussions:** GitHub Discussions

### Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Run tests
5. Submit pull request

---

## License

Nexus is open-source software licensed under the [MIT License](LICENSE).

---

## Acknowledgments

Built with:
- [Laravel](https://laravel.com)
- [Vue.js](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [Vite](https://vitejs.dev)

---

<div align="center">

**Nexus - Social Networking Platform**

Last Updated: March 27, 2026

[Features](docs/FEATURES.md) • [API](docs/API.md) • [Security](docs/SECURITY.md) • [Installation](docs/INSTALLATION.md)

</div>
