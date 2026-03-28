# Nexus - Technologies Documentation

Complete documentation of all technologies, libraries, frameworks, and tools used in the Nexus project.

---

## Table of Contents

1. [Backend Technologies](#1-backend-technologies)
2. [Frontend Technologies](#2-frontend-technologies)
3. [Development Tools](#3-development-tools)
4. [Database & Storage](#4-database--storage)
5. [Build & Deployment](#5-build--deployment)
6. [Third-Party Services](#6-third-party-services)
7. [Architecture Patterns](#7-architecture-patterns)

---

## 1. Backend Technologies

### 1.1 Core Framework

- **Laravel** (12.x): Web application framework - https://laravel.com
- **PHP** (8.2+): Server-side scripting language - https://php.net

**Laravel Components Used:**
- Eloquent ORM (database)
- Blade templating (views)
- Artisan CLI (commands)
- Middleware system
- Service providers
- Facades
- Collections
- Validation
- Authentication
- Authorization
- Mail system
- Notifications

### 1.2 Official Laravel Packages

- `laravel/framework` (^12.0): Core framework
- `laravel/sanctum` (^4.0): API authentication
- `laravel/socialite` (^5.24): OAuth authentication (Google)
- `laravel/tinker` (^2.10.1): REPL for database interaction
- `laravel/breeze` (^2.3): Authentication scaffolding
- `laravel/pail` (^1.2.2): Log monitoring
- `laravel/pint` (^1.24): PHP code formatter
- `laravel/sail` (^1.41): Docker development environment

### 1.3 Third-Party PHP Packages

- `inertiajs/inertia-laravel` (^2.0): Server-driven SPA routing
- `intervention/image` (^3.11): Image processing and manipulation
- `tightenco/ziggy` (^2.0): Laravel route access in JavaScript
- `minishlink/web-push` (^10.0): Web push notifications
- `simplesoftwareio/simple-qrcode` (^4.2): QR code generation

### 1.4 Development Dependencies (PHP)

- `fakerphp/faker` (^1.23): Fake data generation for testing
- `pestphp/pest` (Latest): PHP testing framework
- `pestphp/pest-plugin-laravel` (Latest): Pest integration with Laravel
- `nunomaduro/collision` (^8.6): CLI error handler
- `mockery/mockery` (^1.6): Mocking framework for tests

### 1.5 Backend Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel 12 Application                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Routes    │  │ Middleware  │  │ Controllers │             │
│  │  web.php    │  │  (9 total)  │  │ (39 total)  │             │
│  │  api.php    │  │             │  │             │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │  Services   │  │   Models    │  │    Mail     │             │
│  │  (9 total)  │  │ (25 total)  │  │  (3 class)  │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │  Commands   │  │  Providers  │  │   Traits    │             │
│  │ (11 total)  │  │  (2 total)  │  │  (custom)   │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 1.6 Controllers (39 Total)

**Main Controllers (17):**
- `ActivityController.php` - Activity logs and session management
- `AdminController.php` - Admin panel operations
- `AiController.php` - AI chatbot
- `ChatController.php` - Chat and messaging
- `CommentController.php` - Comment CRUD
- `EventController.php` - Life events
- `GroupController.php` - Group management
- `HashtagController.php` - Hashtag pages
- `LanguageController.php` - Language switching
- `NotificationController.php` - User notifications
- `PostController.php` - Post CRUD
- `ProfileController.php` - Profile management
- `PushNotificationController.php` - Push notifications
- `ReportController.php` - Content reports
- `StoryController.php` - Story operations
- `UserController.php` - User profiles and social

**Auth Controllers (13):**
- `LoginController.php` - Login handling
- `RegisterController.php` - Registration
- `PasswordResetLinkController.php` - Reset link requests
- `ResetPasswordController.php` - Password reset
- `SocialAuthController.php` - Google OAuth
- `PasswordController.php` - Password changes
- `AuthenticatedSessionController.php` - Session management
- `ConfirmablePasswordController.php` - Password confirmation
- `EmailVerificationNotificationController.php` - Verification emails
- `EmailVerificationPromptController.php` - Verification prompt
- `NewPasswordController.php` - New password setup
- `RegisteredUserController.php` - User registration
- `VerifyEmailController.php` - Email verification

**API Controllers (9):**
- `Api/CommentController.php` - API comment operations
- `Api/EventController.php` - API events
- `Api/HashtagApiController.php` - API hashtag suggestions
- `Api/MessageController.php` - API messages
- `Api/NotificationController.php` - API notifications
- `Api/PasswordController.php` - API password changes
- `Api/PostController.php` - API post operations
- `Api/UserController.php` - API user operations
- `Api/UserMentionApiController.php` - API user mentions

### 1.7 Models (25 Total)

- `User`: User accounts (Auth, verification, online status)
- `Profile`: Extended profiles (Avatar, cover, bio, social links)
- `Post`: User posts (Slug URLs, privacy, pinning)
- `PostMedia`: Post attachments (Images, videos, thumbnails)
- `Comment`: Post comments (Nested replies, mentions)
- `CommentLike`: Comment likes (Like/unlike)
- `Like`: Post likes (Toggle likes)
- `Follow`: Follow relationships (Follow/unfollow)
- `Block`: User blocks (Block/unblock)
- `SavedPost`: Bookmarked posts (Save for later)
- `Story`: Ephemeral stories (24-hour expiry)
- `StoryView`: Story views (View tracking)
- `StoryReaction`: Story reactions (Emoji reactions)
- `Conversation`: Chat conversations (Direct and group)
- `Message`: Chat messages (Status tracking)
- `Group`: User groups (Invite links)
- `GroupMember`: Group membership (Roles admin/member)
- `Notification`: User notifications (Multiple types)
- `Mention`: User mentions (@username)
- `Hashtag`: Hashtags (Content discovery)
- `PostReport`: Content reports (Moderation)
- `PushSubscription`: Push notifications (Web push)
- `ActivityLog`: Activity tracking (Audit logs)
- `Event`: Life events (Special occasions)
- `EventReaction`: Event reactions (React to events)

### 1.8 Services (9 Total)

- `ActivityService.php`: Activity logging and analytics
- `EventService.php`: Life events management
- `FileUploadService.php`: File upload handling
- `HashtagService.php`: Hashtag extraction and linking
- `JsObfuscator.php`: JavaScript code obfuscation
- `MentionService.php`: @mention processing
- `PushNotificationService.php`: Web push notification delivery
- `QrCodeService.php`: QR code generation
- `RealtimeService.php`: Real-time polling for chat/notifications

### 1.9 Middleware (9 Total)

- `AdminMiddleware.php`: Admin authorization
- `CheckEmailVerified.php`: Email verification check
- `CheckUserSuspended.php`: Account suspension check
- `ForceHttps.php`: HTTPS enforcement
- `HandleInertiaRequests.php`: Inertia setup
- `LogRealTimeRequests.php`: Real-time request logging
- `RequirePasswordSet.php`: Password requirement check
- `SetLocale.php`: Language/locale setting
- `TrustCloudflare.php`: Trust Cloudflare proxies

### 1.10 Console Commands (11 Total)

- `BackfillIpLocations.php`: Backfill IP location data
- `CleanupExpiredStories.php`: Clean up expired stories (hourly)
- `DeleteExpiredStories.php`: Alternative story cleanup
- `DeleteUnverifiedUsers.php`: Remove unverified accounts
- `ExtractHashtags.php`: Extract hashtags from existing posts
- `GeneratePostSlugs.php`: Generate slugs for posts
- `GenerateVapidKeysCommand.php`: Generate VAPID keys
- `SendBirthdayReminders.php`: Send birthday notifications
- `SendInactiveUserReminders.php`: Re-engagement emails
- `SendTestEmail.php`: Test email configuration
- `Troubleshoot.php`: Troubleshooting command

---

## 2. Frontend Technologies

### 2.1 Core Technologies

- **Vue.js** (^3.4.0): JavaScript framework - https://vuejs.org
- **Vite** (^6.4.1): Frontend build tool - https://vitejs.dev
- **Tailwind CSS** (^3.2.1): Utility-first CSS - https://tailwindcss.com
- **Alpine.js** (Embedded): Lightweight interactivity - https://alpinejs.dev
- **Axios** (^1.11.0): HTTP client - https://axios-http.com

### 2.2 Vue.js Ecosystem

- `vue` (^3.4.0): Core framework
- `@vitejs/plugin-vue` (^5.0.0): Vue 3 Vite plugin
- `vue-tsc` (^2.0.24): Vue TypeScript checker
- `motion-v` (^2.0.0): Vue animation library

### 2.3 Build & Development

- `laravel-vite-plugin` (^2.0.0): Laravel Vite integration
- `@tailwindcss/forms` (^0.5.3): Tailwind form plugin
- `@tailwindcss/vite` (^4.0.0): Tailwind Vite plugin
- `autoprefixer` (^10.4.12): CSS vendor prefixing
- `postcss` (^8.4.31): CSS processing

### 2.4 Code Quality

- `eslint` (^8.57.0): JavaScript linting
- `eslint-plugin-vue` (^9.23.0): Vue ESLint plugin
- `@rushstack/eslint-patch` (Latest): ESLint patches
- `prettier` (^3.3.0): Code formatting
- `prettier-plugin-organize-imports` (^4.0.0): Import organization
- `prettier-plugin-tailwindcss` (^0.6.5): Tailwind class sorting
- `typescript` (^5.6.3): TypeScript support

### 2.5 JavaScript Obfuscation

- `javascript-obfuscator` (^5.3.0): Code obfuscation
- `terser` (^5.46.0): JavaScript minification
- `uglify-js` (^3.19.3): Alternative minification

### 2.6 Additional Libraries

- `wavesurfer.js` (^7.12.5): Audio waveform visualization

### 2.7 Frontend Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Frontend Architecture                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Primary: Blade Templates + Vanilla JavaScript                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/views/ (67 Blade templates)                  │   │
│  │  ├── layouts/app.blade.php (Main layout)                │   │
│  │  ├── home.blade.php (Landing page)                      │   │
│  │  ├── posts/ (Post views)                                │   │
│  │  ├── stories/ (Story views)                             │   │
│  │  ├── chat/ (Chat views)                                 │   │
│  │  ├── groups/ (Group views)                              │   │
│  │  ├── users/ (User views)                                │   │
│  │  ├── admin/ (Admin views)                               │   │
│  │  ├── auth/ (Auth views)                                 │   │
│  │  └── partials/ (Reusable components)                    │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  JavaScript: Legacy Module Pattern (16 modules)                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/js/legacy/                                    │   │
│  │  ├── realtime.js (Chat polling, notifications)          │   │
│  │  ├── posts.js (Post interactions)                       │   │
│  │  ├── comments.js (Comment system)                       │   │
│  │  ├── home.js (Feed functionality)                       │   │
│  │  ├── groups-show.js (Group page)                       │   │
│  │  ├── groups-edit.js (Group editing)                    │   │
│  │  ├── ai-chat.js (AI chatbot)                           │   │
│  │  ├── ui-utils.js (UI helpers, theme toggle)            │   │
│  │  └── auth-*.js (Auth pages - 8 files)                  │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Vue.js Components (27 total: 13 base + 2 layouts + 12 pages)   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/js/Components/                                │   │
│  │  ├── ApplicationLogo.vue                                │   │
│  │  ├── Checkbox.vue                                       │   │
│  │  ├── DangerButton.vue                                   │   │
│  │  ├── Dropdown.vue                                       │   │
│  │  ├── InputError.vue                                     │   │
│  │  ├── InputLabel.vue                                     │   │
│  │  ├── Modal.vue                                          │   │
│  │  ├── NavLink.vue                                        │   │
│  │  ├── PrimaryButton.vue                                  │   │
│  │  ├── ResponsiveNavLink.vue                              │   │
│  │  ├── SecondaryButton.vue                                │   │
│  │  └── TextInput.vue                                      │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  CSS Architecture                                               │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/css/app.css (Tailwind entry)                 │   │
│  │  public/css/app-layout.css                              │   │
│  │  public/css/comments.css                                │   │
│  │  public/css/mobile-header.css                           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 2.8 Vue Components (27 Total: 13 Base + 2 Layouts + 12 Pages)

**Base Components:**
- `ApplicationLogo.vue`
- `Checkbox.vue`
- `DangerButton.vue`
- `Dropdown.vue`
- `DropdownLink.vue`
- `InputError.vue`
- `InputLabel.vue`
- `Modal.vue`
- `NavLink.vue`
- `PrimaryButton.vue`
- `ResponsiveNavLink.vue`
- `SecondaryButton.vue`
- `TextInput.vue`

**Layouts:**
- `Layouts/AuthenticatedLayout.vue`
- `Layouts/GuestLayout.vue`

**Pages:**
- `Pages/Auth/ConfirmPassword.vue`
- `Pages/Auth/ForgotPassword.vue`
- `Pages/Auth/Login.vue`
- `Pages/Auth/Register.vue`
- `Pages/Auth/ResetPassword.vue`
- `Pages/Auth/VerifyEmail.vue`
- `Pages/Dashboard.vue`
- `Pages/Profile/Edit.vue`
- `Pages/Profile/Partials/DeleteUserForm.vue`
- `Pages/Profile/Partials/UpdatePasswordForm.vue`
- `Pages/Profile/Partials/UpdateProfileInformationForm.vue`
- `Pages/Welcome.vue`

---

## 3. Development Tools

### 3.1 Testing

- **Pest PHP**: Primary testing framework
- **PHPUnit**: Unit testing (via Pest)
- **Mockery**: Mocking objects
- **Faker**: Test data generation

### 3.2 Code Quality

- **Laravel Pint**: PHP code formatting
- **ESLint**: JavaScript linting
- **Prettier**: Code formatting
- **TypeScript**: Type checking

### 3.3 Development Scripts

**NPM Scripts:**
```json
{
    "dev": "vite (development server)",
    "build": "vite build + obfuscation",
    "build:terser": "vite build + terser minification",
    "build:uglify": "vite build + uglify minification",
    "build:no-obf": "vite build (no obfuscation)",
    "lint": "eslint resources/js --fix",
    "obfuscate": "node scripts/obfuscate.js",
    "minify:terser": "node scripts/minify-terser.js",
    "minify:uglify": "node scripts/minify-uglify.js"
}
```

**Composer Scripts:**
```json
{
    "setup": "Full project setup",
    "dev": "Run all development services",
    "test": "Run tests"
}
```

---

## 4. Database & Storage

### 4.1 Database

- **SQLite** (Latest): Default database (development)
- **MySQL** (8.0+): Production database (optional)

### 4.2 ORM

- **Eloquent ORM**: Laravel's ActiveRecord implementation

**Eloquent Features Used:**
- Model relationships (HasMany, BelongsTo, BelongsToMany)
- Eager loading
- Query scopes
- Accessors/mutators
- Model events
- Soft deletes
- Timestamps
- Serialization

### 4.3 Migrations

- **79 migration files**: Database schema version control

### 4.4 File Storage

- **Local Storage**: Default file storage
- **Public Disk**: Publicly accessible files
- **Private Disk**: Protected files

**Storage Directories:**
```
storage/app/public/
├── posts/       (Post media)
├── stories/     (Story media)
├── avatars/     (User avatars)
├── covers/      (Cover images)
└── messages/    (Chat media)
```

---

## 5. Build & Deployment

### 5.1 Build Tools

- **Vite** (^6.4.1): Frontend build tool
- **Composer** (2.x): PHP dependency manager
- **npm** (9+): JavaScript package manager

### 5.2 Vite Configuration

**Entry Points:**
```javascript
'resources/js/app.js',
'resources/js/legacy/ui-utils.js',
'resources/js/legacy/posts.js',
'resources/js/legacy/home.js',
'resources/js/legacy/realtime.js',
'resources/js/legacy/comments.js',
'resources/js/legacy/auth-login.js',
'resources/js/legacy/auth-register.js',
'resources/js/legacy/auth-forgot-password.js',
'resources/js/legacy/auth-reset-password.js',
'resources/js/legacy/auth-set-password.js',
'resources/js/legacy/auth-password-change.js',
'resources/js/legacy/auth-suspended.js',
'resources/js/legacy/auth-verify-email.js',
'resources/js/legacy/groups-show.js',
'resources/js/legacy/groups-edit.js',
'resources/js/legacy/ai-chat.js',
```

**Total:** 17 entry points (1 app.js + 16 legacy modules)

### 5.3 Deployment Optimization

**Production Commands:**
```bash
# PHP optimization
composer install --optimize-autoloader --no-dev

# Asset optimization
npm run build

# Laravel caching
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Third-Party Services

### 6.1 Authentication

- **Google OAuth**: Social login (Configuration: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`)

### 6.2 Email

- **SMTP**: Email delivery (configurable)
- **Mailtrap**: Development email testing (recommended)

### 6.3 Optional Services

- **Cloudflare Tunnel**: Public URL sharing for development
- **VirusTotal API**: Security scanning (configured but optional)
- **AWS S3**: File storage (optional, for production)
- **Redis**: Cache/sessions (optional, for production)

---

## 7. Architecture Patterns

### 7.1 Backend Patterns

- **MVC**: Model-View-Controller
- **Active Record**: Eloquent ORM
- **Service Layer**: Business logic in Services
- **Repository**: Eloquent models as repositories
- **Middleware**: Request filtering
- **Dependency Injection**: Constructor injection
- **Service Provider**: Application bootstrap

### 7.2 Frontend Patterns

- **Component-Based**: Vue.js components
- **Module Pattern**: Legacy JavaScript IIFE
- **Server-Side Rendering**: Blade templates
- **Progressive Enhancement**: Alpine.js for interactivity
- **Utility-First CSS**: Tailwind CSS

### 7.3 Database Patterns

- **Relational**: SQL database
- **Foreign Keys**: Referential integrity
- **Indexes**: Query optimization
- **Soft Deletes**: Recoverable deletion
- **Timestamps**: created_at, updated_at

### 7.4 Security Patterns

- **CSRF Protection**: Token validation
- **XSS Prevention**: Blade escaping
- **SQL Injection**: Parameterized queries
- **Rate Limiting**: Request throttling
- **Authentication**: Session-based + Sanctum
- **Authorization**: Middleware + Policies

---

## Technology Stack Summary

### Backend

```
Laravel 12.x
├── PHP 8.2+
├── Eloquent ORM
├── Blade Templates
├── Artisan CLI
├── Middleware
└── Service Providers
```

### Frontend

```
Hybrid Architecture
├── Blade Templates (Primary UI)
├── Vue.js 3.4 (Components)
├── Alpine.js (Interactivity)
├── Tailwind CSS 3.2 (Styling)
└── Axios (HTTP)
```

### Database

```
SQLite/MySQL
├── 24+ Tables
├── 79 Migrations
├── Eloquent Models (25)
└── Foreign Keys
```

### Build & Deploy

```
Vite 6.4
├── Vue Plugin
├── Laravel Plugin
├── TypeScript
├── ESLint
└── Prettier
```

---

## What's NOT Used (But Installed)

### Available But Not Actively Used

- **Inertia.js**: Installed but project primarily uses Blade templates
- **Vue.js SPA**: Available for components, but primary UI is Blade
- **Laravel Reverb**: Not configured (uses polling instead of WebSockets)
- **Pusher**: Not configured
- **Redis**: Optional (file/database drivers used by default)
- **Laravel Sail**: Available but not required (native PHP development)

---

<div align="center">

**Nexus - Technologies Documentation**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
