# Nexus Project Summary

Complete overview of the Nexus social networking platform - features, architecture, and statistics.

---

## Project Overview

**Nexus** is a production-ready social networking platform built with Laravel 12 that enables users to connect, share, and communicate in real-time.

| Attribute | Value |
|-----------|-------|
| **Framework** | Laravel 12.x |
| **PHP Version** | 8.2+ |
| **Database** | SQLite (dev) / MySQL 8.0+ (prod) |
| **Frontend** | Blade Templates + Vue.js 3 |
| **Real-Time** | Polling-based (2-10s intervals) |
| **Languages** | English & Arabic (RTL support) |
| **Last Updated** | March 16, 2026 |

---

## Project Statistics

### Code Metrics

| Component | Count | Description |
|-----------|-------|-------------|
| **Controllers** | 31 | 30 controllers + 1 base class |
| **└─ Main Controllers** | 11 | Post, Comment, Story, Chat, User, Group, etc. |
| **└─ API Controllers** | 6 | Post, Comment, Message, Notification, User, Password |
| **└─ Auth Controllers** | 13 | Login, Register, Password Reset, OAuth, etc. |
| **Models** | 19 | Eloquent ORM models |
| **Middleware** | 8 | Auth, Admin, Verified, Suspended, etc. |
| **Services** | 4 | MentionService, RealtimeService, FileUploadService, JsObfuscator |
| **Console Commands** | 6 | Cleanup, Delete, Generate, Send reminders |
| **Migrations** | 58 | Database schema migrations |
| **Database Tables** | 24 | Users, Posts, Comments, Messages, Groups, etc. |
| **JavaScript Modules** | 16 | Legacy modules (realtime, chat, posts, etc.) |
| **Blade Views** | 49 | Server-rendered templates |
| **Language Files** | 24 | English (12) + Arabic (12) |

### File Distribution

```
laravel_project/
├── app/
│   ├── Http/Controllers/     31 files
│   ├── Http/Middleware/       8 files
│   ├── Models/               19 files
│   ├── Services/              4 files
│   ├── Console/Commands/      6 files
│   ├── Mail/                  1 file
│   └── Providers/             2 files
├── database/migrations/       58 files
├── resources/
│   ├── views/                49 files
│   └── js/legacy/            16 files
├── lang/
│   ├── en/                   12 files
│   └── ar/                   12 files
└── routes/
    ├── web.php               (main routing)
    ├── api.php               (API endpoints)
    └── console.php           (console routes)
```

---

## Features

### 1. Authentication System

| Feature | Description |
|---------|-------------|
| **Email/Password** | Traditional registration with 6-digit email verification |
| **Google OAuth** | Single sign-on via Google |
| **Password Reset** | Email-based password recovery |
| **Email Verification** | 6-digit code system (10-minute expiry) |
| **Account Suspension** | Admin-controlled suspension |
| **Username System** | Unique usernames with 3-day cooldown |
| **Password Strength** | Requires 3 of 5 criteria |
| **Reserved Usernames** | 40+ blocked names |
| **Disposable Email Blocking** | 16+ temporary email domains blocked |

### 2. Content Management

| Feature | Description |
|---------|-------------|
| **Posts** | Text (280 chars) + 30 media files (50MB each) |
| **Comments** | Nested replies with @mentions and likes |
| **Reactions** | Like, save, share posts |
| **Stories** | Ephemeral 24-hour content |
| **Media Processing** | FFmpeg thumbnails, compression |
| **Slug URLs** | 24-character unique slugs (SEO-friendly) |

### 3. Real-Time Communication

| Feature | Description | Polling Interval |
|---------|-------------|------------------|
| **Direct Messages** | One-on-one conversations | 2 seconds |
| **Group Chat** | Multi-user conversations | 2 seconds |
| **Typing Indicators** | Real-time typing status | 1 second |
| **Read Receipts** | Delivery and read tracking | On message open |
| **Online Status** | User availability | 10 seconds |
| **Notifications** | Real-time alerts | 3 seconds |

