# Technologies Used in Nexus

Accurate documentation of all technologies, libraries, and tools actually used in this project.

---

## Table of Contents

- [Backend Technologies](#backend-technologies)
- [Frontend Technologies](#frontend-technologies)
- [Development Tools](#development-tools)
- [Third-Party Services](#third-party-services)
- [Architecture Patterns](#architecture-patterns)

---

## Backend Technologies

### Core Framework

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 12.x | Web application framework |
| **PHP** | 8.2+ | Server-side scripting language |
| **SQLite** | Latest | Default database (development) |
| **MySQL** | 8.0+ | Production database (optional) |

### Official Laravel Packages

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^12.0 | Core framework |
| `laravel/sanctum` | ^4.0 | API authentication |
| `laravel/socialite` | ^5.24 | OAuth authentication (Google) |
| `laravel/tinker` | ^2.10.1 | REPL for database interaction |
| `laravel/breeze` | ^2.3 | Authentication scaffolding |
| `laravel/pail` | ^1.2.2 | Log monitoring |
| `laravel/pint` | ^1.24 | PHP code formatter |
| `laravel/sail` | ^1.41 | Docker development environment |

### Third-Party PHP Packages

| Package | Version | Purpose |
|---------|---------|---------|
| `inertiajs/inertia-laravel` | ^2.0 | Server-driven SPA routing (available but not actively used) |
| `intervention/image` | ^3.11 | Image processing and manipulation |
| `tightenco/ziggy` | ^2.0 | Laravel route access in JavaScript |
| `fakerphp/faker` | ^1.23 | Fake data generation for testing |
| `pestphp/pest` | Latest | PHP testing framework |
| `pestphp/pest-plugin-laravel` | Latest | Pest integration with Laravel |
| `nunomaduro/collision` | ^8.6 | Error handler for CLI |
| `mockery/mockery` | ^1.6 | Mocking framework for tests |

### Backend Features Used

```
┌─────────────────────────────────────────────────────────────────┐
│                    Backend Architecture                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │  Routes     │  │ Controllers │  │   Models    │             │
│  │  (web.php)  │  │  (31 files) │  │  (19 total) │             │
│  │             │  │  (30 + 1 base)│ │             │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │ Middleware  │  │   Mail      │  │  Services   │             │
│  │  (8 total)  │  │  (1 class)  │  │ (4 total)   │             │
│  │             │  │             │  │             │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐                               │
│  │  Commands   │  │   Providers │                               │
│  │  (6 total)  │  │  (2 total)  │                               │
│  └─────────────┘  └─────────────┘                               │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Controllers (32 Files: 12 Main + 6 API + 13 Auth + 1 Base Class)

> **Note:** `Controller.php` is the base class extended by all other controllers.

**Main Controllers (12 + 1 base class):**
| Controller | Purpose |
|------------|---------|
| `PostController` | Post CRUD operations |
| `CommentController` | Comment management |
| `StoryController` | Story creation, viewing, reactions |
| `ChatController` | Real-time messaging |
| `UserController` | User profiles, follow system |
| `GroupController` | Group management |
| `NotificationController` | Notification handling |
| `AdminController` | Admin panel operations |
| `AiController` | AI chatbot |
| `LanguageController` | Language switching (EN/AR) |
| `ProfileController` | Profile management |
| `PushNotificationController` | Web push notifications |
| `NotificationController` | User notifications |

**API Controllers (6):**
| Controller | Purpose |
|------------|---------|
| `Api/PostController` | API post operations |
| `Api/CommentController` | API comment operations |
| `Api/MessageController` | API message operations |
| `Api/NotificationController` | API notifications |
| `Api/UserController` | API user operations |
| `Api/PasswordController` | API password changes |

**Auth Controllers (13):**
| Controller | Purpose |
|------------|---------|
| `Auth/LoginController` | Login handling |
| `Auth/RegisterController` | Registration |
| `Auth/PasswordResetLinkController` | Reset link requests |
| `Auth/ResetPasswordController` | Password reset |
| `Auth/SocialAuthController` | Google OAuth |
| `Auth/AuthenticatedSessionController` | Session management |
| `Auth/ConfirmablePasswordController` | Password confirmation |
| `Auth/EmailVerificationNotificationController` | Verification emails |
| `Auth/EmailVerificationPromptController` | Verification prompt |
| `Auth/NewPasswordController` | New password setup |
| `Auth/RegisteredUserController` | User registration |
| `Auth/VerifyEmailController` | Email verification |
| `Auth/PasswordController` | Password changes |

> **Note:** Like and Follow functionality is handled by `PostController` and `UserController` respectively. File uploads are handled by `FileUploadService`.

### Models (20 Total)

| Model | Purpose |
|-------|---------|
| `User` | User accounts |
| `Profile` | Extended user profiles |
| `Post` | User posts |
| `PostMedia` | Post media attachments |
| `Comment` | Post comments |
| `CommentLike` | Comment likes |
| `Like` | Post likes |
| `Follow` | User follow relationships |
| `Block` | User blocks |
| `SavedPost` | Bookmarked posts |
| `Story` | Ephemeral stories |
| `StoryView` | Story view tracking |
| `StoryReaction` | Story reactions |
| `Conversation` | Chat conversations |
| `Message` | Chat messages |
| `Group` | User groups |
| `GroupMember` | Group membership |
| `Notification` | User notifications |
| `Mention` | User mentions |
| `PushSubscription` | Web push notification subscriptions |

### Mail (1 Class)

| Class | Purpose |
|-------|---------|
| `VerificationCodeMail` | Email verification code delivery |

### Services (5 Total)

| Service | Purpose |
|---------|---------|
| `MentionService` | Process @mentions in posts/comments |
| `RealtimeService` | Real-time polling for chat/notifications |
| `FileUploadService` | Handle file uploads |
| `JsObfuscator` | JavaScript code obfuscation |
| `PushNotificationService` | Web push notification delivery |

### Commands (7 Total)

| Command | Purpose |
|---------|---------|
| `CleanupExpiredStories` | Clean up expired stories |
| `DeleteExpiredStories` | Alternative story cleanup |
| `DeleteUnverifiedUsers` | Remove unverified accounts |
| `GeneratePostSlugs` | Generate slugs for posts |
| `SendInactiveUserReminders` | Re-engagement emails |
| `SendTestEmail` | Test email configuration |
| `GenerateVapidKeysCommand` | Generate VAPID keys for push notifications |

### Middleware (8 Total)

| Middleware | Purpose |
|------------|---------|
| `AdminMiddleware` | Admin authorization |
| `CheckEmailVerified` | Email verification check |
| `CheckUserSuspended` | Account suspension check |
| `ForceHttps` | HTTPS enforcement |
| `HandleInertiaRequests` | Inertia setup |
| `LogRealTimeRequests` | Real-time request logging |
| `RequirePasswordSet` | Password requirement check |
| `SetLocale` | Language/locale setting |

---

## Frontend Technologies

### Core Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | ^3.4.0 | JavaScript framework (available for components) |
| **Vite** | ^6.4.1 | Frontend build tool |
| **Tailwind CSS** | ^3.2.1 | Utility-first CSS framework |
| **Alpine.js** | Embedded | Lightweight JavaScript for interactivity |
| **Axios** | ^1.11.0 | HTTP client for AJAX requests |
| **motion-v** | ^2.0.0 | Vue animation/motion library |

### Build & Development

| Package | Version | Purpose |
|---------|---------|---------|
| `@vitejs/plugin-vue` | ^5.0.0 | Vue 3 support in Vite |
| `laravel-vite-plugin` | ^2.0.0 | Laravel integration with Vite |
| `@tailwindcss/forms` | ^0.5.3 | Tailwind form plugin |
| `@tailwindcss/vite` | ^4.0.0 | Tailwind Vite plugin |
| `autoprefixer` | ^10.4.12 | CSS vendor prefixing |
| `postcss` | ^8.4.31 | CSS processing |

### Code Quality

| Package | Version | Purpose |
|---------|---------|---------|
| `eslint` | ^8.57.0 | JavaScript linting |
| `eslint-plugin-vue` | ^9.23.0 | Vue ESLint plugin |
| `prettier` | ^3.3.0 | Code formatting |
| `prettier-plugin-tailwindcss` | ^0.6.5 | Tailwind class sorting |
| `typescript` | ^5.6.3 | TypeScript support |
| `vue-tsc` | ^2.0.24 | Vue TypeScript checker |

### JavaScript Obfuscation (Custom Scripts)

| Package | Version | Purpose |
|---------|---------|---------|
| `javascript-obfuscator` | ^5.3.0 | Code obfuscation |
| `terser` | ^5.46.0 | JavaScript minification |
| `uglify-js` | ^3.19.3 | Alternative minification |

### Frontend Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                   Frontend Architecture                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Primary: Blade Templates + Vanilla JavaScript                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/views/                                        │   │
│  │  ├── layouts/app.blade.php (Main layout)                │   │
│  │  ├── home.blade.php (Landing page)                      │   │
│  │  ├── posts/                                             │   │
│  │  ├── stories/                                           │   │
│  │  ├── chat/                                              │   │
│  │  ├── groups/                                            │   │
│  │  ├── users/                                             │   │
│  │  ├── admin/                                             │   │
│  │  ├── ai/                                                │   │
│  │  ├── auth/                                              │   │
│  │  ├── emails/                                            │   │
│  │  ├── errors/                                            │   │
│  │  ├── layouts/                                           │   │
│  │  ├── notifications/                                     │   │
│  │  └── partials/                                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  JavaScript: Legacy Module Pattern                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/js/legacy/                                    │   │
│  │  ├── ui-utils.js (UI helpers, theme toggle)             │   │
│  │  ├── realtime.js (Chat polling, notifications)          │   │
│  │  ├── posts.js (Post interactions)                       │   │
│  │  ├── comments.js (Comment system)                       │   │
│  │  ├── home.js (Feed functionality)                       │   │
│  │  ├── groups-show.js (Group page)                        │   │
│  │  ├── groups-edit.js (Group editing)                     │   │
│  │  ├── ai-chat.js (AI chatbot)                            │   │
│  │  └── auth-*.js (Authentication pages - 8 files)         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Vue.js Components (13 components)                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  resources/js/Components/                                │   │
│  │  └── (Reusable UI components)                           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### CSS Architecture

| File | Purpose |
|------|---------|
| `resources/css/app.css` | Tailwind CSS entry point |
| `public/css/app-layout.css` | Main layout styles |
| `public/css/comments.css` | Comment styling |
| `public/css/mobile-header.css` | Mobile responsive header |

---

## Development Tools

### Testing

| Tool | Purpose |
|------|---------|
| **Pest PHP** | Primary testing framework |
| **PHPUnit** | Unit testing (via Pest) |
| **Mockery** | Mocking objects |
| **Faker** | Test data generation |

### Code Quality

| Tool | Purpose |
|------|---------|
| **Laravel Pint** | PHP code formatting |
| **ESLint** | JavaScript linting |
| **Prettier** | Code formatting |
| **TypeScript** | Type checking |

### Development Scripts

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

### Composer Scripts

```json
{
    "setup": "Full project setup",
    "dev": "Run all development services",
    "test": "Run tests"
}
```

---

## Third-Party Services

### Authentication

| Service | Purpose | Configuration |
|---------|---------|---------------|
| **Google OAuth** | Social login | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` |

### Email

| Service | Purpose |
|---------|---------|
| **SMTP** | Email delivery (configurable) |
| **Mailtrap** | Development email testing (recommended) |

### Optional Services

| Service | Purpose |
|---------|---------|
| **Cloudflare Tunnel** | Public URL sharing for development |
| **VirusTotal API** | Security scanning (configured but optional) |
| **AWS S3** | File storage (optional, for production) |
| **Redis** | Cache/sessions (optional, for production) |

---

## Architecture Patterns

### Backend Patterns

```
┌─────────────────────────────────────────────────────────────────┐
│                    Request Flow                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. HTTP Request → routes/web.php                               │
│  2. Middleware Stack (auth, verified, admin, etc.)             │
│  3. Controller Action                                          │
│  4. Service Layer (optional - MentionService, RealtimeService) │
│  5. Eloquent Model → Database                                  │
│  6. Return Blade View or JSON                                  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Frontend Patterns

```
┌─────────────────────────────────────────────────────────────────┐
│                  Frontend Patterns                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Blade Templates:                                               │
│  • Server-side rendering                                        │
│  • @yield/@section for layouts                                  │
│  • @auth/@guest for conditional rendering                       │
│                                                                  │
│  JavaScript (Legacy Module Pattern):                            │
│  • IIFE (Immediately Invoked Function Expression)               │
│  • Polling-based real-time updates                              │
│  • Event delegation for dynamic elements                        │
│  • AJAX with Axios                                              │
│                                                                  │
│  Real-time Features:                                            │
│  • Polling (not WebSockets)                                     │
│  • Chat: 2-second interval                                      │
│  • Notifications: 3-second interval                             │
│  • Online status: 10-second interval                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Database Patterns

| Pattern | Implementation |
|---------|----------------|
| **Active Record** | Eloquent ORM |
| **Migrations** | Version-controlled schema |
| **Seeders** | Test data population |
| **Factories** | Model generation for tests |
| **Relationships** | HasMany, BelongsTo, BelongsToMany |

---

## What's NOT Used (But Installed)

### Available But Not Actively Used

| Technology | Status |
|------------|--------|
| **Inertia.js** | Installed but project uses Blade templates |
| **Vue.js SPA** | Available for components, but primary UI is Blade |
| **Laravel Reverb** | Not configured (uses polling instead of WebSockets) |
| **Pusher** | Not configured |
| **Redis** | Optional (file/database drivers used by default) |
| **Laravel Sail** | Available but not required (native PHP development) |

---

## System Requirements

### Required

| Software | Version |
|----------|---------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ (LTS) |
| npm | 9+ |
| SQLite | Built-in with PHP |
| Git | Latest |

### Optional (Production)

| Software | Purpose |
|----------|---------|
| MySQL 8.0+ | Production database |
| Redis | Cache/sessions |
| FFmpeg | Video processing (thumbnails, trimming) |
| Apache/Nginx | Production web server |

---

## File Structure Summary

```
laravel_project/
├── Backend (PHP/Laravel)
│   ├── app/Http/Controllers/  (32 files: 12 main + 6 API + 13 Auth + 1 base class)
│   ├── app/Models/            (20 models)
│   ├── app/Http/Middleware/   (8 middleware)
│   ├── app/Mail/              (1 mail class)
│   ├── app/Services/          (5 services)
│   ├── app/Console/Commands/  (7 artisan commands)
│   ├── app/Traits/            (reusable traits)
│   └── routes/web.php         (Main routing)
│
├── Frontend (Hybrid: Blade + Vue 3)
│   ├── resources/views/       (Blade templates - primary UI, 51 files)
│   ├── resources/js/
│   │   ├── Components/        (Vue 3 components, 13 components)
│   │   ├── Pages/             (Inertia pages)
│   │   ├── Composables/       (Vue composables)
│   │   ├── Layouts/           (Vue layouts)
│   │   └── legacy/            (16 JavaScript modules)
│   ├── resources/css/         (Tailwind CSS)
│   └── public/css/            (Compiled CSS)
│
├── Database
│   ├── database/migrations/   (60 migrations)
│   ├── database/factories/    (Model factories)
│   └── database/database.sqlite
│
└── Configuration
    ├── config/                (10 config files)
    ├── .env.example           (Environment template)
    └── vite.config.js         (Build configuration)
```

---

## Next Steps

Continue reading:

- [Architecture](ARCHITECTURE.md) - System design diagrams
- [Features](FEATURES.md) - Feature documentation
- [API Reference](API.md) - RESTful API documentation
- [Database Schema](DATABASE.md) - Table definitions
