# Architecture & Database Documentation

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client Layer                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │   Browser   │  │   Mobile    │  │  Third-party │              │
│  │  (Vue.js)   │  │    Apps     │  │   Services   │              │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘              │
└─────────┼────────────────┼────────────────┼─────────────────────┘
          │                │                │
          │  HTTP/HTTPS    │  REST API      │  OAuth
          │  Inertia.js    │  Sanctum       │
          ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Application Layer                           │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    Laravel 12 Framework                   │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │   │
│  │  │   Routes    │  │ Middleware  │  │ Controllers │       │   │
│  │  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘       │   │
│  │         │                │                │               │   │
│  │  ┌──────▼────────────────▼────────────────▼──────┐       │   │
│  │  │              Services & Business Logic         │       │   │
│  │  └──────┬────────────────────────────────────────┘       │   │
│  │         │                                                 │   │
│  │  ┌──────▼────────────────────────────────────────┐       │   │
│  │  │                 Eloquent ORM                   │       │   │
│  │  └──────┬────────────────────────────────────────┘       │   │
│  └─────────┼─────────────────────────────────────────────────┘   │
└────────────┼─────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Data Layer                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │   MySQL/    │  │    Redis    │  │   File      │              │
│  │ PostgreSQL  │  │   (Cache)   │  │  Storage    │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
└─────────────────────────────────────────────────────────────────┘
```

---

### Application Flow

```
User Request
    │
    ▼