### 4. Social Network

| Feature | Description |
|---------|-------------|
| **Follow System** | Follow/unfollow with private account support |
| **User Profiles** | Avatars, cover photos, bio, social links |
| **Privacy Controls** | Private accounts, post-level privacy |
| **Block Users** | Block unwanted interactions |
| **Explore** | Discover new users and content |
| **Mentions** | @username with notifications |

### 5. Groups

| Feature | Description |
|---------|-------------|
| **Create Groups** | Public or private communities |
| **Member Roles** | Admin and member permissions |
| **Invite Links** | Shareable unique links |
| **Group Chat** | Dedicated conversation |
| **Member Management** | Add/remove, promote to admin |

### 6. AI Assistant

| Feature | Description |
|---------|-------------|
| **Menu-Based Chat** | Interactive chatbot interface |
| **Context Aware** | Remembers conversation history |
| **Quick Actions** | Pre-defined prompts |

### 7. Admin Panel

| Feature | Description |
|---------|-------------|
| **Dashboard** | Platform statistics |
| **User Management** | View, edit, suspend, delete |
| **Content Moderation** | Delete posts, comments, stories |
| **Admin Creation** | Create new admin accounts |

---

## Database Schema

### Core Tables (24 Total)

#### User Management (4 tables)
- `users` - User accounts and authentication
- `profiles` - Extended user information
- `follows` - User follow relationships
- `blocks` - User block relationships

#### Content (6 tables)
- `posts` - User posts with slug URLs
- `post_media` - Media attachments (images/videos)
- `comments` - Post comments with nested replies
- `comment_likes` - Comment likes
- `likes` - Post likes
- `saved_posts` - Bookmarked posts
- `mentions` - User mentions in content

#### Stories (3 tables)
- `stories` - Ephemeral 24-hour stories
- `story_views` - Story view tracking
- `story_reactions` - Story emoji reactions

#### Messaging (3 tables)
- `conversations` - Chat conversations (direct/group)
- `messages` - Chat messages with delivery tracking
- `notifications` - User notifications

#### Groups (3 tables)
- `groups` - User groups with invite links
- `group_members` - Group membership with roles
- `conversation` links (via `is_group` flag)

#### System (5 tables)
- `cache` - Application cache
- `sessions` - Session storage
- `personal_access_tokens` - Sanctum API tokens
- `password_reset_tokens` - Password reset tokens
- `failed_jobs` - Failed job tracking

### Migration Timeline

```
2025-12-31: Initial schema (users, posts, comments, follows, likes)
2026-01-01: Stories system (stories, story_views, story_reactions)
2026-01-02: Admin features (is_admin, is_suspended)
2026-01-02: Messaging system (conversations, messages)
2026-01-03: Notifications system
2026-01-05: Mentions system
2026-01-16: Email verification codes
2026-01-19: Post slugs
2026-02-12: Username system
2026-02-13: Last active tracking
2026-02-19: Message media attachments
2026-02-21: Groups system
2026-02-26: Username tracking
2026-02-28: Story slugs
2026-03-02: Message delivery tracking
2026-03-09: Inactive user reminders
2026-03-10: OAuth password support (nullable)
2026-03-10: Group invite links
2026-03-10: Language preference
2026-03-11: Performance indexes
2026-03-16: Message soft deletes
```

---

## Technology Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 12.x | Web framework |
| PHP | 8.2+ | Server runtime |
| SQLite | Latest | Development database |
| MySQL | 8.0+ | Production database |
| Laravel Sanctum | 4.x | API authentication |
| Laravel Socialite | 5.24 | OAuth (Google) |
| Intervention Image | 3.11 | Image processing |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Blade Templates | - | Primary UI (server-rendered) |
| Vue.js | 3.4 | Component framework |
| Tailwind CSS | 3.2 | Styling |
| Alpine.js | - | Lightweight interactivity |
| Vite | 6.4 | Build tool |
| Axios | 1.11 | HTTP client |
| motion-v | 2.0 | Animations |

### Development Tools

