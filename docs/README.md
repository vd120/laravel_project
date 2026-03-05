# Nexus - Social Media Platform

## Project Overview

**Nexus** is a full-featured social media platform built with Laravel 12, Vue.js 3, and Inertia.js. It provides a modern, real-time social networking experience with posts, stories, private messaging, group chats, notifications, and comprehensive admin moderation tools.

### Key Features

- **Authentication & User Management**
  - Email/password login with 6-digit email verification
  - Google OAuth integration
  - Password reset via email
  - Account suspension system
  - Admin role with full platform control
  - 3-day username change cooldown

- **Posts & Content**
  - Text posts (280 char limit) with up to 30 media files
  - Image compression (GD/Imagick)
  - Video support (MP4, MOV, AVI, WebM)
  - Public/private post visibility
  - Like, save, and comment functionality
  - @mention support with notifications
  - Slug-based URLs (24-char random)

- **Stories**
  - 24-hour ephemeral stories
  - Image and video support
  - Video trimming (max 60 seconds)
  - View tracking
  - Emoji reactions
  - Viewers list for story authors

- **Messaging (Chat)**
  - Direct messages between users
  - Group chats with admin/member roles
  - Media messages (images, videos, files)
  - Read receipts and delivery status
  - Message deletion (for me/everyone)
  - Typing indicators
  - Clear chat functionality
  - Invite links for groups

- **Social Features**
  - Follow/unfollow system with notifications
  - Block users system
  - Private accounts (approval required for followers)
  - Saved posts functionality
  - Online status tracking
  - User discovery (explore page)

- **Groups**
  - Create groups with avatar and description
  - Admin/member roles
  - Add/remove members
  - Promote/demote admins
  - Invite links
  - Quick invite via private message

- **Notifications**
  - Real-time via polling (5-second intervals)
  - Types: follow, like, comment, mention, message, group_invite
  - Unread count badge
  - Mark as read/delete functionality

- **Admin Panel**
  - Dashboard with statistics
  - User management (edit, suspend, delete)
  - Content moderation (posts, comments, stories)
  - Create admin accounts

- **AI Assistant**
  - Rule-based chatbot
  - 9 menu options for help topics

---

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL/PostgreSQL/SQLite
- **Cache**: Redis/File
- **Queue**: Database/Redis
- **Authentication**: Laravel Sanctum, Laravel Socialite
- **Image Processing**: Intervention Image 3.x
- **Real-time**: Polling-based (5-second intervals)

### Frontend
- **Framework**: Vue.js 3.x
- **Inertia.js**: 2.x (bridges Laravel & Vue)
- **CSS**: TailwindCSS 3.x/4.x
- **Build Tool**: Vite 7.x
- **Language**: TypeScript 5.x
- **HTTP Client**: Axios

### DevOps & Tools
- **Testing**: Pest PHP, PHPUnit
- **Local Development**: Laravel Sail (Docker)
- **Code Quality**: Laravel Pint, ESLint, Prettier
- **Debugging**: Laravel Tinker, Laravel Pail

---

## Project Structure

```
laravel_project/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Request handling
│   │   │   ├── Api/           # API controllers
│   │   │   ├── Auth/          # Authentication controllers
│   │   │   └── ...            # Main controllers
│   │   ├── Middleware/        # HTTP middleware
│   │   ├── Requests/          # Form request validation
│   │   └── Resources/         # API resources
│   ├── Models/                # Eloquent models
│   ├── Mail/                  # Email classes
│   ├── Services/              # Business logic services
│   ├── Providers/             # Service providers
│   └── Console/Commands/      # Artisan commands
├── bootstrap/                 # Application bootstrapping
├── config/                    # Configuration files
├── database/
│   ├── migrations/            # Database migrations
│   ├── seeders/               # Database seeders
│   └── factories/             # Model factories
├── public/                    # Public assets
├── resources/
│   ├── css/                   # Stylesheets
│   ├── js/
│   │   ├── Components/        # Vue components
│   │   ├── Layouts/           # Vue layouts
│   │   ├── Pages/             # Inertia pages
│   │   └── types/             # TypeScript definitions
│   ├── views/                 # Blade templates
│   └── emails/                # Email templates
├── routes/
│   ├── web.php                # Web routes
│   ├── api.php                # API routes
│   └── console.php            # Console routes
├── storage/                   # Uploaded files, logs
├── tests/                     # Test files
└── vendor/                    # Composer dependencies
```

---

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL, PostgreSQL, or SQLite
- Redis (optional, for cache/sessions)

### Step 1: Clone and Install Dependencies

```bash
# Clone the repository
cd laravel_project

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Step 2: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=your_password

# Configure mail settings (for verification/password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@nexus.com
MAIL_FROM_NAME="${APP_NAME}"

# Google OAuth (optional)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Step 3: Database Setup

```bash
# Run migrations
php artisan migrate

# (Optional) Seed database
php artisan db:seed
```

### Step 4: Storage Setup

```bash
# Create symbolic link for storage
php artisan storage:link
```

### Step 5: Build Frontend Assets

```bash
# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Step 6: Start the Application

```bash
# Start Laravel development server
php artisan serve

# Access the application at http://localhost:8000
```

### Optional: Queue Worker

```bash
# Start queue worker for background jobs
php artisan queue:work
```

### Optional: Story Cleanup Scheduler

```bash
# Add to crontab (runs hourly)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# Or manually cleanup expired stories
php artisan stories:cleanup
```

---

## Running Tests