┌─────────────────┐
│  Public/Web     │
│  Middleware     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Auth           │
│  Middleware     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Route          │
│  Matching       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Controller     │
│  Action         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Request        │
│  Validation     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Service Layer  │
│  (Business      │
│   Logic)        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Model Layer    │
│  (Data Access)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Database       │
│  (Query)        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Response       │
│  (Inertia/Vue)  │
└─────────────────┘
```

---

## Directory Structure

```
laravel_project/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── DeleteExpiredStories.php    # Cleanup expired stories
│   │       ├── DeleteUnverifiedUsers.php   # Remove unverified users
│   │       ├── GeneratePostSlugs.php       # Generate post slugs
│   │       └── SendTestEmail.php           # Test email configuration
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── CommentController.php   # API comment operations
│   │   │   │   ├── MessageController.php   # API message operations
│   │   │   │   ├── NotificationController.php  # API notifications
│   │   │   │   ├── PostController.php      # API post operations
│   │   │   │   ├── UserController.php      # API user operations
│   │   │   │   └── PasswordController.php  # API password change
│   │   │   │
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php         # Login handling
│   │   │   │   ├── RegisterController.php      # Registration
│   │   │   │   ├── PasswordResetLinkController.php  # Reset link
│   │   │   │   ├── ResetPasswordController.php # Password reset
│   │   │   │   └── SocialAuthController.php    # Google OAuth
│   │   │   │
│   │   │   ├── AdminController.php         # Admin panel operations
│   │   │   ├── AiController.php            # AI chatbot
│   │   │   ├── ChatController.php          # Chat/messaging
│   │   │   ├── CommentController.php       # Comment operations
│   │   │   ├── Controller.php              # Base controller
│   │   │   ├── GroupController.php         # Group operations
│   │   │   ├── NotificationController.php  # Notifications
│   │   │   ├── PostController.php          # Post operations
│   │   │   ├── StoryController.php         # Story operations
│   │   │   └── UserController.php          # User operations
│   │   │
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php         # Admin authorization
│   │   │   ├── CheckEmailVerified.php      # Email verification
│   │   │   ├── CheckUserSuspended.php      # Account suspension
│   │   │   ├── ForceHttps.php              # HTTPS enforcement
│   │   │   └── HandleInertiaRequests.php   # Inertia setup
│   │   │
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   └── LoginRequest.php        # Login validation
│   │   │   └── ProfileUpdateRequest.php    # Profile validation
│   │   │
│   │   └── Kernel.php                      # HTTP kernel
│   │
│   ├── Mail/
│   │   └── VerificationCodeMail.php        # Email verification
│   │
│   ├── Models/
│   │   ├── Block.php                       # User blocks
│   │   ├── Comment.php                     # Comments
│   │   ├── CommentLike.php                 # Comment likes
│   │   ├── Conversation.php                # Chat conversations
│   │   ├── Follow.php                      # Follow relationships
│   │   ├── Group.php                       # Groups
│   │   ├── GroupMember.php                 # Group membership
│   │   ├── Like.php                        # Post likes
│   │   ├── Mention.php                     # User mentions
│   │   ├── Message.php                     # Chat messages
│   │   ├── Notification.php                # Notifications
│   │   ├── Post.php                        # Posts
│   │   ├── PostMedia.php                   # Post media
│   │   ├── Profile.php                     # User profiles
│   │   ├── SavedPost.php                   # Saved posts
│   │   ├── Story.php                       # Stories
│   │   ├── StoryReaction.php               # Story reactions
│   │   ├── StoryView.php                   # Story views
│   │   └── User.php                        # Users
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php          # App bootstrap
│   │
│   └── Services/
│       ├── MentionService.php              # Mention processing
│       └── RealtimeService.php             # Real-time polling
│
├── bootstrap/
│   └── app.php                             # App bootstrap
│
├── config/
│   ├── app.php                             # App config
│   ├── auth.php                            # Auth config
│   ├── database.php                        # Database config
│   ├── filesystems.php                     # Storage config
│   ├── logging.php                         # Logging config
│   ├── mail.php                            # Mail config
│   ├── queue.php                           # Queue config
│   ├── sanctum.php                         # Sanctum config
│   ├── services.php                        # Third-party services
│   └── session.php                         # Session config
│
├── database/
│   ├── factories/                          # Model factories
│   ├── migrations/                         # Database migrations
│   └── seeders/                            # Database seeders
│
├── public/
│   ├── index.php                           # Entry point
│   └── .htaccess                         # Apache config
│
├── resources/
│   ├── css/
│   │   └── app.css                         # Tailwind CSS
│   │
│   ├── js/
│   │   ├── Components/                     # Vue components
│   │   ├── Layouts/                        # Vue layouts
│   │   ├── Pages/                          # Inertia pages
│   │   ├── types/                          # TypeScript types
│   │   └── app.js                          # App entry
│   │
│   └── views/
│       ├── admin/                          # Admin views
│       ├── auth/                           # Auth views
│       ├── chat/                           # Chat views
│       ├── emails/                         # Email templates
│       ├── errors/                         # Error pages
│       ├── groups/                         # Group views
│       ├── layouts/                        # Layout templates
│       ├── partials/                       # Partial views
│       ├── posts/                          # Post views
│       ├── stories/                        # Story views
│       ├── users/                          # User views
│       └── app.blade.php                   # Root template
│
├── routes/
│   ├── api.php                             # API routes
│   ├── console.php                         # Console routes
│   └── web.php                             # Web routes
│
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── avatars/                    # User avatars
│   │   │   ├── covers/                     # Cover images
│   │   │   ├── posts/                      # Post media
│   │   │   ├── stories/                    # Story media
│   │   │   └── groups/                     # Group avatars
│   │   └── temp/                           # Temporary files
│   │
│   ├── framework/                          # Framework cache
│   └── logs/                               # Application logs
│
├── tests/
│   ├── Feature/                            # Feature tests
│   └── Unit/                               # Unit tests
│
└── .env                                    # Environment config
```

---

## Database Schema

### Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │    profiles     │
├─────────────────┤       ├─────────────────┤
│ id              │───┬──▶│ id              │
│ name            │   │   │ user_id (FK)    │
│ username        │   │   │ avatar          │
│ email           │   │   │ cover_image     │
│ password        │   │   │ bio             │
│ is_admin        │   │   │ website         │
│ is_suspended    │   │   │ location        │
│ is_online       │   │   │ birth_date      │
│ last_active     │   │   │ occupation      │
│ verification_*  │   │   │ about           │
│ username_*      │   │   │ phone           │
│ timestamps      │   │   │ gender          │
└────────┬────────┘   │   │ is_private      │
         │            │   │ social_links    │
         │            │   │ timestamps      │
         │            │   └─────────────────┘
         │
         │  ┌──────────────────────────────────────────┐
         │  │                                          │
         ▼  ▼                                          ▼
┌─────────────────┐       ┌─────────────────┐   ┌─────────────────┐
│     posts       │       │    follows      │   │    comments     │
├─────────────────┤       ├─────────────────┤   ├─────────────────┤
│ id              │       │ id              │   │ id              │
│ user_id (FK)    │       │ follower_id     │   │ user_id (FK)    │
│ content         │       │ followed_id     │   │ post_id (FK)    │
│ slug            │       │ timestamps      │   │ parent_id (FK)  │
│ is_private      │       └─────────────────┘   │ content         │
│ timestamps      │                             │ timestamps      │
└────────┬────────┘                             └─────────────────┘
         │
         │  ┌──────────────────────────────────────────┐
         │  │                                          │
         ▼  ▼                                          ▼
┌─────────────────┐       ┌─────────────────┐   ┌─────────────────┐
│   post_media    │       │     likes       │   │  saved_posts    │
├─────────────────┤       ├─────────────────┤   ├─────────────────┤
│ id              │       │ id              │   │ id              │
│ post_id (FK)    │       │ user_id (FK)    │   │ user_id (FK)    │
│ media_type      │       │ post_id (FK)    │   │ post_id (FK)    │
│ media_path      │       │ timestamps      │   │ timestamps      │
│ sort_order      │       └─────────────────┘   └─────────────────┘
│ timestamps      │
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│    stories      │       │  story_views    │
├─────────────────┤       ├─────────────────┤
│ id              │──────▶│ id              │
│ user_id (FK)    │       │ user_id (FK)    │
│ slug            │       │ story_id (FK)   │
│ media_type      │       │ timestamps      │
│ media_path      │       └─────────────────┘
│ content         │
│ expires_at      │       ┌─────────────────┐
│ views           │       │story_reactions  │
│ timestamps      │       ├─────────────────┤
└─────────────────┘       │ id              │
                          │ user_id (FK)    │
                          │ story_id (FK)   │
                          │ reaction_type   │
                          │ timestamps      │
                          └─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│  conversations  │       │    messages     │
├─────────────────┤       ├─────────────────┤
│ id              │──────▶│ id              │
│ user1_id (FK)   │       │ conversation_id │
│ user2_id (FK)   │       │ sender_id (FK)  │
│ is_group        │       │ content         │
│ group_id (FK)   │       │ type            │
│ slug            │       │ media_path      │
│ last_message_at │       │ read_at         │
│ timestamps      │       │ delivered_at    │
└─────────────────┘       │ deleted_for     │
                          │ timestamps      │
┌─────────────────┐       └─────────────────┘
│     groups      │
├─────────────────┤
│ id              │       ┌─────────────────┐
│ name            │       │ group_members   │
│ description     │       ├─────────────────┤
│ creator_id (FK) │◀──────│ id              │
│ avatar          │       │ group_id (FK)   │
│ is_private      │       │ user_id (FK)    │
│ slug            │       │ role            │
│ invite_link     │       │ joined_at       │
│ timestamps      │       └─────────────────┘
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│ notifications   │       │    mentions     │
├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │
│ user_id (FK)    │       │ mentioner_id    │
│ type            │       │ mentioned_id    │
│ data (JSON)     │       │ mentionable_*   │
│ read_at         │       │ timestamps      │
│ related_*       │       └─────────────────┘
│ timestamps      │
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│     blocks      │       │ comment_likes   │
├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │
│ blocker_id (FK) │       │ user_id (FK)    │
│ blocked_id (FK) │       │ comment_id (FK) │
│ timestamps      │       │ timestamps      │
└─────────────────┘       └─────────────────┘
```