| Tool | Purpose |
|------|---------|
| Laravel Pint | PHP code formatting |
| ESLint | JavaScript linting |
| Prettier | Code formatting |
| Pest PHP | Testing framework |
| JavaScript Obfuscator | Code protection |
| FFmpeg | Video processing |

---

## Architecture

### Request Flow

```
1. HTTP Request → routes/web.php or routes/api.php
2. Middleware Stack (auth, verified, admin, throttle)
3. Controller Action
4. Service Layer (optional: MentionService, RealtimeService)
5. Eloquent Model → Database
6. Return Blade View or JSON Response
```

### Real-Time Architecture

**Polling-Based System** (not WebSockets)

| Feature | Interval | Endpoint |
|---------|----------|----------|
| Chat Messages | 2s | `/chat/{id}/messages` |
| Notifications | 3s | `/api/notifications/unread-count` |
| Online Status | 10s | `/user/online-status/batch` |
| Typing Indicators | 1s | `/chat/{id}/typing-status` |
| Conversations | 2s | `/chat/conversations/updated` |

**Why Polling?**
- Simplicity (no WebSocket server)
- Compatibility (all hosting providers)
- Firewall-friendly (standard HTTP ports)
- Easy scaling (no sticky sessions)

---

## API Endpoints

### Authentication (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | Register new user |
| POST | `/login` | Login |
| POST | `/logout` | Logout |
| POST | `/forgot-password` | Request reset link |
| POST | `/reset-password` | Reset password |
| GET | `/auth/google` | Google OAuth redirect |
| GET | `/auth/google/callback` | Google OAuth callback |

### Posts (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/posts` | Get feed |
| GET | `/posts/{slug}` | Get single post |
| POST | `/posts` | Create post |
| PUT | `/posts/{slug}` | Update post |
| DELETE | `/posts/{slug}` | Delete post |
| POST | `/posts/{id}/like` | Like/unlike post |
| POST | `/posts/{id}/save` | Save/unsave post |
| GET | `/posts/{id}/likers` | Get likers |

### Comments (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/comments` | Create comment |
| DELETE | `/comments/{id}` | Delete comment |
| POST | `/comments/{id}/like` | Like comment |

### Stories (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/stories` | Get stories |
| POST | `/stories` | Create story |
| GET | `/stories/{user}/{slug}` | View story |
| POST | `/stories/{user}/{slug}/react` | React to story |
| DELETE | `/stories/{user}/{slug}` | Delete story |

### Users (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users/{user}` | Get profile |
| GET | `/users/{user}/followers` | Get followers |
| GET | `/users/{user}/following` | Get following |
| POST | `/users/{user}/follow` | Follow/unfollow |
| POST | `/users/{user}/block` | Block user |
| GET | `/explore` | Explore users |
| GET | `/search` | Search users |

### Chat (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/chat` | Get conversations |
| GET | `/chat/{id}` | View conversation |
| POST | `/chat/{id}` | Send message |
| DELETE | `/chat/message/{id}` | Delete message |
| POST | `/chat/{id}/read` | Mark as read |
| POST | `/chat/{id}/typing` | Send typing indicator |

### Groups (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/groups` | Get groups |
| POST | `/groups` | Create group |
| GET | `/groups/{slug}` | View group |
| PUT | `/groups/{slug}` | Update group |
| DELETE | `/groups/{slug}` | Delete group |
| POST | `/groups/{slug}/members` | Add members |
| DELETE | `/groups/{slug}/members/{id}` | Remove member |

### Notifications (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notifications` | Get notifications |
| GET | `/api/notifications/unread-count` | Get unread count |
| POST | `/api/notifications/{id}/read` | Mark as read |
| DELETE | `/api/notifications/{id}` | Delete notification |

### Admin (Admin Only)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin` | Dashboard |
| GET | `/admin/users` | User management |
| DELETE | `/admin/users/{id}` | Delete user |
| GET | `/admin/posts` | Post moderation |
| DELETE | `/admin/posts/{id}` | Delete post |
| GET | `/admin/comments` | Comment moderation |
| DELETE | `/admin/comments/{id}` | Delete comment |