```bash
# Run all tests
php artisan test

# Run with Pest (if configured)
./vendor/bin/pest

# Run specific test file
php artisan test tests/Feature/PostTest.php
```

---

## Development Commands

```bash
# Code formatting
composer format          # Laravel Pint
npm run format           # Prettier

# Linting
npm run lint             # ESLint

# Database
php artisan migrate:fresh --seed    # Fresh migration with seeding
php artisan db:seed                  # Run seeders

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate
php artisan make:controller ControllerName
php artisan make:model ModelName -m
php artisan make:request RequestName
php artisan make:mail MailName
```

---

## API Documentation

See [API Documentation](./API.md) for detailed API endpoints.

### Quick Reference

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/posts` | GET | ✅ | Get all posts |
| `/api/posts` | POST | ✅ | Create post |
| `/api/posts/{id}` | GET | ✅ | Get single post |
| `/api/posts/{id}/like` | POST | ✅ | Like post |
| `/api/comments` | POST | ✅ | Create comment |
| `/api/users/{id}` | GET | ✅ | Get user profile |
| `/api/users/{id}/follow` | POST | ✅ | Follow user |
| `/api/notifications` | GET | ✅ | Get notifications |
| `/api/conversations` | GET | ✅ | Get conversations |

---

## Architecture

### MVC Pattern
Nexus follows the Model-View-Controller pattern:
- **Models**: Eloquent models in `app/Models/`
- **Views**: Blade templates and Vue components in `resources/`
- **Controllers**: HTTP controllers in `app/Http/Controllers/`

### Service Layer
Business logic is encapsulated in services:
- **MentionService**: Parse and process @mentions
- **RealtimeService**: Real-time data via polling

### Repository Pattern
Data access is handled through Eloquent ORM with relationships defined in models.

### Event-Driven
Notifications are created through events triggered by user actions (follow, like, comment, mention).

---

## Security Features

- **CSRF Protection**: Automatic via Laravel middleware
- **XSS Prevention**: Escaped output in Blade/Vue
- **SQL Injection Prevention**: Parameterized queries via Eloquent
- **Password Hashing**: Bcrypt algorithm
- **Email Verification**: 6-digit code verification
- **Rate Limiting**: Login attempts (5/minute)
- **Input Validation**: Form Request classes
- **Authorization**: Middleware and policies
- **HTTPS Enforcement**: Production middleware

---

## Performance Optimizations

- **Eager Loading**: Relationships loaded efficiently
- **Query Caching**: RealtimeService cache layer
- **Image Compression**: GD/Imagick for uploads
- **Lazy Loading**: Pagination for large datasets
- **Asset Optimization**: Vite bundling and minification

---

## Database Schema

### Core Tables

| Table | Description |
|-------|-------------|
| `users` | User accounts with auth |
| `profiles` | User profile information |
| `posts` | User posts |
| `post_media` | Post media attachments |
| `comments` | Post comments (nested) |
| `follows` | User follow relationships |
| `likes` | Post likes |
| `comment_likes` | Comment likes |
| `blocks` | User blocks |
| `saved_posts` | Saved/bookmarked posts |
| `stories` | Ephemeral stories |
| `story_views` | Story view tracking |
| `story_reactions` | Story reactions |
| `conversations` | DM and group chats |
| `messages` | Chat messages |
| `groups` | User groups |
| `group_members` | Group membership |
| `notifications` | User notifications |
| `mentions` | Polymorphic mentions |

See [Database Documentation](./DATABASE.md) for detailed schema.

---

## Frontend Architecture

### Inertia.js
Nexus uses Inertia.js to build single-page applications without building an API:
- Server-side routing with client-side navigation
- Vue.js components for UI
- Laravel controllers for logic
- No need for separate API endpoints

### Component Structure

```
resources/js/
├── Components/          # Reusable UI components
├── Layouts/             # Page layouts
├── Pages/               # Inertia page components
└── types/               # TypeScript definitions
```

### State Management
- Local component state (Vue reactivity)
- Inertia shared data (via middleware)
- Props for parent-child communication

---

## Custom Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan stories:cleanup` | Delete expired stories |
| `php artisan users:delete-unverified` | Delete unverified users |
| `php artisan posts:generate-slugs` | Generate slugs for posts |
| `php artisan mail:test` | Send test email |

---

## Configuration

Key configuration files in `config/`:

| File | Purpose |
|------|---------|
| `app.php` | App name, timezone, locale |
| `auth.php` | Authentication guards |
| `database.php` | Database connections |
| `services.php` | Third-party services (Google OAuth) |
| `sanctum.php` | API token settings |
| `mail.php` | Mail configuration |
| `session.php` | Session settings |
| `cache.php` | Cache configuration |

---

## Troubleshooting

### Common Issues

**1. Storage link not working**
```bash
php artisan storage:link
```

**2. Permission denied for storage**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**3. Queue not processing**
```bash
php artisan queue:work
```

**4. Cache issues**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**5. Vite manifest not found**
```bash
npm run build
```

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit changes (`git commit -am 'Add new feature'`)
4. Push to branch (`git push origin feature/new-feature`)
5. Create a Pull Request

---

## License

This project is proprietary software. All rights reserved.

---

## Support

For issues and questions:
- Check existing documentation
- Review error logs in `storage/logs/`
- Contact the development team

---

## Credits

- Laravel Framework
- Vue.js
- Inertia.js
- TailwindCSS
- Vite
- All contributors

---

**Version**: 1.0.0  
**Last Updated**: March 2026