---

## Table Definitions

### users

Stores user account information.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NO | - | User's display name |
| username | VARCHAR(255) | NO | - | Unique username |
| email | VARCHAR(255) | NO | - | Unique email |
| email_verified_at | TIMESTAMP | YES | NULL | Email verification date |
| password | VARCHAR(255) | YES | NULL | Hashed password |
| is_admin | BOOLEAN | NO | FALSE | Admin flag |
| is_suspended | BOOLEAN | NO | FALSE | Suspension flag |
| verification_code | VARCHAR(6) | YES | NULL | Email verification code |
| verification_code_expires_at | TIMESTAMP | YES | NULL | Code expiration |
| last_active | TIMESTAMP | YES | NULL | Last activity timestamp |
| is_online | BOOLEAN | NO | FALSE | Online status |
| username_changed_at | TIMESTAMP | YES | NULL | Last username change |
| remember_token | VARCHAR(100) | YES | NULL | Remember me token |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (username)
- UNIQUE (email)

---

### profiles

Stores extended user profile information.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| avatar | VARCHAR(255) | YES | NULL | Avatar image path |
| cover_image | VARCHAR(255) | YES | NULL | Cover image path |
| bio | VARCHAR(255) | YES | NULL | Short bio |
| website | VARCHAR(255) | YES | NULL | Website URL |
| location | VARCHAR(255) | YES | NULL | Location |
| birth_date | DATE | YES | NULL | Birth date |
| occupation | VARCHAR(255) | YES | NULL | Occupation |
| about | TEXT | YES | NULL | Extended about |
| phone | VARCHAR(50) | YES | NULL | Phone number |
| gender | VARCHAR(50) | YES | NULL | Gender |
| is_private | BOOLEAN | NO | FALSE | Private account |
| social_links | JSON | YES | NULL | Social media links |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### posts