---

## Security Features

### Authentication Security

| Feature | Implementation |
|---------|----------------|
| **Password Hashing** | Bcrypt (12 rounds) |
| **CSRF Protection** | Laravel CSRF tokens |
| **Session Security** | Database sessions, encrypted cookies |
| **Rate Limiting** | 5/min (auth), 30/min (posts), 20/min (comments) |
| **Email Verification** | 6-digit code (10-minute expiry) |
| **Account Suspension** | Admin-controlled flag |

### Input Validation

| Feature | Implementation |
|---------|----------------|
| **Password Strength** | 3 of 5 criteria required |
| **Username Validation** | Alphanumeric + underscore/hyphen |
| **Reserved Usernames** | 40+ blocked names |
| **Disposable Email Blocking** | 16+ domains blocked |
| **File Upload Limits** | 50MB per file, 30 files max |
| **Content Length** | 280 characters (posts/comments) |

### Access Control

| Feature | Implementation |
|---------|----------------|
| **Middleware** | Auth, Admin, Verified, Suspended |
| **Policy Checks** | Owner or admin for deletions |
| **Privacy Controls** | Private accounts, post-level privacy |
| **Block System** | Prevent interactions with blocked users |

---

## Performance Optimizations

### Database

| Optimization | Description |
|--------------|-------------|
| **Indexes** | 20+ indexes on frequently queried columns |
| **Eager Loading** | Prevents N+1 queries |
| **Query Caching** | Database-level query cache |
| **Soft Deletes** | Efficient data retention |

### Frontend

| Optimization | Description |
|--------------|-------------|
| **Vite Build** | Minification, tree-shaking |
| **Code Splitting** | Lazy-loaded components |
| **Asset Optimization** | Compressed images, minified CSS/JS |
| **Browser Caching** | Long-term asset caching |

### Real-Time

| Optimization | Description |
|--------------|-------------|
| **Conditional Polling** | Only poll when relevant page is active |
| **Efficient Queries** | `WHERE id > last_id` pattern |
| **Result Limiting** | Max 20 results per query |
| **Batch Requests** | Combine multiple status checks |

---

## Documentation

| Document | Description |
|----------|-------------|
| [README.md](../README.md) | Project overview and quick start |
| [docs/INSTALLATION.md](INSTALLATION.md) | Detailed setup instructions |
| [docs/ARCHITECTURE.md](ARCHITECTURE.md) | System design and patterns |
| [docs/FEATURES.md](FEATURES.md) | Feature documentation |
| [docs/REALTIME.md](REALTIME.md) | Real-time polling implementation |
| [docs/API.md](API.md) | RESTful API reference |
| [docs/DATABASE.md](DATABASE.md) | Database schema documentation |
| [docs/TECHNOLOGIES.md](TECHNOLOGIES.md) | Technology stack details |
| [docs/SECURITY.md](SECURITY.md) | Security audit and best practices |
| [docs/TROUBLESHOOTING.md](TROUBLESHOOTING.md) | Common issues and solutions |
| [UML.md](../UML.md) | UML diagrams (Class, ERD, Sequence) |

---

## Quick Start

### Installation

```bash
# Clone repository
git clone https://github.com/vd120/nexus.git
cd laravel_project

# Run setup (Linux/macOS)
chmod +x setup.sh
./setup.sh

# Run setup (Windows PowerShell)
.\setup.ps1

# Start development server
php artisan serve
```

### Default Credentials

- **URL:** http://localhost:8000
- **Email:** admin@example.com
- **Password:** admin123

> **Security Notice:** Change the default password immediately!

---

## Contributing

### Areas We Need Help

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

---

## License

This project is proprietary software. All rights reserved.

> **Note:** This project is for demonstration/educational purposes. If you intend to use it commercially, please ensure you have proper licensing for all dependencies and comply with their respective licenses.

---

**Last Updated:** March 16, 2026  
**Laravel Version:** 12.x  
**PHP Version:** 8.2+
