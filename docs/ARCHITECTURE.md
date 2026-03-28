# Nexus - Architecture Guide

Complete system architecture documentation for Nexus social networking platform.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture Diagram](#architecture-diagram)
3. [Application Flow](#application-flow)
4. [Directory Structure](#directory-structure)
5. [Design Patterns](#design-patterns)
6. [Data Flow](#data-flow)
7. [Security Architecture](#security-architecture)
8. [Performance Architecture](#performance-architecture)

---

## System Overview

### High-Level Architecture

Nexus is built using a modern three-tier architecture with Laravel 12 as the backend framework, Blade templates with Vue.js for the frontend, and SQLite/MySQL for data storage.

```
┌─────────────────────────────────────────────────────────────────┐
│                              CLIENT LAYER                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                  │
│  │   Desktop    │  │   Mobile     │  │   Third-     │                  │
│  │   Browser    │  │   Browser    │  │   Party API  │                  │
│  │   (Blade +   │  │   (Blade +   │  │   Clients    │                  │
│  │    Vue.js)   │  │    Vue.js)   │  │   (REST)     │                  │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘                  │
│         │                 │                 │                           │
│         │  HTTP/HTTPS     │  REST API       │  Sanctum Token            │
│         │  Inertia.js     │  Sanctum Token  │  OAuth 2.0                │
│         ▼                 ▼                 ▼                           │
└─────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                          APPLICATION LAYER                               │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │                     Laravel 12 Framework                            │  │
│  │                                                                   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐   │  │
│  │  │   Routes    │  │ Middleware  │  │    Controllers          │   │  │
│  │  │  web.php    │  │ • Auth      │  │ • PostController        │   │  │
│  │  │  api.php    │  │ • Admin     │  │ • CommentController     │   │  │
│  │  │             │  │ • Verified  │  │ • StoryController       │   │  │
│  │  │             │  │ • Suspended │  │ • ChatController        │   │  │
│  │  │             │  │ • RateLimit │  │ • GroupController       │   │  │
│  │  │             │  │ • CSRF      │  │ • UserController        │   │  │
│  │  └──────┬──────┘  └──────┬──────┘  └───────────┬─────────────┘   │  │
│  │         │                │                     │                  │  │
│  │         └────────────────┼─────────────────────┘                  │  │
│  │                          │                                        │  │
│  │                          ▼                                        │  │
│  │  ┌─────────────────────────────────────────────────────────────┐  │  │
│  │  │                    Service Layer                             │  │  │
│  │  │  • MentionService    • PushNotificationService              │  │  │
│  │  │  • FileUploadService • RealtimeService                      │  │  │
│  │  │  • HashtagService    • ActivityService                      │  │  │
│  │  │  • EventService      • QrCodeService                        │  │  │
│  │  │  • JsObfuscator                                             │  │  │
│  │  └────────────────────────────┬────────────────────────────────┘  │  │
│  │                               │                                   │  │
│  │                               ▼                                   │  │
│  │  ┌─────────────────────────────────────────────────────────────┐  │  │
│  │  │                   Model Layer (Eloquent ORM)                │  │  │
│  │  │  User • Post • Comment • Story • Message • Group • etc.     │  │  │
│  │  └────────────────────────────┬────────────────────────────────┘  │  │
│  └───────────────────────────────┼───────────────────────────────────┘  │
└──────────────────────────────────┼──────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────┐
│                            DATA LAYER                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌────────────┐  │
│  │   MySQL/     │  │  Database/   │  │    File      │  │  Session   │  │
│  │   SQLite     │  │    Redis     │  │   Storage    │  │   Store    │  │
│  │   Database   │  │  (Optional)  │  │  • Avatars   │  │  (Database)│  │
│  │  • Users     │  │  • Cache     │  │  • Posts     │  │  • Cache   │  │
│  │  • Posts     │  │  • Queue     │  │  • Stories   │  │            │  │
│  │  • Comments  │  │  • Sessions  │  │  • Messages  │  │            │  │
│  │  • Messages  │  │  • RateLimit │  │  • Groups    │  │            │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  └────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Architecture Diagram

### Component Architecture

```
                              ┌─────────────────┐
                              │   Load Balancer │
                              │   (Nginx/Apache)│
                              └────────┬────────┘
                                       │
                                       ▼
                              ┌─────────────────┐
                              │   Laravel App   │
                              │   (PHP 8.2+)    │
                              └────────┬────────┘
                                       │
              ┌────────────────────────┼────────────────────────┐
              │                        │                        │
              ▼                        ▼                        ▼
     ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
     │   HTTP Request  │     │  Queue Worker   │     │  Polling        │
     │   Handler       │     │  (Jobs/Events)  │     │  (Real-time)    │
     └────────┬────────┘     └────────┬────────┘     └────────┬────────┘
              │                       │                       │
              │                       ▼                       │
              │              ┌─────────────────┐              │
              │              │  Database Queue │              │
              │              │  • Email Jobs   │              │
              │              │  • Notifications│              │
              │              └─────────────────┘              │
              │                                               │
              ▼                                               ▼
     ┌─────────────────────────────────────────────────────────────────┐
     │                     Controller Layer                             │
     │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
     │  │   Post   │ │  Comment │ │  Story   │ │   Chat   │           │
     │  │Controller│ │Controller│ │Controller│ │Controller│           │
     │  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘           │
     │       │            │            │            │                   │
     │       └────────────┴────────────┴────────────┘                   │
     │                          │                                       │
     │                          ▼                                       │
     │                  ┌─────────────────┐                             │
     │                  │  Service Layer  │                             │
     │                  │  (Business Logic)                             │
     │                  └────────┬────────┘                             │
     │                           │                                      │
     │                           ▼                                      │
     │                  ┌─────────────────┐                             │
     │                  │  Repository     │                             │
     │                  │  (Eloquent ORM) │                             │
     │                  └────────┬────────┘                             │
     └───────────────────────────┼──────────────────────────────────────┘
                                 │
                                 ▼
     ┌─────────────────────────────────────────────────────────────────┐
     │                      Data Access Layer                           │
     │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
     │  │  MySQL   │ │  Redis   │ │   File   │ │ Session  │           │
     │  │          │ │  Cache   │ │  Storage │ │  Store   │           │
     │  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
     └─────────────────────────────────────────────────────────────────┘
```

---

## Application Flow

### Request Lifecycle

```
┌────────────────────────────────────────────────────────────────────────┐
│                        Request Lifecycle                                │
└────────────────────────────────────────────────────────────────────────┘

1. User Request
       │
       ▼
2. public/index.php (Entry Point)
       │
       ▼
3. Autoloader Initialization
       │
       ▼
4. Application Bootstrap (bootstrap/app.php)
       │
       ├── Load Configuration
       ├── Register Service Providers
       └── Create Application Container
       │
       ▼
5. Middleware Pipeline (bootstrap/app.php)
       │
       ├── Global Middleware
       │   • HandleCors
       │   • ValidateCsrfToken
       │   • HandleInertiaRequests
       │   • TrustCloudflare
       │   • SetLocale
       │   • LogRealTimeRequests
       │   • ForceHttps
       │
       ▼
6. Route Matching
       │
       ├── Check Route Definition
       ├── Apply Route Middleware
       │   • auth
       │   • verified
       │   • admin
       │   • suspended
       │   • password.set
       │   • throttle (rate limiting)
       │
       ▼
7. Controller Execution
       │
       ├── Request Validation
       ├── Business Logic (Services)
       ├── Database Operations (Models)
       │
       ▼
8. Response Generation
       │
       ├── Inertia Response (Vue.js)
       ├── JSON Response (API)
       └── View Response (Blade)
       │
       ▼
9. Response Middleware
       │
       ▼
10. Send Response to Browser
```

### Authentication Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        Authentication Flow                               │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Visits     │
└──────┬───────┘
       │
       ▼
┌──────────────┐     ┌──────────────┐
│   Landing    │────▶│   Login      │
│   Page       │     │   Page       │
└──────────────┘     └──────┬───────┘
                            │
                            ▼
                    ┌──────────────┐
                    │   Submit     │
                    │   Credentials│
                    └──────┬───────┘
                           │
                           ▼
                   ┌───────────────────┐
                   │  LoginController  │
                   │  • Validate       │
                   │  • Check Suspended│
                   │  • Create Session │
                   └─────────┬─────────┘
                             │
                             ▼
                   ┌───────────────────┐
                   │  Email Verified?  │
                   └─────────┬─────────┘
                             │
              ┌──────────────┴──────────────┐
              │ NO                          │ YES
              ▼                             ▼
     ┌─────────────────┐           ┌─────────────────┐
     │  Send Verify    │           │  Check Password │
     │  Code Email     │           │  (OAuth users)  │
     └────────┬────────┘           └────────┬────────┘
              │                             │
              │                    ┌───────┴───────┐
              │                    │ NO            │ YES
              │                    ▼               ▼
              │           ┌─────────────┐  ┌─────────────┐
              │           │ Set Password│  │  Redirect   │
              │           │   Page      │  │  to Home    │
              │           └─────────────┘  └─────────────┘
              │
              ▼
     ┌─────────────────┐
     │  Verify Code    │
     │  Input Page     │
     └────────┬────────┘
              │
              ▼
     ┌─────────────────┐
     │  VerifyCodeMail │
     │  • Generate 6-digit
     │  • Send Email   │
     └────────┬────────┘
              │
              ▼
     ┌─────────────────┐
     │  Code Valid?    │
     └────────┬────────┘
              │
     ┌────────┴────────┐
     │ NO              │ YES
     ▼                 ▼
┌─────────┐       ┌─────────────┐
│  Error  │       │  Set Password│
│  Retry  │       │  (if OAuth) │
└─────────┘       └──────┬──────┘
                         │
                         ▼
                  ┌─────────────┐
                  │   Redirect  │
                  │   to Home   │
                  └─────────────┘
```

---

## Directory Structure

```
nexus/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ActivityService.php
│   │       ├── BackfillIpLocations.php
│   │       ├── CleanupExpiredStories.php
│   │       ├── DeleteExpiredStories.php
│   │       ├── DeleteUnverifiedUsers.php
│   │       ├── ExtractHashtags.php
│   │       ├── GeneratePostSlugs.php
│   │       ├── GenerateVapidKeysCommand.php
│   │       ├── SendBirthdayReminders.php
│   │       ├── SendInactiveUserReminders.php
│   │       ├── SendTestEmail.php
│   │       └── Troubleshoot.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── CommentController.php
│   │   │   │   ├── EventController.php
│   │   │   │   ├── HashtagApiController.php
│   │   │   │   ├── MessageController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PostController.php
│   │   │   │   ├── UserController.php
│   │   │   │   └── UserMentionApiController.php
│   │   │   │
│   │   │   ├── Auth/
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── ResetPasswordController.php
│   │   │   │   ├── SocialAuthController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   │
│   │   │   ├── ActivityController.php
│   │   │   ├── AdminController.php
│   │   │   ├── AiController.php
│   │   │   ├── ChatController.php
│   │   │   ├── CommentController.php
│   │   │   ├── Controller.php
│   │   │   ├── EventController.php
│   │   │   ├── GroupController.php
│   │   │   ├── HashtagController.php
│   │   │   ├── LanguageController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── PostController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── PushNotificationController.php
│   │   │   ├── ReportController.php
│   │   │   ├── StoryController.php
│   │   │   └── UserController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   ├── CheckEmailVerified.php
│   │   │   ├── CheckUserSuspended.php
│   │   │   ├── ForceHttps.php
│   │   │   ├── HandleInertiaRequests.php
│   │   │   ├── LogRealTimeRequests.php
│   │   │   ├── RequirePasswordSet.php
│   │   │   ├── SetLocale.php
│   │   │   └── TrustCloudflare.php
│   │   │
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   └── LoginRequest.php
│   │   │   └── ProfileUpdateRequest.php
│   │   │
│   │   └── (No Kernel.php - Laravel 12 uses bootstrap/app.php)
│   │
│   ├── Jobs/
│   │   ├── LogActivityJob.php
│   │   └── SendLoginEmailJob.php
│   │
│   ├── Listeners/
│   │   └── LogUserLogout.php
│   │
│   ├── Mail/
│   │   ├── LoginSecurityAlert.php
│   │   ├── VerificationCodeMail.php
│   │   └── WelcomeMail.php
│   │
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── Block.php
│   │   ├── Comment.php
│   │   ├── CommentLike.php
│   │   ├── Conversation.php
│   │   ├── Event.php
│   │   ├── EventReaction.php
│   │   ├── Follow.php
│   │   ├── Group.php
│   │   ├── GroupMember.php
│   │   ├── Hashtag.php
│   │   ├── Like.php
│   │   ├── Mention.php
│   │   ├── Message.php
│   │   ├── Notification.php
│   │   ├── Post.php
│   │   ├── PostMedia.php
│   │   ├── PostReport.php
│   │   ├── Profile.php
│   │   ├── PushSubscription.php
│   │   ├── SavedPost.php
│   │   ├── Story.php
│   │   ├── StoryReaction.php
│   │   ├── StoryView.php
│   │   └── User.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── ObfuscatorServiceProvider.php
│   │
│   ├── Services/
│   │   ├── ActivityService.php
│   │   ├── EventService.php
│   │   ├── FileUploadService.php
│   │   ├── HashtagService.php
│   │   ├── JsObfuscator.php
│   │   ├── MentionService.php
│   │   ├── PushNotificationService.php
│   │   ├── QrCodeService.php
│   │   └── RealtimeService.php
│   │
│   └── Traits/
│       └── SendsPushNotifications.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
│
├── database/
│   ├── factories/
│   │   ├── PostFactory.php
│   │   └── UserFactory.php
│   │
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 2025_12_31_183416_create_posts_table.php
│   │   ├── ... (79 migration files)
│   │   └── 2026_03_27_081337_add_metadata_column_to_stories_table.php
│   │
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/
│   ├── css/
│   │   ├── app-layout.css
│   │   ├── comments.css
│   │   ├── mobile-header.css
│   │   └── ... (37 CSS files)
│   │
│   ├── images/
│   │   └── default-avatar.svg
│   │
│   ├── .htaccess
│   ├── favicon.ico
│   ├── index.php
│   ├── robots.txt
│   ├── sw.js
│   └── vid.mp4
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   ├── Components/
│   │   │   ├── ApplicationLogo.vue
│   │   │   ├── Checkbox.vue
│   │   │   ├── DangerButton.vue
│   │   │   ├── Dropdown.vue
│   │   │   ├── DropdownLink.vue
│   │   │   ├── InputError.vue
│   │   │   ├── InputLabel.vue
│   │   │   ├── Modal.vue
│   │   │   ├── NavLink.vue
│   │   │   ├── PrimaryButton.vue
│   │   │   ├── ResponsiveNavLink.vue
│   │   │   ├── SecondaryButton.vue
│   │   │   └── TextInput.vue
│   │   │
│   │   ├── Layouts/
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   └── GuestLayout.vue
│   │   │
│   │   ├── Pages/
│   │   │   ├── Auth/
│   │   │   ├── Profile/
│   │   │   ├── Dashboard.vue
│   │   │   └── Welcome.vue
│   │   │
│   │   ├── legacy/
│   │   │   ├── ai-chat.js
│   │   │   ├── auth-*.js
│   │   │   ├── comments.js
│   │   │   ├── groups-edit.js
│   │   │   ├── groups-show.js
│   │   │   ├── home.js
│   │   │   ├── posts.js
│   │   │   ├── realtime.js
│   │   │   └── ui-utils.js
│   │   │
│   │   ├── types/
│   │   │   └── global.d.ts
│   │   │
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── push-notifications.js
│   │
│   ├── lang/
│   │   ├── en/
│   │   │   ├── messages.php
│   │   │   └── validation.php
│   │   └── ar/
│   │       ├── messages.php
│   │       └── validation.php
│   │
│   └── views/
│       ├── activity/
│       ├── admin/
│       ├── ai/
│       ├── auth/
│       ├── chat/
│       ├── emails/
│       ├── errors/
│       ├── events/
│       ├── groups/
│       ├── hashtags/
│       ├── layouts/
│       ├── notifications/
│       ├── partials/
│       ├── posts/
│       ├── reports/
│       ├── stories/
│       ├── users/
│       ├── app.blade.php
│       └── home.blade.php
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
│
├── storage/
│   ├── app/
│   │   └── public/
│   │       ├── posts/
│   │       ├── stories/
│   │       ├── avatars/
│   │       ├── covers/
│   │       └── messages/
│   │
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   │
│   └── logs/
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
├── tailwind.config.js
└── README.md
```

---

## Design Patterns

### MVC (Model-View-Controller)

```
┌─────────────────────────────────────────┐
│              MVC Pattern                 │
├─────────────────────────────────────────┤
│                                         │
│  ┌──────────┐     ┌──────────┐         │
│  │  Model   │◀───▶│Controller│         │
│  │  (Data)  │     │ (Logic)  │         │
│  └──────────┘     └────┬─────┘         │
│                        │                │
│                        ▼                │
│                  ┌──────────┐           │
│                  │   View   │           │
│                  │ (Blade)  │           │
│                  └──────────┘           │
│                                         │
└─────────────────────────────────────────┘
```

### Service Layer Pattern

```php
// Controllers delegate business logic to services
class PostController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'content' => 'nullable|string|max:280',
            'media' => 'nullable|array|max:30',
            'is_private' => 'boolean'
        ]);

        // Create post
        $post = Post::create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? false,
            'slug' => Str::random(24)
        ]);

        // Process media files
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                // Upload and create PostMedia records
            }
        }

        // Process mentions
        app(MentionService::class)->processMentions($post, $validated['content']);

        // Process hashtags
        app(HashtagService::class)->extractHashtags($post);

        return redirect()->back();
    }
}
```

### Repository Pattern (via Eloquent)

```php
// Models act as repositories
class PostRepository
{
    public function __construct(protected Post $model)
    {
    }
    
    public function getFeedForUser(User $user)
    {
        return $this->model->with(['user', 'media'])
            ->whereHas('user', function ($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('is_private', false);
            })
            ->latest()
            ->paginate(15);
    }
}
```

### Observer Pattern (Model Events)

> **Note**: Nexus uses direct service calls in controllers rather than model observers.

```php
// Services are called directly from controllers
class PostController extends Controller
{
    public function store(Request $request)
    {
        // ... validation and post creation

        // Process mentions directly
        app(MentionService::class)->processMentions($post, $content);

        // Process hashtags directly
        app(HashtagService::class)->extractHashtags($post);
    }
}
```

### Strategy Pattern (Authentication)

> **Note**: Nexus uses Laravel's built-in authentication with Socialite for OAuth.

```php
// LoginController handles email/password authentication
class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // Authentication successful
        }
    }
}

// SocialAuthController handles Google OAuth
class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();
        // Find or create user, then authenticate
    }
}
```

---

## Data Flow

### Read Operations (Feed Loading)

```
User Request
     │
     ▼
Browser → HTTP GET /
     │
     ▼
Laravel Router → routes/web.php
     │
     ▼
Middleware Stack (auth, verified, suspended)
     │
     ▼
PostController@index
     │
     ▼
Build Query:
- Include: own posts, public accounts, followed users
- Exclude: blocked users, unfollowed private accounts
     │
     ▼
Eloquent Query with Eager Loading:
Post::with(['user.profile', 'media', 'likes', 'comments.user.profile'])
     │
     ▼
Database Query
     │
     ▼
Results → Collection
     │
     ▼
Blade View Rendering
     │
     ▼
HTML Response → Browser
```

### Write Operations (Post Creation)

```
User Submit Form
     │
     ▼
Browser → POST /posts (multipart/form-data)
     │
     ▼
Laravel Router → routes/web.php
     │
     ▼
Middleware Stack (auth, verified, csrf)
     │
     ▼
PostController@store
     │
     ▼
Validation:
- content: max 280 chars
- media: max 30 files, 50MB each
- MIME type check
     │
     ▼
Create Post Record:
- Generate unique slug (24 chars)
- Set user_id, content, is_private
     │
     ▼
Process Media:
- Upload each file
- Create PostMedia records
- Generate video thumbnails (FFmpeg)
     │
     ▼
Process Mentions:
- Parse @username from content
- Find mentioned users
- Create Mention records
- Create Notifications
     │
     ▼
Process Hashtags:
- Extract #hashtags
- Create/link Hashtag records
     │
     ▼
Redirect to Post → Success Message
```

---

## Security Architecture

### Multi-Layer Security Model

```
┌─────────────────────────────────────────────────────────────────┐
│                     Nexus Security Layers                        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  Layer 1: Network Security                                       │
│  • HTTPS enforcement (production)                               │
│  • Cloudflare Tunnel (optional)                                 │
│  • Firewall rules                                               │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Layer 2: Application Security                                   │
│  • Middleware stack (Auth, Admin, Verified)                     │
│  • Rate limiting                                                │
│  • CSRF protection                                              │
│  • Session management                                           │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Layer 3: Data Security                                          │
│  • Input validation                                             │
│  • SQL injection prevention (Eloquent ORM)                      │
│  • XSS prevention (Blade escaping)                              │
│  • File upload validation                                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Layer 4: Business Logic Security                                │
│  • Authorization checks                                         │
│  • Privacy controls                                             │
│  • Account suspension                                           │
│  • User blocking                                                │
└─────────────────────────────────────────────────────────────────┘
```

---

## Performance Architecture

### Caching Strategy

```
┌─────────────────────────────────────────────────────────────────┐
│                    Caching Architecture                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │  Config     │  │   Route     │  │    View     │             │
│  │   Cache     │  │   Cache     │  │   Cache     │             │
│  │             │  │             │  │             │             │
│  │ php artisan │  │ php artisan │  │ php artisan │             │
│  │ config:cache│  │ route:cache │  │ view:cache  │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Query     │  │   Object    │  │    Page     │             │
│  │   Cache     │  │   Cache     │  │   Cache     │             │
│  │ (Database)  │  │ (Redis/DB)  │  │ (Blade)     │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Database Optimization

```
┌─────────────────────────────────────────────────────────────────┐
│                  Database Optimization                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Indexes:                                                        │
│  • Foreign keys (user_id, post_id, etc.)                       │
│  • Timestamps (created_at, updated_at)                         │
│  • Unique fields (username, email, slug)                       │
│  • Composite indexes (user_id + created_at)                    │
│                                                                  │
│  Query Optimization:                                             │
│  • Eager loading (with())                                       │
│  • Select only needed columns                                   │
│  • Use whereHas instead of joins                                │
│  • Paginate large result sets                                   │
│                                                                  │
│  Connection Pooling:                                             │
│  • Persistent connections                                       │
│  • Connection reuse                                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Frontend Optimization

```
┌─────────────────────────────────────────────────────────────────┐
│                 Frontend Optimization                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Build Optimization:                                             │
│  • Vite bundling                                                │
│  • Code splitting                                               │
│  • Tree shaking                                                 │
│  • Minification (Terser/Uglify)                                 │
│  • Obfuscation (javascript-obfuscator)                         │
│                                                                  │
│  Runtime Optimization:                                             │
│  • Lazy loading images                                          │
│  • Debounced scroll handlers                                    │
│  • Conditional polling (Page Visibility API)                    │
│  • Event delegation                                             │
│                                                                  │
│  CSS Optimization:                                               │
│  • Tailwind PurgeCSS                                            │
│  • Critical CSS extraction                                      │
│  • CSS minification                                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

<div align="center">

**Nexus - Architecture Guide**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