Stores user posts.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| content | TEXT | YES | NULL | Post content |
| slug | VARCHAR(24) | NO | - | Unique 24-char slug |
| is_private | BOOLEAN | NO | FALSE | Privacy flag |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (user_id, is_private)

---

### post_media

Stores media attachments for posts.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| media_type | ENUM('image', 'video') | NO | - | Media type |
| media_path | VARCHAR(255) | NO | - | File path |
| media_thumbnail | VARCHAR(255) | YES | NULL | Thumbnail path |
| sort_order | INT | NO | 0 | Display order |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
- INDEX (post_id, sort_order)

---

### comments

Stores post comments (supports nested replies).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| parent_id | BIGINT UNSIGNED | YES | NULL | Parent comment (for replies) |
| content | TEXT | NO | - | Comment content |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
- FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
- INDEX (post_id, parent_id)

---

### follows

Stores user follow relationships.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| follower_id | BIGINT UNSIGNED | NO | - | User who is following |
| followed_id | BIGINT UNSIGNED | NO | - | User being followed |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (follower_id, followed_id)
- FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE

---

### likes

Stores post likes.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, post_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE

---

### comment_likes

Stores comment likes.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| comment_id | BIGINT UNSIGNED | NO | - | Foreign key to comments |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, comment_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE

---

### blocks

Stores user block relationships.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| blocker_id | BIGINT UNSIGNED | NO | - | User who is blocking |
| blocked_id | BIGINT UNSIGNED | NO | - | User being blocked |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (blocker_id, blocked_id)
- FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE

---

### saved_posts

Stores saved/bookmarked posts.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| post_id | BIGINT UNSIGNED | NO | - | Foreign key to posts |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, post_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE

---

### stories

Stores ephemeral stories (24-hour lifespan).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| slug | VARCHAR(24) | NO | - | Unique 24-char slug |
| media_type | ENUM('image', 'video') | NO | - | Media type |
| media_path | VARCHAR(255) | NO | - | File path |
| content | TEXT | YES | NULL | Story caption |
| expires_at | TIMESTAMP | NO | - | Expiration timestamp |
| views | INT UNSIGNED | NO | 0 | View count |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (expires_at)

---

### story_views

Tracks story views.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| story_id | BIGINT UNSIGNED | NO | - | Foreign key to stories |
| created_at | TIMESTAMP | YES | NULL | View timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE
- UNIQUE (user_id, story_id)

---

### story_reactions

