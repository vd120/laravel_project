	# Architecture Documentation

System architecture, design patterns, and data flow for Nexus.

---

## Table of Contents

- [System Overview](#system-overview)
- [Architecture Diagram](#architecture-diagram)
- [Application Flow](#application-flow)
- [Directory Structure](#directory-structure)
- [Design Patterns](#design-patterns)
- [Data Flow](#data-flow)
- [Security Architecture](#security-architecture)
- [Performance Architecture](#performance-architecture)

---

## System Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              CLIENT LAYER                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                  │
│  │   Desktop    │  │   Mobile     │  │   Third-     │                  │
│  │   Browser    │  │   Browser    │  │   Party API  │                  │
│  │   (Vue.js)   │  │   (Vue.js)   │  │   Clients    │                  │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘                  │
│         │                 │                 │                           │
│         │  HTTP/HTTPS     │  REST API       │  OAuth 2.0                │
│         │  Inertia.js     │  Sanctum Token  │  Socialite                │
│         ▼                 ▼                 ▼                           │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                          APPLICATION LAYER                               │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │                     Laravel 12 Framework                           │  │
│  │                                                                   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐   │  │
│  │  │   Routes    │  │ Middleware  │  │    Controllers          │   │  │
│  │  │   web.php   │  │ • Auth      │  │ • PostController        │   │  │
│  │  │   api.php   │  │ • Admin     │  │ • CommentController     │   │  │
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
┌─────────────────────────────────────────────────────────────────────────┐
│                            DATA LAYER                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌────────────┐  │
│  │   MySQL/     │  │    Redis     │  │    File      │  │  Session   │  │
│  │   SQLite     │  │   (Cache)    │  │   Storage    │  │   Files    │  │
│  │   Database   │  │  • Queue     │  │  • Avatars   │  │  • Cache   │  │
│  │  • Users     │  │  • Cache     │  │  • Posts     │  │  • Session │  │
│  │  • Posts     │  │  • Sessions  │  │  • Stories   │  │            │  │
│  │  • Comments  │  │  • RateLimit │  │  • Messages  │  │            │  │
│  │  • Messages  │  │              │  │  • Groups    │  │            │  │
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
2. Public/index.php (Entry Point)
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
5. HTTP Kernel (app/Http/Kernel.php)
       │
       ├── Global Middleware
       │   • TrustHosts
       │   • HandleCors
       │   • ValidateCsrfToken
       │
       ▼
6. Route Matching
       │
       ├── Check Route Definition
       ├── Apply Route Middleware
       │   • auth
       │   • verified
       │   • admin
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
laravel_project/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── CleanupExpiredStories.php    # Hourly story cleanup
│   │       ├── DeleteExpiredStories.php     # Alternative cleanup command
│   │       ├── DeleteUnverifiedUsers.php    # Remove unverified accounts
│   │       ├── GeneratePostSlugs.php        # Migrate posts to slug system
│   │       ├── SendInactiveUserReminders.php # Re-engagement emails
│   │       └── SendTestEmail.php            # Email configuration test
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── CommentController.php    # API: Comment operations
│   │   │   │   ├── MessageController.php    # API: Message operations
│   │   │   │   ├── NotificationController.php # API: Notifications
│   │   │   │   ├── PostController.php       # API: Post operations
│   │   │   │   ├── UserController.php       # API: User operations
│   │   │   │   └── PasswordController.php   # API: Password changes
│   │   │   │
│   │   │   ├── Auth/
│   │   │   │   ├── AuthenticatedSessionController.php # Session management
│   │   │   │   ├── ConfirmablePasswordController.php  # Password confirmation
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── LoginController.php      # Login handling
│   │   │   │   ├── NewPasswordController.php # New password setup
│   │   │   │   ├── PasswordResetLinkController.php  # Reset link requests
│   │   │   │   ├── RegisteredUserController.php # User registration
│   │   │   │   ├── ResetPasswordController.php # Password reset
│   │   │   │   ├── VerifyEmailController.php # Email verification
│   │   │   │   └── SocialAuthController.php # Google OAuth
│   │   │   │
│   │   │   ├── AdminController.php          # Admin panel operations
│   │   │   ├── AiController.php             # AI chatbot
│   │   │   ├── ChatController.php           # Chat/messaging
│   │   │   ├── CommentController.php        # Comment CRUD
│   │   │   ├── Controller.php               # Base controller
│   │   │   ├── GroupController.php          # Group management
│   │   │   ├── LanguageController.php       # Language switching
│   │   │   ├── NotificationController.php   # Notifications
│   │   │   ├── PostController.php           # Post CRUD
│   │   │   ├── ProfileController.php        # Profile management
│   │   │   ├── StoryController.php          # Story operations
│   │   │   └── UserController.php           # User operations
│   │   │
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php          # Admin authorization
│   │   │   ├── Authenticate.php             # Auth check
│   │   │   ├── CheckEmailVerified.php       # Email verification check
│   │   │   ├── CheckUserSuspended.php       # Suspension check
│   │   │   ├── EncryptCookies.php           # Cookie encryption
│   │   │   ├── HandleInertiaRequests.php    # Inertia setup
│   │   │   ├── PreventRequestsDuringMaintenance.php
│   │   │   ├── RedirectIfAuthenticated.php  # Guest redirect
│   │   │   ├── SetLocale.php                # Language setting
│   │   │   ├── TrimStrings.php              # Input trimming
│   │   │   ├── TrustHosts.php               # Host trust
│   │   │   ├── TrustProxies.php             # Proxy trust
│   │   │   ├── ValidateSignature.php        # Signature validation
│   │   │   └── VerifyCsrfToken.php          # CSRF protection
│   │   │
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   └── LoginRequest.php         # Login validation
│   │   │   └── ProfileUpdateRequest.php     # Profile validation
│   │   │
│   │   └── Kernel.php                       # HTTP kernel
│   │
│   ├── Mail/
│   │   └── VerificationCodeMail.php         # Email verification code
│   │
│   ├── Models/
│   │   ├── Block.php                        # User blocking
│   │   ├── Comment.php                      # Comments
│   │   ├── CommentLike.php                  # Comment likes
│   │   ├── Conversation.php                 # Chat conversations
│   │   ├── Follow.php                       # Follow relationships
│   │   ├── Group.php                        # Groups
│   │   ├── GroupMember.php                  # Group membership
│   │   ├── Like.php                         # Post likes
│   │   ├── Mention.php                      # User mentions
│   │   ├── Message.php                      # Chat messages
│   │   ├── Notification.php                 # Notifications
│   │   ├── Post.php                         # Posts
│   │   ├── PostMedia.php                    # Post media attachments
│   │   ├── Profile.php                      # User profiles
│   │   ├── SavedPost.php                    # Saved posts
│   │   ├── Story.php                        # Stories
│   │   ├── StoryReaction.php                # Story reactions
│   │   ├── StoryView.php                    # Story views
│   │   └── User.php                         # Users
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php           # Application bootstrap
│   │   └── ObfuscatorServiceProvider.php    # URL obfuscation
│   │
│   └── Services/
│       ├── FileUploadService.php            # File upload handling
│       ├── JsObfuscator.php                 # JavaScript obfuscation
│       ├── MentionService.php               # @mention processing
│       └── RealtimeService.php              # Real-time polling
│
├── bootstrap/
│   ├── app.php                              # Application bootstrap
│   └── providers.php                        # Service providers
│
├── config/
│   ├── app.php                              # App configuration
│   ├── auth.php                             # Authentication config
│   ├── cache.php                            # Cache configuration
│   ├── database.php                         # Database config
│   ├── filesystems.php                      # File storage config
│   ├── logging.php                          # Logging config
│   ├── mail.php                             # Mail configuration
│   ├── queue.php                            # Queue configuration
│   ├── sanctum.php                          # Sanctum config
│   ├── services.php                         # Third-party services
│   └── session.php                          # Session config
│
├── database/
│   ├── factories/                           # Model factories
│   │   ├── PostFactory.php
│   │   ├── UserFactory.php
│   │   └── ...
│   │
│   ├── migrations/                          # Database migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 2025_12_31_183416_create_posts_table.php
│   │   ├── 2025_12_31_183428_create_follows_table.php
│   │   ├── 2025_12_31_183440_create_likes_table.php
│   │   ├── 2025_12_31_184455_create_comments_table.php
│   │   ├── 2025_12_31_184509_create_comment_likes_table.php
│   │   ├── 2025_12_31_185456_create_personal_access_tokens_table.php
│   │   ├── 2025_12_31_190832_create_profiles_table.php
│   │   ├── 2025_12_31_195043_add_is_private_to_profiles_table.php
│   │   ├── 2025_12_31_195638_create_blocks_table.php
│   │   ├── 2025_12_31_201829_add_media_to_posts_table.php
│   │   ├── 2025_12_31_203558_add_is_private_to_posts_table.php
│   │   ├── 2025_12_31_204120_create_post_media_table.php
│   │   ├── 2025_12_31_204526_make_content_nullable_in_posts_table.php
│   │   ├── 2025_12_31_211517_create_saved_posts_table.php
│   │   ├── 2026_01_01_020301_create_stories_table.php
│   │   ├── 2026_01_01_023011_add_views_to_stories_table.php
│   │   ├── 2026_01_01_024005_create_story_views_table.php
│   │   ├── 2026_01_01_024641_create_story_reactions_table.php
│   │   ├── 2026_01_02_001005_add_full_name_to_profiles_table.php
│   │   ├── 2026_01_02_020406_drop_full_name_from_profiles_table.php
│   │   ├── 2026_01_02_045911_add_is_admin_to_users_table.php
│   │   ├── 2026_01_02_052131_add_is_suspended_to_users_table.php
│   │   ├── 2026_01_02_165014_create_conversations_table.php
│   │   ├── 2026_01_02_165034_create_messages_table.php
│   │   ├── 2026_01_02_171409_add_slug_to_conversations_table.php
│   │   ├── 2026_01_02_180145_add_soft_deletes_to_messages_table.php
│   │   ├── 2026_01_02_215252_create_notifications_table.php
│   │   ├── 2026_01_03_214127_add_notified_at_to_messages_table.php
│   │   ├── 2026_01_03_215758_add_indexes_for_performance.php
│   │   ├── 2026_01_05_200731_create_mentions_table.php
│   │   ├── 2026_01_16_123018_add_verification_code_to_users_table.php
│   │   ├── 2026_01_19_140649_add_slug_to_posts_table.php
│   │   ├── 2026_02_12_100000_add_username_to_users_table.php
│   │   ├── 2026_02_13_091601_add_last_active_to_users.php
│   │   ├── 2026_02_19_121800_add_media_to_messages_table.php
│   │   ├── 2026_02_21_170301_create_groups_table.php
│   │   ├── 2026_02_21_170303_create_group_members_table.php
│   │   ├── 2026_02_21_170304_add_is_group_to_conversations_table.php
│   │   ├── 2026_02_23_000000_add_system_type_to_messages.php
│   │   ├── 2026_02_23_191845_add_group_invite_type_to_messages_table.php
│   │   ├── 2026_02_26_013542_populate_usernames_for_existing_users.php
│   │   ├── 2026_02_26_013853_add_username_changed_at_to_users_table.php
│   │   ├── 2026_02_26_015139_update_existing_usernames_to_remove_hyphens.php
│   │   ├── 2026_02_27_012712_increase_media_path_length_in_messages_table.php
│   │   ├── 2026_02_28_021459_populate_story_slugs_and_add_unique_constraint.php
│   │   ├── 2026_02_28_172610_add_visible_to_to_messages_table.php
│   │   ├── 2026_03_02_000000_add_delivered_at_to_messages_table.php
│   │   ├── 2026_03_02_000001_add_delete_options_to_messages_table.php
│   │   ├── 2026_03_09_210144_add_inactive_reminder_fields_to_users_table.php
│   │   ├── 2026_03_10_003407_make_password_column_nullable_in_users_table.php
│   │   ├── 2026_03_10_232137_add_slug_and_invite_link_to_groups_table.php
│   │   ├── 2026_03_10_232405_add_language_to_users_table.php
│   │   └── 2026_03_11_002925_add_performance_indexes.php
│   │
│   └── seeders/                             # Database seeders
│       └── DatabaseSeeder.php
│
├── public/
│   ├── index.php                            # Application entry point
│   ├── robots.txt                           # Robots configuration
│   └── .htaccess                            # Apache configuration
│
├── resources/
│   ├── css/
│   │   └── app.css                          # Tailwind CSS entry
│   │
│   ├── js/
│   │   ├── Components/                      # Vue components
│   │   │   ├── ApplicationLogo.vue
│   │   │   ├── Checkbox.vue
│   │   │   ├── DangerButton.vue
│   │   │   ├── Dropdown.vue
│   │   │   ├── InputError.vue
│   │   │   ├── InputLabel.vue
│   │   │   ├── Modal.vue
│   │   │   ├── NavLink.vue
│   │   │   ├── PrimaryButton.vue
│   │   │   ├── ResponsiveNavLink.vue
│   │   │   ├── SecondaryButton.vue
│   │   │   ├── TextInput.vue
│   │   │   ├── posts/
│   │   │   ├── comments/
│   │   │   ├── stories/
│   │   │   ├── chat/
│   │   │   └── groups/
│   │   │
│   │   ├── Layouts/                         # Vue layouts
│   │   │   ├── AppLayout.vue
│   │   │   └── GuestLayout.vue
│   │   │
│   │   ├── Pages/                           # Inertia pages
│   │   │   ├── auth/
│   │   │   ├── posts/
│   │   │   ├── stories/
│   │   │   ├── chat/
│   │   │   ├── groups/
│   │   │   ├── users/
│   │   │   ├── admin/
│   │   │   └── ai/
│   │   │
│   │   ├── types/                           # TypeScript types
│   │   │   └── global.d.ts
│   │   │
│   │   ├── app.js                           # Application entry
│   │   └── bootstrap.js                     # Bootstrap configuration
│   │
│   └── views/
│       ├── admin/                           # Admin views
│       │   ├── dashboard.blade.php
│       │   ├── users.blade.php
│       │   ├── user-detail.blade.php
│       │   ├── user-edit.blade.php
│       │   ├── posts.blade.php
│       │   ├── comments.blade.php
│       │   └── stories.blade.php
│       │
│       ├── auth/                            # Auth views
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── forgot-password.blade.php
│       │   ├── reset-password.blade.php
│       │   ├── verify-email.blade.php
│       │   ├── password-change.blade.php
│       │   ├── set-password.blade.php
│       │   └── suspended.blade.php
│       │
│       ├── chat/                            # Chat views
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       │
│       ├── emails/                          # Email templates
│       │   ├── password-reset.blade.php
│       │   ├── verification-code.blade.php
│       │   └── verification-code-text.blade.php
│       │
│       ├── errors/                          # Error pages
│       │   ├── 403.blade.php
│       │   ├── 404.blade.php
│       │   ├── 419.blade.php
│       │   └── 500.blade.php
│       │
│       ├── groups/                          # Group views
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       │
│       ├── layouts/                         # Layout templates
│       │   ├── app.blade.php
│       │   └── language.blade.php
│       │
│       ├── partials/                        # Partial views
│       │   ├── comment.blade.php
│       │   ├── language-switcher.blade.php
│       │   └── post.blade.php
│       │
│       ├── posts/                           # Post views
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       │
│       ├── stories/                         # Story views
│       │   ├── create.blade.php
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   └── viewers.blade.php
│       │
│       ├── users/                           # User views
│       │   ├── show.blade.php
│       │   ├── edit-profile.blade.php
│       │   ├── followers.blade.php
│       │   ├── following.blade.php
│       │   ├── blocked.blade.php
│       │   ├── saved-posts.blade.php
│       │   ├── explore.blade.php
│       │   └── search.blade.php
│       │
│       ├── ai/                              # AI views
│       │   └── index.blade.php
│       │
│       ├── app.blade.php                    # Root template
│       └── home.blade.php                   # Landing page
│
├── routes/
│   ├── web.php                              # Web routes
│   ├── api.php                              # API routes
│   ├── console.php                          # Console routes
│   └── channels.php                         # Broadcasting channels
│
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── avatars/                     # User avatars
│   │   │   ├── covers/                      # Cover images
│   │   │   ├── posts/                       # Post media
│   │   │   ├── stories/                     # Story media
│   │   │   ├── groups/                      # Group avatars
│   │   │   └── messages/                    # Message attachments
│   │   │
│   │   └── temp/                            # Temporary files
│   │
│   ├── framework/                           # Framework cache
│   │   ├── cache/
│   │   ├── sessions/
│   │   ├── views/
│   │   └── testing/
│   │
│   └── logs/                                # Application logs
│       └── laravel.log
│
├── tests/
│   ├── Feature/
│   │   └── ExampleTest.php
│   └── Unit/
│       └── ExampleTest.php
│
├── .env.example                             # Environment template
├── .gitignore                               # Git ignore rules
├── artisan                                  # Laravel CLI
├── composer.json                            # PHP dependencies
├── package.json                             # Node dependencies
├── phpunit.xml                              # PHPUnit config
├── tailwind.config.js                       # Tailwind config
├── vite.config.js                           # Vite config
└── README.md                                # Project documentation
```

---

## Design Patterns

### MVC Pattern

```
┌─────────────────────────────────────────────────────────────────┐
│                     Model-View-Controller                        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│   Model     │◀───────▶│ Controller  │────────▶│    View     │
│             │         │             │         │             │
│ • Data      │         │ • Input     │         │ • Display   │
│ • Business  │         │   Handling  │         │ • Templates │
│   Logic     │         │ • Validation│         │ • Blade/Vue │
│ • Database  │         │ • Response  │         │ • JSON      │
│   Access    │         │             │         │             │
└─────────────┘         └─────────────┘         └─────────────┘
       ▲                       │                       │
       │                       │                       │
       └───────────────────────┴───────────────────────┘
                         Eloquent ORM
```

### Repository Pattern (via Eloquent)

```php
// Controller
class PostController extends Controller
{
    public function index()
    {
        // Repository pattern via Eloquent
        $posts = Post::with(['user', 'media', 'likes'])
            ->latest()
            ->paginate(15);

        return inertia('Posts/Index', compact('posts'));
    }
}

// Model (Repository)
class Post extends Model
{
    // Data access methods
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }

    // Business logic
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
```

### Service Layer Pattern

```php
// Service class for mention handling
class MentionService
{
    public function processMentions($model, $content, $mentionerId)
    {
        // Extract @mentions from content
        preg_match_all('/@(\w+)/', $content, $matches);

        foreach ($matches[1] as $username) {
            $mentionedUser = User::where('username', $username)->first();

            if ($mentionedUser) {
                // Create mention record
                Mention::create([
                    'mentioner_id' => $mentionerId,
                    'mentioned_id' => $mentionedUser->id,
                    'mentionable_id' => $model->id,
                    'mentionable_type' => get_class($model),
                ]);

                // Create notification
                Notification::create([
                    'user_id' => $mentionedUser->id,
                    'type' => 'mention',
                    'data' => [
                        'mentioner_id' => $mentionerId,
                        'mentionable_type' => get_class($model),
                        'mentionable_id' => $model->id,
                    ],
                ]);
            }
        }
    }
}
```

### Observer Pattern (Eloquent Events)

```php
// In AppServiceProvider
public function boot(): void
{
    // Observer for Post model
    Post::deleted(function ($post) {
        // Cascade delete media files
        foreach ($post->media as $media) {
            Storage::disk('public')->delete($media->media_path);
        }
    });

    // Observer for Story model
    Story::created(function ($story) {
        // Schedule expiration cleanup
        // (Handled by hourly artisan command)
    });
}
```

---

## Data Flow

### Post Creation Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        Post Creation Flow                                │
└─────────────────────────────────────────────────────────────────────────┘

1. User fills post form
   │
   ├── Content (max 280 chars)
   ├── Media files (max 30)
   └── Privacy setting
   │
   ▼
2. Submit to POST /posts
   │
   ▼
3. PostController@store
   │
   ├── Validate request
   │   • content: string, max:280
   │   • is_private: boolean
   │   • media.*: file, mimes, max:50MB
   │
   ▼
4. Create Post record
   │
   ├── user_id: auth()->id()
   ├── content: validated content
   ├── is_private: privacy flag
   └── slug: Str::random(24)
   │
   ▼
5. Process media files
   │
   ├── For each file:
   │   ├── Validate type & size
   │   ├── Generate unique filename
   │   ├── Store in storage/app/public/posts/
   │   ├── Create PostMedia record
   │   └── Generate thumbnail (videos)
   │
   ▼
6. Process mentions
   │
   ├── Parse @username from content
   ├── Find mentioned users
   ├── Create Mention records
   └── Create Notification records
   │
   ▼
7. Return response
   │
   └── Redirect back with success message
```

### Message Sending Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Message Sending Flow                               │
└─────────────────────────────────────────────────────────────────────────┘

1. User types message
   │
   ├── Text content
   └── Optional media attachment
   │
   ▼
2. Submit to POST /chat/{conversation}
   │
   ▼
3. ChatController@store
   │
   ├── Validate conversation membership
   ├── Validate message content
   └── Validate media (if present)
   │
   ▼
4. Create Message record
   │
   ├── conversation_id
   ├── sender_id
   ├── content
   ├── type (text/image/file)
   └── media_path (if attachment)
   │
   ▼
5. Update conversation
   │
   └── last_message_at: now()
   │
   ▼
6. Create notifications
   │
   └── For each recipient:
       └── Create Notification record
   │
   ▼
7. Broadcast real-time event
   │
   └── broadcast(new MessageSent($message))
   │
   ▼
8. Return JSON response
   │
   └── Message with sender profile
```

---

## Security Architecture

### Authentication & Authorization

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     Security Architecture                                │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────┐
│                        Authentication Stack                            │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐               │
│  │   Session   │    │   Sanctum   │    │  Socialite  │               │
│  │   (Web)     │    │   (API)     │    │  (Google)   │               │
│  └──────┬──────┘    └──────┬──────┘    └──────┬──────┘               │
│         │                  │                  │                       │
│         └──────────────────┼──────────────────┘                       │
│                            │                                         │
│                            ▼                                         │
│                  ┌─────────────────┐                                 │
│                  │  Auth Middleware│                                 │
│                  └────────┬────────┘                                 │
│                           │                                          │
│                           ▼                                          │
│                  ┌─────────────────┐                                 │
│                  │  Guard Checks   │                                 │
│                  │  • Verified     │                                 │
│                  │  • Not Suspended│                                 │
│                  │  • Admin (if needed)                              │
│                  └─────────────────┘                                 │
└──────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────┐
│                        Authorization Flow                              │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  Request ──▶ Middleware Chain                                        │
│                                                                       │
│  1. Authenticate (auth middleware)                                    │
│     └── Check valid session/token                                    │
│                                                                       │
│  2. Email Verified (verified middleware)                              │
│     └── Check email_verified_at                                      │
│                                                                       │
│  3. Not Suspended (suspended middleware)                              │
│     └── Check is_suspended flag                                      │
│                                                                       │
│  4. Admin Only (admin middleware)                                     │
│     └── Check is_admin flag                                          │
│                                                                       │
│  5. Rate Limiting (throttle middleware)                               │
│     └── Check request count per minute                               │
│                                                                       │
│  6. CSRF Protection                                                   │
│     └── Validate CSRF token                                          │
│                                                                       │
└──────────────────────────────────────────────────────────────────────┘
```

### Input Validation

```php
// Example: Post creation validation
public function store(Request $request)
{
    $validated = $request->validate([
        'content' => ['required_without:media', 'string', 'max:280'],
        'is_private' => ['boolean'],
        'media.*' => [
            'file',
            'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm',
            'max:51200', // 50MB
        ],
    ], [
        'content.required_without' => 'Post must have content or media',
        'media.*.max' => 'Each file must be under 50MB',
    ]);

    // Process validated data...
}
```

### CSRF Protection

```
┌─────────────────────────────────────────────────────────────────┐
│                    CSRF Protection Flow                          │
└─────────────────────────────────────────────────────────────────┘

1. Session Start
   │
   └── Generate CSRF token
       └── Store in session
       └── Share with views (@csrf)

2. Form Submission
   │
   └── Include _token field
       └── Hidden input with token

3. Request Processing
   │
   └── VerifyCsrfToken middleware
       ├── Extract token from request
       ├── Compare with session token
       └── Reject if mismatch (419 error)

4. AJAX Requests
   │
   └── Include X-XSRF-TOKEN header
       └── Token from cookies
```

---

## Performance Architecture

### Caching Strategy

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        Caching Architecture                              │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│  Application │───▶│    Redis     │───▶│  Database    │
│  Layer       │    │    Cache     │    │  (MySQL)     │
└──────────────┘    └──────────────┘    └──────────────┘
       │                   │                   │
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         Cache Layers                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  1. View Cache                                                       │
│     └── Compiled Blade templates                                   │
│                                                                      │
│  2. Route Cache                                                      │
│     └── Registered routes list                                     │
│                                                                      │
│  3. Config Cache                                                     │
│     └── Merged configuration                                       │
│                                                                      │
│  4. Data Cache                                                       │
│     ├── User online status (5s TTL)                                │
│     ├── Typing indicators (5s TTL)                                 │
│     └── Expensive queries                                          │
│                                                                      │
│  5. Session Cache                                                    │
│     └── User sessions (file/redis)                                 │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### Query Optimization

```php
// Eager loading to prevent N+1 queries
$posts = Post::with([
    'user.profile',      // Eager load user and profile
    'media',             // Eager load media
    'likes',             // Eager load likes
    'comments.user.profile', // Eager load nested relationships
])
->whereHas('user', function ($query) use ($user) {
    $query->where('id', $user->id)
          ->orWhere('is_private', false);
})
->latest()
->paginate(15);

// With count for aggregate data
$users = User::withCount([
    'posts',
    'followers',
    'following',
])->get();
```

### Index Strategy

```sql
-- Primary indexes (auto-created)
PRIMARY KEY (id)

-- Foreign key indexes
INDEX (user_id)
INDEX (post_id)
INDEX (conversation_id)

-- Unique indexes
UNIQUE (username)
UNIQUE (email)
UNIQUE (slug)
UNIQUE (follower_id, followed_id)
UNIQUE (user_id, post_id)  -- likes

-- Composite indexes
INDEX (post_id, parent_id)  -- comments
INDEX (user_id, is_private) -- posts filtering
INDEX (expires_at)          -- story cleanup
```

---

## Next Steps

Continue reading:

- [Features Documentation](FEATURES.md) - Detailed feature flows
- [API Reference](API.md) - RESTful API documentation
- [Database Schema](DATABASE.md) - Complete table definitions
- [Frontend Guide](FRONTEND.md) - Vue.js architecture