Stores story reactions.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| story_id | BIGINT UNSIGNED | NO | - | Foreign key to stories |
| reaction_type | VARCHAR(50) | NO | - | Emoji reaction |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (user_id, story_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE

---

### conversations

Stores chat conversations (DM and group).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user1_id | BIGINT UNSIGNED | NO | - | First participant |
| user2_id | BIGINT UNSIGNED | YES | NULL | Second participant (DM) |
| is_group | BOOLEAN | NO | FALSE | Group conversation flag |
| group_id | BIGINT UNSIGNED | YES | NULL | Foreign key to groups |
| slug | VARCHAR(255) | NO | - | Unique slug |
| name | VARCHAR(255) | YES | NULL | Group name |
| avatar | VARCHAR(255) | YES | NULL | Group avatar |
| last_message_at | TIMESTAMP | YES | NULL | Last message timestamp |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- UNIQUE (user1_id, user2_id) WHERE is_group = FALSE
- FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE

---

### messages

Stores chat messages.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| conversation_id | BIGINT UNSIGNED | NO | - | Foreign key to conversations |
| sender_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| visible_to | BIGINT UNSIGNED | YES | NULL | Visibility (for delete) |
| content | TEXT | YES | NULL | Message content |
| type | ENUM('text', 'image', 'file') | NO | 'text' | Message type |
| media_path | JSON | YES | NULL | Media file paths |
| media_thumbnail | VARCHAR(255) | YES | NULL | Thumbnail path |
| original_filename | VARCHAR(255) | YES | NULL | Original filename |
| media_size | INT | YES | NULL | File size in bytes |
| read_at | TIMESTAMP | YES | NULL | Read timestamp |
| delivered_at | TIMESTAMP | YES | NULL | Delivery timestamp |
| notified_at | TIMESTAMP | YES | NULL | Notification timestamp |
| deleted_for | JSON | YES | NULL | User IDs deleted for |
| deleted_by_sender | BOOLEAN | NO | FALSE | Deleted by sender |
| deleted_at | TIMESTAMP | YES | NULL | Soft delete timestamp |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
- FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (conversation_id, created_at)
- INDEX (read_at)

---

### groups

Stores user groups.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NO | - | Group name |
| description | TEXT | YES | NULL | Group description |
| creator_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| avatar | VARCHAR(255) | YES | NULL | Group avatar |
| is_private | BOOLEAN | NO | FALSE | Private group flag |
| slug | VARCHAR(255) | NO | - | Unique slug |
| invite_link | VARCHAR(255) | YES | NULL | Unique invite link |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- UNIQUE (invite_link)
- FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE

---

### group_members

Stores group membership.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| group_id | BIGINT UNSIGNED | NO | - | Foreign key to groups |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| role | ENUM('admin', 'member') | NO | 'member' | Member role |
| joined_at | TIMESTAMP | YES | NULL | Join timestamp |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (group_id, user_id)
- FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (group_id, role)

---

### notifications

Stores user notifications.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | NO | - | Foreign key to users |
| type | VARCHAR(50) | NO | - | Notification type |
| data | JSON | NO | - | Notification data |
| read_at | TIMESTAMP | YES | NULL | Read timestamp |
| related_id | BIGINT UNSIGNED | YES | NULL | Related entity ID |
| related_type | VARCHAR(255) | YES | NULL | Related entity type |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (user_id, read_at)
- INDEX (related_id, related_type)

---

### mentions

Stores user mentions in posts/comments.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| mentioner_id | BIGINT UNSIGNED | NO | - | User who mentioned |
| mentioned_id | BIGINT UNSIGNED | NO | - | User being mentioned |
| mentionable_type | VARCHAR(255) | NO | - | Polymorphic type |
| mentionable_id | BIGINT UNSIGNED | NO | - | Polymorphic ID |
| created_at | TIMESTAMP | YES | NULL | Creation timestamp |
| updated_at | TIMESTAMP | YES | NULL | Update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (mentioner_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (mentioned_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (mentionable_id, mentionable_type)
- UNIQUE (mentioner_id, mentioned_id, mentionable_type, mentionable_id)

---

## Model Relationships

### User Model

```php
// One-to-Many
hasMany(Post::class)      // User's posts
hasMany(Comment::class)   // User's comments
hasMany(Like::class)      // User's likes
hasMany(Story::class)     // User's stories
hasMany(Notification::class)  // User's notifications
hasMany(Message::class)   // User's messages
hasMany(Block::class, 'blocker_id')    // Users blocked by this user
hasMany(Block::class, 'blocked_id')    // Blocks against this user
hasMany(Follow::class, 'follower_id')  // Users this user follows
hasMany(Follow::class, 'followed_id')  // Followers of this user
hasMany(GroupMember::class)  // Group memberships
hasMany(SavedPost::class)  // Saved posts
hasMany(StoryView::class)  // Story views
hasMany(StoryReaction::class)  // Story reactions
hasMany(CommentLike::class)  // Comment likes

// One-to-One
hasOne(Profile::class)    // User's profile

// Many-to-Many
belongsToMany(User::class, 'following', 'follower_id', 'followed_id')  // Following
belongsToMany(User::class, 'followers', 'followed_id', 'follower_id')  // Followers
belongsToMany(Conversation::class)  // Conversations
belongsToMany(Group::class)  // Groups
```

### Post Model

```php
// BelongsTo
belongsTo(User::class)    // Post author

// One-to-Many
hasMany(Comment::class)   // Post comments
hasMany(Like::class)      // Post likes
hasMany(PostMedia::class) // Post media attachments
hasMany(SavedPost::class) // Saved post records
```

### Comment Model

```php
// BelongsTo
belongsTo(User::class)    // Comment author
belongsTo(Post::class)    // Parent post
belongsTo(Comment::class, 'parent_id')  // Parent comment

// One-to-Many
hasMany(Comment::class, 'parent_id')  // Replies
hasMany(CommentLike::class)  // Comment likes
```

### Story Model

```php
// BelongsTo
belongsTo(User::class)    // Story author

// One-to-Many
hasMany(StoryView::class)  // Story views
hasMany(StoryReaction::class)  // Story reactions

// Scope
scopeActive()  // Stories not yet expired
```

### Conversation Model

```php
// BelongsTo
belongsTo(User::class, 'user1_id')
belongsTo(User::class, 'user2_id')
belongsTo(Group::class)

// One-to-Many
hasMany(Message::class)

// Accessors
getOtherUserAttribute()  // Other participant in DM
getDisplayNameAttribute()  // Display name
getDisplayAvatarAttribute()  // Display avatar
getUnreadCountAttribute()  // Unread message count
```

### Message Model

```php
// BelongsTo
belongsTo(Conversation::class)
belongsTo(User::class, 'sender_id')

// Methods
markAsRead()  // Mark message as read
```

### Group Model

```php
// BelongsTo
belongsTo(User::class, 'creator_id')

// One-to-Many
hasMany(GroupMember::class)
hasOne(Conversation::class)

// Many-to-Many (through GroupMember)
hasMany(GroupMember::class, 'admins')  // Admin members
hasMany(GroupMember::class, 'regularMembers')  // Regular members

// Methods
hasMember(User::class)  // Check membership
isAdmin(User::class)  // Check if admin
addMember(User::class, role)  // Add member
removeMember(User::class)  // Remove member
```

### Notification Model

```php
// BelongsTo
belongsTo(User::class)

// MorphTo
morphTo('related')  // Related entity

// Methods
markAsRead()  // Mark as read
markAsUnread()  // Mark as unread
isRead()  // Check if read
getMessageAttribute()  // Human-readable message
```

---

## Services

### MentionService

Handles @mention parsing, processing, and notification.

**Location:** `app/Services/MentionService.php`

**Methods:**

```php
/**
 * Parse @mentions from text
 * @param string $text
 * @return array ['@username1', '@username2']
 */
public function parseMentions(string $text): array

/**
 * Process mentions and create notifications
 * @param Model $mentionable (Post or Comment)
 * @param string $text
 * @param int $mentionerId
 * @return void
 */
public function processMentions(Model $mentionable, string $text, int $mentionerId): void

/**
 * Convert mentions to HTML links
 * @param string $text
 * @return string
 */
public function convertMentionsToLinks(string $text): string
```

**Usage:**
```php
// In PostController@store
$mentionService->processMentions($post, $content, auth()->id());

// In PostController@show
$content = $mentionService->convertMentionsToLinks($post->content);
```

---

### RealtimeService

Handles real-time data via polling (5-second intervals).

**Location:** `app/Services/RealtimeService.php`

**Methods:**

```php
/**
 * Update cache for user
 * @param string $key
 * @param mixed $value
 * @return void
 */
public function updateCache(string $key, $value): void

/**
 * Get cached value
 * @param string $key
 * @return mixed
 */
public function getCache(string $key)

/**
 * Check if realtime is available
 * @return bool
 */
public function isRealtimeAvailable(): bool

/**
 * Get polling configuration
 * @return array ['interval' => 5000, ...]
 */
public function getRealtimeConfig(): array

/**
 * Update user notification count cache
 * @param int $userId
 * @param int $count
 * @return void
 */
public function updateUserNotificationCount(int $userId, int $count): void

/**
 * Update post engagement data
 * @param int $postId
 * @param array $data
 * @return void
 */
public function updatePostData(int $postId, array $data): void

/**
 * Get real-time data for user
 * @param int $userId
 * @return array
 */
public function getRealtimeData(int $userId): array
```

**Usage:**
```php
// In NotificationController
$realtimeService->updateUserNotificationCount($userId, $unreadCount);

// In frontend (polling)
setInterval(() => {
    fetch('/api/notifications/realtime-updates')
}, 5000);
```

---

## Middleware

### AdminMiddleware

**Location:** `app/Http/Middleware/AdminMiddleware.php`

**Purpose:** Restricts access to admin-only routes.

**Logic:**
```php
public function handle($request, Closure $next)
{
    if (!auth()->check() || !auth()->user()->is_admin) {
        return redirect()->route('login');
    }
    return $next($request);
}
```

**Applied to:**
- All `/admin/*` routes

---

### CheckEmailVerified

**Location:** `app/Http/Middleware/CheckEmailVerified.php`

**Purpose:** Ensures user has verified email.

**Logic:**
```php
public function handle($request, Closure $next)
{
    if (!auth()->user()->hasVerifiedEmail() && !auth()->user()->is_admin) {
        return redirect()->route('verification.notice');
    }
    return $next($request);
}
```

**Applied to:**
- All authenticated routes (except verification routes)

---

### CheckUserSuspended

**Location:** `app/Http/Middleware/CheckUserSuspended.php`

**Purpose:** Prevents suspended users from accessing the app.

**Logic:**
```php
public function handle($request, Closure $next)
{
    if (auth()->check() && auth()->user()->is_suspended) {
        auth()->logout();
        return redirect()->route('auth.suspended');
    }
    return $next($request);
}
```

**Applied to:**
- All authenticated routes

---

### ForceHttps

**Location:** `app/Http/Middleware/ForceHttps.php`

**Purpose:** Forces HTTPS in production.

**Logic:**
```php
public function handle($request, Closure $next)
{
    if (app()->environment('production') && !$request->secure()) {
        return redirect()->secure($request->getRequestUri());
    }
    return $next($request);
}
```

**Applied to:**
- Global middleware (production only)

---

### HandleInertiaRequests

**Location:** `app/Http/Middleware/HandleInertiaRequests.php`

**Purpose:** Configures Inertia.js requests.

**Logic:**
```php
public function share($request)
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user(),
        ],
    ]);
}
```

**Applied to:**
- Web middleware group

---

## Console Commands

### DeleteExpiredStories

**Command:** `php artisan stories:cleanup`

**Location:** `app/Console/Commands/DeleteExpiredStories.php`

**Purpose:** Deletes stories older than 24 hours.

**Logic:**
```php
public function handle()
{
    $expiredStories = Story::where('expires_at', '<', now())->get();
    
    foreach ($expiredStories as $story) {
        // Delete media files
        Storage::delete($story->media_path);
        $story->delete();
    }
    
    $this->info("Deleted {$expiredStories->count()} expired stories.");
}
```

**Scheduled:** Hourly via `app/Console/Kernel.php`

---

### DeleteUnverifiedUsers

**Command:** `php artisan users:delete-unverified {--hours=24}`

**Location:** `app/Console/Commands/DeleteUnverifiedUsers.php`

**Purpose:** Deletes unverified users older than specified hours.

**Logic:**
```php
public function handle()
{
    $hours = $this->option('hours');
    $users = User::whereNull('email_verified_at')
        ->where('created_at', '<', now()->subHours($hours))
        ->get();
    
    foreach ($users as $user) {
        $user->delete();
    }
    
    $this->info("Deleted {$users->count()} unverified users.");
}
```

---

### GeneratePostSlugs

**Command:** `php artisan posts:generate-slugs`

**Location:** `app/Console/Commands/GeneratePostSlugs.php`

**Purpose:** Generates slugs for existing posts without them.

**Logic:**
```php
public function handle()
{
    $posts = Post::whereNull('slug')->get();
    
    foreach ($posts as $post) {
        $post->slug = $post->generateUniqueSlug();
        $post->save();
    }
    
    $this->info("Generated slugs for {$posts->count()} posts.");
}
```

---

### SendTestEmail

**Command:** `php artisan mail:test {email?}`

**Location:** `app/Console/Commands/SendTestEmail.php`

**Purpose:** Sends test email to verify mail configuration.

**Logic:**
```php
public function handle()
{
    $email = $this->argument('email') ?? auth()->user()->email;
    
    Mail::to($email)->send(new VerificationCodeMail('123456'));
    
    $this->info("Test email sent to {$email}");
}
```

---

## Events & Notifications

### Notification Types

| Type | Trigger | Data |
|------|---------|------|
| `follow` | User follows another user | `{ user: {...} }` |
| `like` | User likes post | `{ user: {...}, post_id: 1 }` |
| `comment` | User comments on post | `{ user: {...}, post_id: 1, comment_id: 1 }` |
| `mention` | User mentioned in post/comment | `{ user: {...}, post_id: 1 }` |
| `message` | New message received | `{ user: {...}, conversation_id: 1 }` |
| `group_invite` | Invited to group | `{ user: {...}, group_id: 1, invite_link: "..." }` |

### Creating Notifications

```php
// Using NotificationController::createNotification
NotificationController::createNotification(
    $recipientId,
    'like',
    ['user' => $liker, 'post_id' => $post->id],
    $post
);
```

---

## Security Measures

### Input Validation

All user input is validated using Form Request classes:

```php
// LoginRequest
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ];
}
```

### CSRF Protection

Automatic via Laravel middleware for all POST, PUT, DELETE requests.

### XSS Prevention

- Blade templates escape output by default: `{{ $variable }}`
- Vue.js automatically escapes interpolated values

### SQL Injection Prevention

Eloquent ORM uses parameterized queries:

```php
// Safe - uses parameterized query
User::where('email', $email)->first();

// Unsafe - don't do this
DB::select("SELECT * FROM users WHERE email = '$email'");
```

### Password Hashing

Bcrypt algorithm with automatic salting:

```php
Hash::make($password);  // Hash
Hash::check($password, $hash);  // Verify
```

### Rate Limiting

```php
// In RouteServiceProvider
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->email);
});
```

---

## Performance Optimizations

### Eager Loading

```php
// Bad - N+1 query problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;  // Query per post
}

// Good - Eager loading
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;  // No additional queries
}
```

### Query Caching

```php
// Cache user notification count
$cacheKey = "user:{$userId}:notification_count";
$count = Cache::remember($cacheKey, 300, function () use ($userId) {
    return Notification::where('user_id', $userId)
        ->whereNull('read_at')
        ->count();
});
```

### Image Compression

```php
// In UserController@updateProfile
if ($request->hasFile('avatar')) {
    $image = Image::make($request->file('avatar'));
    $image->resize(400, 400, function ($constraint) {
        $constraint->aspectRatio();
    });
    $image->save($path);
}
```

### Pagination

```php
// Paginate large datasets
$posts = Post::with(['user', 'media'])
    ->latest()
    ->paginate(15);
```

---

## Testing Strategy

### Feature Tests

Test complete user flows:

```php
public function test_user_can_create_post()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/posts', [
            'content' => 'Test post',
        ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'content' => 'Test post',
    ]);
}
```

### Unit Tests

Test individual methods:

```php
public function test_mention_service_parses_mentions()
{
    $service = new MentionService();
    $mentions = $service->parseMentions('Hello @john and @jane');
    
    $this->assertEquals(['@john', '@jane'], $mentions);
}
```

### Browser Tests (Optional)

Test with Laravel Dusk:

```php
public function test_login()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'user@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/');
    });
}
```

---

## Deployment Considerations

### Environment Variables

Required `.env` settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_DATABASE=nexus
DB_USERNAME=prod_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.provider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=secure_password

GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
```

### Queue Configuration

For background jobs (emails, notifications):

```bash
# Start queue worker
php artisan queue:work --daemon

# Supervisor configuration
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work
autostart=true
autorestart=true
numprocs=4
```

### Cron Setup

For scheduled tasks:

```bash
# Add to crontab
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Asset Building

```bash
# Build production assets
npm run build

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**Last Updated**: March 2026
