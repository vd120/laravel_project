# Features Documentation

Complete documentation of all Nexus features with detailed flow diagrams.

---

## Table of Contents

1. [Authentication System](#authentication-system)
2. [Posts](#posts)
3. [Comments](#comments)
4. [Stories](#stories)
5. [Chat & Messaging](#chat--messaging)
6. [Groups](#groups)
7. [User Profile & Follow System](#user-profile--follow-system)
8. [Notifications](#notifications)
9. [Admin Panel](#admin-panel)
10. [AI Assistant](#ai-assistant)

---

## Authentication System

### Overview

Nexus provides multiple authentication methods with email verification and account security features.

| Feature | Description |
|---------|-------------|
| **Email/Password** | Traditional registration with 6-digit email verification |
| **Google OAuth** | Single sign-on via Google |
| **Password Reset** | Email-based password recovery |
| **Email Verification** | 6-digit code verification system (10 min expiry) |
| **Account Suspension** | Admin-controlled account suspension |
| **Session Management** | Secure session handling with Remember Me |
| **Password Strength Validation** | Requires 3 of 5 criteria (see below) |
| **Reserved Usernames** | 40+ blocked names (admin, moderator, etc.) |
| **Disposable Email Blocking** | 16+ temporary email domains blocked |

---

### Password Strength Validation

Passwords must meet at least 3 of the following 5 criteria:

| Criteria | Requirement |
|----------|-------------|
| **Length** | Minimum 8 characters |
| **Lowercase** | At least one lowercase letter (a-z) |
| **Uppercase** | At least one uppercase letter (A-Z) |
| **Digit** | At least one number (0-9) |
| **Special Character** | At least one special character (!@#$%^&*, etc.) |

**Implementation:** `RegisterController.php` - Custom validation closure

---

### Reserved Usernames

The following usernames are reserved and cannot be registered:

| Category | Reserved Names |
|----------|---------------|
| **Admin/System** | admin, administrator, root, system, sysadmin |
| **Moderation** | moderator, mod, staff, support, help |
| **Technical** | bot, robot, api, service |
| **Platform** | laravel, social, twitter, x, meta, facebook, instagram, linkedin, youtube, tiktok |
| **Common Variations** | admin1, admin123, administrator1, root1, mod1, moderator1, staff1, support1 |
| **Application** | app, application, platform, site, website, company, official, team, dev, developer |
| **Management** | superuser, superadmin, master, owner, ceo, founder, manager, director |

**Total:** 40+ reserved usernames

**Implementation:** `RegisterController.php` - Custom validation closure

---

### Disposable Email Blocking

The following disposable/temporary email domains are blocked:

| Blocked Domains |
|-----------------|
| 10minutemail.com, guerrillamail.com, mailinator.com, temp-mail.org |
| throwaway.email, yopmail.com, maildrop.cc, tempail.com |
| fakeinbox.com, mailcatch.com, tempinbox.com, dispostable.com |
| 0-mail.com, 20minutemail.com, 33mail.com, anonbox.net |

**Total:** 16+ blocked domains

**Implementation:** `RegisterController.php` - Custom validation closure

---

### Username Change Cooldown

Regular users must wait **3 days** between username changes.

| Rule | Description |
|------|-------------|
| **Cooldown Period** | 259,200 seconds (3 days) |
| **Admin Exemption** | Administrators can change anytime |
| **First Change** | Allowed if never changed before |

**Implementation:** `User.php` - `USERNAME_COOLDOWN_SECONDS` constant, `canChangeUsername()` method

---

### Registration Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        Registration Flow                                 │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Visits     │
│   /register  │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Register    │
│  Page        │
│  • Name      │
│  • Email     │
│  • Password  │
│  • Confirm   │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  Form        │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  RegisterController@store               │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Input                 │  │
│  │    • name: required, max:255      │  │
│  │    • email: required, unique      │  │
│  │    • password: required, min:8    │  │
│  │    • password_confirmation: match │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Create User                    │  │
│  │    • Hash password                │  │
│  │    • Generate username            │  │
│  │    • Create Profile               │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Generate Verification Code     │  │
│  │    • 6-digit random code          │  │
│  │    • Set expiry (10 min)          │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Send Verification Email        │  │
│  │    • HTML + Text templates        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Store Pending User in Session  │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect to │
│  /email/verify│
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  User enters │
│  6-digit code│
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  VerifyCode Request                     │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate code format           │  │
│  │    • 6 digits, numeric            │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check code validity            │  │
│  │    • Match stored code            │  │
│  │    • Check expiry                 │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. If valid:                      │  │
│  │    • Set email_verified_at        │  │
│  │    • Clear verification_code      │  │
│  │    • Login user                   │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. If password is null (OAuth):   │  │
│  │    • Redirect to set-password     │  │
│  │    • Else redirect to home        │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
       ┌───────┴───────┐
       │               │
       ▼               ▼
┌─────────────┐ ┌─────────────┐
│   Success   │ │   Error     │
│  Redirect   │ │  Retry      │
│  to Home    │ │  Page       │
└─────────────┘ └─────────────┘
```

### Registration Code Example

```php
// RegisterController.php
public function store(Request $request)
{
    // 1. Validate input
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'unique:users'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    // 2. Create user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'username' => $this->generateUsername($validated['name']),
    ]);

    // 3. Create profile
    Profile::create(['user_id' => $user->id]);

    // 4. Generate and send verification code
    $verificationCode = $user->generateVerificationCode();
    
    Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

    // 5. Store pending user and redirect
    session(['pending_verification_user_id' => $user->id]);
    
    return redirect()->route('verification.notice');
}
```

---

### Login Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          Login Flow                                      │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Visits     │
│   /login     │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Login Page  │
│  • Email     │
│  • Password  │
│  • Remember  │
│  • Google    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  Credentials │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  LoginController@store                  │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Input                 │  │
│  │    • email: required, exists      │  │
│  │    • password: required           │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check Account Status           │  │
│  │    • is_suspended? → Redirect     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Attempt Authentication         │  │
│  │    • Check credentials            │  │
│  │    • Create session               │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Check Email Verification       │  │
│  │    • Not verified? → Verify page  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Check Password (OAuth users)   │  │
│  │    • No password? → Set password  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 6. Update Last Active             │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect to │
│  Home        │
└──────────────┘
```

---

### Google OAuth Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Google OAuth Flow                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Clicks     │
│  "Login with │
│   Google"    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Redirect to │
│  Google      │
│  /auth/google│
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Google      │
│  OAuth       │
│  Consent     │
│  Screen      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  User Grants │
│  Permission  │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  SocialAuthController@handleGoogleCallback│
│  ┌───────────────────────────────────┐  │
│  │ 1. Get Google User Data           │  │
│  │    • id, name, email, avatar      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Find or Create User            │  │
│  │    • Search by email              │  │
│  │    • Create if not exists         │  │
│  │    • password = null (OAuth)      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Update Avatar (if changed)     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Login User                     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Check Verification Status      │  │
│  │    • Google emails pre-verified   │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 6. Check Password                 │  │
│  │    • No password? → Set password  │  │
│  │    • Else → Home                  │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
       ┌───────┴───────┐
       │               │
       ▼               ▼
┌─────────────┐ ┌─────────────┐
│  Set        │ │  Redirect   │
│  Password   │ │  to Home    │
│  Page       │ │             │
└─────────────┘ └─────────────┘
```

---

## Posts

### Overview

Posts are the primary content type in Nexus. Users can share text, images, and videos with privacy controls.

| Feature | Description |
|---------|-------------|
| **Content** | Text up to 280 characters (optional if media attached) |
| **Media** | Up to 30 images or videos per post (50MB each) |
| **Privacy** | Public or private posts |
| **Reactions** | Like, save, share |
| **Mentions** | @username mentions with notifications |
| **Slug URLs** | 24-character unique slugs for SEO |
| **Video Processing** | FFmpeg thumbnails, compression |

---

### Create Post Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        Create Post Flow                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Clicks     │
│  "New Post"  │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Post Form   │
│  ┌────────┐  │
│  │ Text   │  │
│  │ (280)  │  │
│  └────────┘  │
│  ┌────────┐  │
│  │ Media  │  │
│  │ (30x)  │  │
│  └────────┘  │
│  ☐ Private   │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  POST /posts │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  PostController@store                   │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Request               │  │
│  │    • content: max:280             │  │
│  │    • is_private: boolean          │  │
│  │    • media.*: file, max:50MB      │  │
│  │    • Require content OR media     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Create Post Record             │  │
│  │    • user_id                      │  │
│  │    • content                      │  │
│  │    • is_private                   │  │
│  │    • slug (24-char random)        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Process Media Files            │  │
│  │    For each file:                 │  │
│  │    • Validate type & size         │  │
│  │    • Generate unique filename     │  │
│  │    • Store in storage/public      │  │
│  │    • Create PostMedia record      │  │
│  │    • Video? → Generate thumbnail  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Process Mentions               │  │
│  │    • Parse @username from content │  │
│  │    • Find mentioned users         │  │
│  │    • Create Mention records       │  │
│  │    • Create Notifications         │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back with   │
│  Success     │
└──────────────┘
```

### Create Post Code Example

```php
// PostController.php
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
    ]);

    // Create post with unique slug
    $post = $request->user()->posts()->create([
        'content' => $validated['content'] ?? '',
        'is_private' => $validated['is_private'] ?? false,
        'slug' => Str::random(24),
    ]);

    // Process media
    if ($request->hasFile('media')) {
        $sortOrder = 1;
        
        foreach ($request->file('media') as $file) {
            $path = $file->store('posts', 'public');
            
            $mediaType = str_starts_with($file->getMimeType(), 'video') 
                ? 'video' : 'image';
            
            $thumbnail = null;
            if ($mediaType === 'video') {
                $thumbnail = $this->generateVideoThumbnail($file);
            }
            
            $post->media()->create([
                'media_type' => $mediaType,
                'media_path' => $path,
                'media_thumbnail' => $thumbnail,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    // Process mentions
    if ($validated['content']) {
        app(MentionService::class)->processMentions(
            $post, 
            $validated['content'], 
            auth()->id()
        );
    }

    return redirect()->back()->with('success', 'Post created!');
}

private function generateVideoThumbnail($file)
{
    $videoPath = $file->getRealPath();
    $thumbnailPath = 'posts/' . Str::random(40) . '_thumb.jpg';
    
    // Extract frame at 1 second using FFmpeg
    $command = sprintf(
        'ffmpeg -i %s -ss 00:00:01 -vframes 1 %s',
        escapeshellarg($videoPath),
        escapeshellarg(storage_path('app/public/' . $thumbnailPath))
    );
    
    exec($command);
    
    return file_exists(storage_path('app/public/' . $thumbnailPath))
        ? $thumbnailPath : null;
}
```

---

### Post Feed Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         Post Feed Flow                                   │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Visits     │
│   Home (/)   │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  PostController@index                   │
│  ┌───────────────────────────────────┐  │
│  │ Build Query:                      │  │
│  │                                   │  │
│  │ Include posts from:               │  │
│  │ • User's own posts                │  │
│  │ • Public accounts                 │  │
│  │ • Followed users (even private)   │  │
│  │                                   │  │
│  │ Exclude:                          │  │
│  │ • Blocked users                   │  │
│  │ • Unfollowed private accounts     │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  Eloquent Query with Eager Loading      │
│  ┌───────────────────────────────────┐  │
│  │ Post::with([                      │  │
│  │   'user.profile',                 │  │
│  │   'media',                        │  │
│  │   'likes',                        │  │
│  │   'comments.user.profile'         │  │
│  │ ])                                │  │
│  │ ->whereHas('user', ...)           │  │
│  │ ->latest()                        │  │
│  │ ->paginate(15)                    │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Render      │
│  Inertia     │
│  Page        │
│  (Vue.js)    │
└──────────────┘
```

---

### Like Post Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         Like Post Flow                                   │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Clicks     │
│  Like Button │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /posts/{id}/│
│  like        │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  PostController@like                    │
│  ┌───────────────────────────────────┐  │
│  │ 1. Find Post                      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check Existing Like            │  │
│  │    Like::where('user_id', auth)   │  │
│  │          ->where('post_id', post) │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Toggle Like                    │  │
│  │    If exists:                     │  │
│  │      → Delete (Unlike)            │  │
│  │    If not exists:                 │  │
│  │      → Create (Like)              │  │
│  │      → Create Notification        │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back        │
└──────────────┘
```

### Like Post Code Example

```php
// PostController.php
public function like(Post $post)
{
    $user = auth()->user();
    
    // Check if already liked
    $like = $post->likes()
        ->where('user_id', $user->id)
        ->first();
    
    if ($like) {
        // Unlike
        $like->delete();
        $liked = false;
    } else {
        // Like
        $post->likes()->create(['user_id' => $user->id]);
        $liked = true;
        
        // Create notification (if not own post)
        if ($post->user_id !== $user->id) {
            Notification::create([
                'user_id' => $post->user_id,
                'type' => 'like',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'post_id' => $post->id,
                ],
                'related_id' => $post->id,
                'related_type' => Post::class,
            ]);
        }
    }
    
    return back()->with('success', $liked ? 'Post liked!' : 'Post unliked.');
}
```

---

### Save Post Flow

```
┌──────────────┐
│   User       │
│   Clicks     │
│  Save Button │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /posts/{id}/│
│  save        │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  PostController@save                    │
│  ┌───────────────────────────────────┐  │
│  │ 1. Find Post                      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check Existing Save            │  │
│  │    SavedPost::where('user_id')    │  │
│  │               ->where('post_id')  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Toggle Save                    │  │
│  │    If exists:                     │  │
│  │      → Delete (Unsave)            │  │
│  │    If not exists:                 │  │
│  │      → Create (Save)              │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back        │
└──────────────┘
```

---

### Delete Post Flow

```
┌──────────────┐
│   User       │
│   Clicks     │
│  Delete      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Confirm     │
│  Dialog      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  DELETE      │
│  /posts/{id} │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  PostController@destroy                 │
│  ┌───────────────────────────────────┐  │
│  │ 1. Authorization Check            │  │
│  │    • Post owner OR                │  │
│  │    • Admin                        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Delete Media Files             │  │
│  │    For each media:                │  │
│  │    • Delete file from storage     │  │
│  │    • Delete thumbnail (if video)  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Delete Post                    │  │
│  │    (Cascades to: media, likes,    │  │
│  │     comments, saved_posts)        │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back with   │
│  Success     │
└──────────────┘
```

---

## Comments

### Overview

Comments support nested replies (threaded comments) with likes and mentions.

| Feature | Description |
|---------|-------------|
| **Nested Replies** | Reply to comments (threaded) |
| **Likes** | Like comments |
| **Mentions** | @username in comments |
| **Notifications** | Notify post owner and mentioned users |
| **Delete** | Comment owner, post owner, or admin |

---

### Create Comment Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Create Comment Flow                                │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Types      │
│   Comment    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Comment     │
│  Form        │
│  • Content   │
│  • (Reply)   │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  POST        │
│  /comments   │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  CommentController@store                │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Request               │  │
│  │    • post_id: required, exists    │  │
│  │    • content: required, max:280   │  │
│  │    • parent_id: nullable          │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Verify Post Access             │  │
│  │    • Check post privacy           │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Create Comment                 │  │
│  │    • user_id                      │  │
│  │    • post_id                      │  │
│  │    • content                      │  │
│  │    • parent_id (if reply)         │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Process Mentions               │  │
│  │    • Parse @username              │  │
│  │    • Create Mention records       │  │
│  │    • Create Notifications         │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Notify Post Owner              │  │
│  │    (If not own post)              │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back with   │
│  Success     │
└──────────────┘
```

### Create Comment Code Example

```php
// CommentController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'post_id' => ['required', 'exists:posts,id'],
        'content' => ['required', 'string', 'max:280'],
        'parent_id' => ['nullable', 'exists:comments,id'],
    ]);

    $post = Post::findOrFail($validated['post_id']);

    // Verify parent comment if replying
    if ($validated['parent_id']) {
        $parentComment = Comment::findOrFail($validated['parent_id']);
        abort_if($parentComment->post_id !== $post->id, 403);
    }

    // Create comment
    $comment = $post->comments()->create([
        'user_id' => auth()->id(),
        'parent_id' => $validated['parent_id'],
        'content' => $validated['content'],
    ]);

    // Process mentions
    app(MentionService::class)->processMentions(
        $comment,
        $validated['content'],
        auth()->id()
    );

    // Notify post owner
    if ($post->user_id !== auth()->id()) {
        Notification::create([
            'user_id' => $post->user_id,
            'type' => 'comment',
            'data' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'post_id' => $post->id,
                'comment_id' => $comment->id,
            ],
            'related_id' => $post->id,
            'related_type' => Post::class,
        ]);
    }

    return back()->with('success', 'Comment added!');
}
```

---

### Delete Comment Flow

```
┌──────────────┐
│   User       │
│   Clicks     │
│  Delete      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  DELETE      │
│  /comments/  │
│  {id}        │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  CommentController@destroy              │
│  ┌───────────────────────────────────┐  │
│  │ Authorization Check:              │  │
│  │ Can delete if:                    │  │
│  │ • Comment owner                   │  │
│  │ • Post owner                      │  │
│  │ • Admin                           │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ Delete Comment                    │  │
│  │ (Cascades to replies & likes)     │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back        │
└──────────────┘
```

---

## Stories

### Overview

Stories are ephemeral content that expires after 24 hours with view tracking and reactions.

| Feature | Description |
|---------|-------------|
| **24-Hour Expiry** | Auto-delete after 24 hours |
| **Media Types** | Images and videos |
| **View Tracking** | See who viewed your story |
| **Reactions** | Emoji reactions |
| **Video Processing** | Trim to 60 seconds max |
| **Privacy** | Private accounts visible to followers only |

---

### Create Story Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Create Story Flow                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Clicks     │
│  "New Story" │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Story       │
│  Create Page │
│  • Upload    │
│  • Caption   │
│  (optional)  │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Select      │
│  Media       │
│  (Image/     │
│  Video)      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  POST        │
│  /stories    │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  StoryController@store                  │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Request               │  │
│  │    • media: required, file        │  │
│  │    • content: nullable, max:280   │  │
│  │    • max: 50MB                    │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Determine Media Type           │  │
│  │    • Image or Video               │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Process Video (if video)       │  │
│  │    • Check duration               │  │
│  │    • Trim to 60s if longer        │  │
│  │    • Using FFmpeg                 │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Store Media                    │  │
│  │    • Generate unique filename     │  │
│  │    • Save to storage/public       │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Create Story Record            │  │
│  │    • user_id                      │  │
│  │    • slug (24-char)               │  │
│  │    • media_type                   │  │
│  │    • media_path                   │  │
│  │    • content (optional)           │  │
│  │    • expires_at (24 hours)        │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect to │
│  Story Index │
└──────────────┘
```

### Create Story Code Example

```php
// StoryController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'media' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
        'content' => ['nullable', 'string', 'max:280'],
    ]);

    $file = $validated['media'];
    $mediaType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

    // Process video if needed
    if ($mediaType === 'video') {
        $path = $this->processVideo($file);
    } else {
        $path = $file->store('stories', 'public');
    }

    // Create story (expires in 24 hours)
    $story = $request->user()->stories()->create([
        'slug' => Str::random(24),
        'media_type' => $mediaType,
        'media_path' => $path,
        'content' => $validated['content'] ?? null,
        'expires_at' => now()->addHours(24),
    ]);

    return redirect()->route('stories.index')
        ->with('success', 'Story created!');
}

private function processVideo($file)
{
    $videoPath = $file->getRealPath();
    $outputPath = 'stories/' . Str::random(40) . '.mp4';
    $outputFullPath = storage_path('app/public/' . $outputPath);

    // Get video duration
    $duration = $this->getVideoDuration($videoPath);

    if ($duration > 60) {
        // Trim to 60 seconds
        $command = sprintf(
            'ffmpeg -i %s -t 60 -c copy %s',
            escapeshellarg($videoPath),
            escapeshellarg($outputFullPath)
        );
        exec($command);
    } else {
        // Just copy
        $command = sprintf(
            'ffmpeg -i %s -c copy %s',
            escapeshellarg($videoPath),
            escapeshellarg($outputFullPath)
        );
        exec($command);
    }

    return $outputPath;
}

private function getVideoDuration($path)
{
    $command = sprintf(
        'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
        escapeshellarg($path)
    );
    return (float) exec($command);
}
```

---

### View Story Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        View Story Flow                                   │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Clicks     │
│  Story       │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  GET         │
│  /stories/   │
│  {user}/     │
│  {story}     │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  StoryController@show                   │
│  ┌───────────────────────────────────┐  │
│  │ 1. Find Story by User & Slug      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check Authorization            │  │
│  │    • Story belongs to user        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Check Expiry                   │  │
│  │    • expires_at < now? → 404      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Check Privacy                  │  │
│  │    • Private account?             │  │
│  │    • Is follower OR owner?        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Track View                     │  │
│  │    • If not own story             │  │
│  │    • Create StoryView record      │  │
│  │    • Increment view count         │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Render      │
│  Story View  │
│  (Full-screen)│
└──────────────┘
```

---

### Story Reaction Flow

```
┌──────────────┐
│   User       │
│   Selects    │
│  Emoji       │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /stories/   │
│  {id}/react  │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  StoryController@react                  │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Reaction              │  │
│  │    • reaction: string, max:10     │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. UpdateOrCreate Reaction        │  │
│  │    StoryReaction::updateOrCreate( │  │
│  │      [user_id, story_id],         │  │
│  │      ['reaction_type' => $emoji]  │  │
│  │    )                              │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back        │
└──────────────┘
```

---

### View Story Viewers Flow

```
┌──────────────┐
│   Story      │
│   Owner      │
│   Clicks     │
│  "Viewers"   │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  GET         │
│  /stories/   │
│  {id}/viewers│
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  StoryController@viewers                │
│  ┌───────────────────────────────────┐  │
│  │ 1. Verify Story Ownership         │  │
│  │    • Only owner can see viewers   │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Get Story Views                │  │
│  │    • With user profiles           │  │
│  │    • Ordered by time              │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Render      │
│  Viewers     │
│  List        │
└──────────────┘
```

---

### Cleanup Expired Stories

```
┌─────────────────────────────────────────────────────────────────────────┐
│                   Expired Stories Cleanup (Hourly)                       │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│  Scheduled   │
│  Task        │
│  (Hourly)    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Artisan     │
│  Command:    │
│  stories:    │
│  cleanup     │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  CleanupExpiredStories Command          │
│  ┌───────────────────────────────────┐  │
│  │ 1. Find Expired Stories           │  │
│  │    Story::where('expires_at',     │  │
│  │           '<', now())->get()       │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. For Each Story:                │  │
│  │    • Delete media file            │  │
│  │    • Delete thumbnail (if video)  │  │
│  │    • Delete story record          │  │
│  │    (Cascades to views/reactions)  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Log Count                      │  │
│  │    "Deleted X expired stories"    │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

### Cleanup Command Code

```php
// CleanupExpiredStories.php
public function handle(): int
{
    $expiredStories = Story::where('expires_at', '<', now())->get();

    foreach ($expiredStories as $story) {
        // Delete media file
        Storage::disk('public')->delete($story->media_path);
        
        // Delete thumbnail if video
        if ($story->media_thumbnail) {
            Storage::disk('public')->delete($story->media_thumbnail);
        }
        
        // Delete story (cascades)
        $story->delete();
    }

    $this->info("Deleted {$expiredStories->count()} expired stories.");
    
    return Command::SUCCESS;
}
```

---

## Chat & Messaging

### Overview

Real-time chat with direct messages and group conversations.

| Feature | Description |
|---------|-------------|
| **Direct Messages** | One-on-one conversations |
| **Group Chat** | Multiple participants via groups |
| **Media Messages** | Images, videos, files |
| **Read Receipts** | Track message read status |
| **Typing Indicators** | Real-time typing status |
| **Message Actions** | Delete for me/everyone |
| **Online Status** | See who's online |
| **Message Deletion Options** | Delete "for me" or "for everyone" |
| **Conversation Clearing** | Force delete all messages |

---

### Message Deletion Options

Users can delete messages with two options:

| Option | Description | Permissions |
|--------|-------------|-------------|
| **Delete for Me** | Message hidden only for current user | Any participant |
| **Delete for Everyone** | Message soft-deleted for all participants | Sender only |

**Implementation Details:**
- **Delete for Me:** Adds user ID to `deleted_for` JSON column
- **Delete for Everyone:** Sets `deleted_by_sender = true` and soft-deletes the message
- Other participants see "message deleted" placeholder for sender-deleted messages

**Implementation:** `ChatController.php` - `destroy()` method

---

### Conversation Clearing

Users can clear entire conversations, permanently removing all messages.

| Feature | Description |
|---------|-------------|
| **Action** | Force delete all messages in conversation |
| **Effect** | Permanently removes messages (not soft-delete) |
| **Conversation** | Remains in list with `last_message_at = null` |
| **UI Behavior** | Shows empty chat instead of "message deleted" placeholders |

**Implementation:** `ChatController.php` - `clearChat()` method

---

### Group Invite Link System

Groups have slug-based invite links for easy joining.

| Feature | Description |
|---------|-------------|
| **Slug** | Unique identifier for group URL |
| **Invite Link** | Unique token-based link (e.g., `/join/abc123xyz`) |
| **Routes** | `/groups/accept-invite/{inviteLink}`, `/join/{inviteLink}` |
| **Regeneration** | Admins can regenerate invite links |

**Implementation:** `GroupController.php` - `acceptInvite()`, `joinViaInvite()`, `regenerateInvite()`

---

### Conversation Types

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      Conversation Types                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────┐
│     Direct Message (DM)         │
├─────────────────────────────────┤
│  • Between 2 users              │
│  • user1_id, user2_id           │
│  • is_group = false             │
│  • slug: unique identifier      │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│       Group Conversation        │
├─────────────────────────────────┤
│  • Multiple participants        │
│  • Linked to Group model        │
│  • is_group = true              │
│  • group_id reference           │
│  • Auto-created with group      │
└─────────────────────────────────┘
```

---

### Get Conversations Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     Get Conversations Flow                               │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Visits     │
│  /chat       │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  GET         │
│  /chat/      │
│  conversations│
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  ChatController@getConversations        │
│  ┌───────────────────────────────────┐  │
│  │ Find conversations where:         │  │
│  │ • user1_id = current user         │  │
│  │ • user2_id = current user         │  │
│  │ • OR member of group conversation │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ Eager Load:                       │  │
│  │ • latestMessage.sender            │  │
│  │ • user1.profile                   │  │
│  │ • user2.profile                   │  │
│  │ • group (if group chat)           │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ Map to Response:                  │  │
│  │ • display_name                    │  │
│  │ • display_avatar                  │  │
│  │ • latest_message                  │  │
│  │ • unread_count                    │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  JSON        │
│  Response    │
└──────────────┘
```

### Get Conversations Code

```php
// ChatController.php
public function getConversations()
{
    $user = auth()->user();

    $conversations = Conversation::where('user1_id', $user->id)
        ->orWhere('user2_id', $user->id)
        ->orWhereHas('group.members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['latestMessage.sender', 'user1.profile', 'user2.profile', 'group'])
        ->latest('last_message_at')
        ->get()
        ->map(function ($conversation) use ($user) {
            return [
                'id' => $conversation->id,
                'slug' => $conversation->slug,
                'is_group' => $conversation->is_group,
                'display_name' => $conversation->display_name,
                'display_avatar' => $conversation->display_avatar,
                'latest_message' => $conversation->latestMessage,
                'unread_count' => $conversation->unread_count,
                'updated_at' => $conversation->updated_at,
            ];
        });

    return response()->json($conversations);
}
```

---

### Send Message Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Send Message Flow                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Types      │
│   Message    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Optional:   │
│  Attach      │
│  Media       │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  POST        │
│  /chat/{id}  │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  ChatController@store                   │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Membership            │  │
│  │    • User is conversation member  │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Validate Request               │  │
│  │    • content OR media required    │  │
│  │    • type: text/image/file        │  │
│  │    • media: max 50MB              │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Create Message                 │  │
│  │    • conversation_id              │  │
│  │    • sender_id                    │  │
│  │    • content                      │  │
│  │    • type                         │  │
│  │    • media_path (if attachment)   │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Update Conversation            │  │
│  │    • last_message_at = now()      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Create Notifications           │  │
│  │    • For each recipient           │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 6. Broadcast Event                │  │
│  │    • new-message                  │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  JSON        │
│  Response    │
│  (Message)   │
└──────────────┘
```

---

### Typing Indicator Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     Typing Indicator Flow                                │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Starts     │
│   Typing     │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Debounced   │
│  Event       │
│  (Vue.js)    │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /chat/{id}/ │
│  typing      │
│  {is_typing} │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  ChatController@sendTypingIndicator     │
│  ┌───────────────────────────────────┐  │
│  │ Store in Cache:                   │  │
│  │ Key: typing:{conv_id}:{user_id}   │  │
│  │ Value: is_typing (bool)           │  │
│  │ TTL: 5 seconds                    │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Broadcast   │
│  to Other    │
│  Participants│
└──────────────┘

┌──────────────┐
│  Recipients  │
│  Poll/       │
│  Listen for  │
│  Typing      │
│  Status      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Display     │
│  "Typing..." │
│  Indicator   │
└──────────────┘
```

### Typing Indicator Code

```php
// ChatController.php
public function sendTypingIndicator(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'is_typing' => ['boolean'],
    ]);

    // Store in cache (expires in 5 seconds)
    Cache::put(
        "typing:{$conversation->id}:" . auth()->id(),
        $validated['is_typing'],
        5
    );

    return response()->json(['success' => true]);
}

public function getTypingStatus(Conversation $conversation)
{
    $recipients = $conversation->getRecipients(auth()->id());

    $typingUsers = [];
    foreach ($recipients as $recipientId) {
        $isTyping = Cache::get("typing:{$conversation->id}:{$recipientId}");
        if ($isTyping) {
            $user = User::find($recipientId);
            $typingUsers[] = [
                'id' => $user->id,
                'name' => $user->name,
            ];
        }
    }

    return response()->json([
        'is_typing' => count($typingUsers) > 0,
        'typing_users' => $typingUsers,
    ]);
}
```

---

### Read Receipts Flow

```
┌──────────────┐
│   User       │
│   Opens      │
│  Chat        │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /chat/{id}/ │
│  mark-read   │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  ChatController@markAsRead              │
│  ┌───────────────────────────────────┐  │
│  │ Mark all unread messages as read  │  │
│  │ Message::where('conversation_id') │  │
│  │          ->where('sender_id', '!=')│  │
│  │          ->whereNull('read_at')   │  │
│  │          ->update(['read_at' =>   │  │
│  │              now()])              │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Broadcast   │
│  Read Status │
└──────────────┘
```

---

### Delete Message Flow

```
┌──────────────┐
│   User       │
│   Clicks     │
│  Delete      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Options:    │
│  • Delete    │
│    for Me    │
│  • Delete    │
│    for       │
│    Everyone  │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  DELETE      │
│  /chat/      │
│  message/{id}│
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  ChatController@destroy                 │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Membership            │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check Delete Type              │  │
│  │    "Delete for Everyone":         │  │
│  │      • Must be sender             │  │
│  │      → Hard delete message        │  │
│  │                                   │  │
│  │    "Delete for Me":               │  │
│  │      • Any member                 │  │
│  │      → Add to deleted_for array   │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  JSON        │
│  Response    │
└──────────────┘
```

### Delete Message Code

```php
// ChatController.php
public function destroy(Request $request, Message $message)
{
    $validated = $request->validate([
        'delete_for' => ['in:me,everyone'],
    ]);

    $conversation = $message->conversation;
    abort_unless($conversation->isMember(auth()->id()), 403);

    if ($validated['delete_for'] === 'everyone' && $message->sender_id === auth()->id()) {
        // Delete for everyone (hard delete)
        $message->delete();
    } else {
        // Delete for me only
        $deletedFor = $message->deleted_for ?? [];
        $deletedFor[] = auth()->id();

        $message->update([
            'deleted_for' => array_unique($deletedFor),
        ]);
    }

    return response()->json(['success' => true]);
}
```

---

## Groups

### Overview

Create and manage communities with members, roles, and group chat.

| Feature | Description |
|---------|-------------|
| **Create Groups** | Public or private communities |
| **Member Roles** | Admin and member permissions |
| **Invite Links** | Shareable links for joining |
| **Group Chat** | Dedicated conversation |
| **Member Management** | Add, remove, promote members |

---

### Create Group Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       Create Group Flow                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User       │
│   Clicks     │
│  "Create     │
│  Group"      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Group Form  │
│  • Name      │
│  • Desc      │
│  • Avatar    │
│  • Privacy   │
│  • Members   │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Submit      │
│  POST        │
│  /groups     │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  GroupController@store                  │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Request               │  │
│  │    • name: required, max:255      │  │
│  │    • description: nullable        │  │
│  │    • is_private: boolean          │  │
│  │    • avatar: nullable, image      │  │
│  │    • member_ids: array            │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Create Group                   │  │
│  │    • Generate slug                │  │
│  │    • Generate invite_link         │  │
│  │    • Set creator_id               │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Upload Avatar (if provided)    │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Add Creator as Admin           │  │
│  │    GroupMember::create([          │  │
│  │      'user_id' => creator,        │  │
│  │      'role' => 'admin'            │  │
│  │    ])                             │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Add Other Members              │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 6. Create Group Conversation      │  │
│  │    Conversation::createGroup()    │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect to │
│  Group Page  │
└──────────────┘
```

---

### Join Group via Invite Flow

```
┌──────────────┐
│   User       │
│   Clicks     │
│  Invite Link │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  GET         │
│  /join/      │
│  {inviteLink}│
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  GroupController@joinViaInvite          │
│  ┌───────────────────────────────────┐  │
│  │ 1. Find Group by Invite Link      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check if Already Member        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Add as Member                  │  │
│  │    GroupMember::create([          │  │
│  │      'user_id' => auth()->id(),   │  │
│  │      'role' => 'member'           │  │
│  │    ])                             │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Add to Group Conversation      │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 5. Create System Message          │  │
│  │    "{user} joined the group"      │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect to │
│  Group Page  │
└──────────────┘
```

---

## User Profile & Follow System

### Overview

User profiles with customizable information and a follow system for content filtering.

| Feature | Description |
|---------|-------------|
| **Profile** | Avatar, cover, bio, social links |
| **Follow** | Follow/unfollow users |
| **Privacy** | Private accounts require approval |
| **Block** | Block unwanted users |
| **Explore** | Discover new users |

---

### Follow/Unfollow Flow

```
┌──────────────┐
│   User       │
│   Clicks     │
│  Follow      │
│  Button      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /users/{id}/│
│  follow      │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  UserController@follow                  │
│  ┌───────────────────────────────────┐  │
│  │ 1. Check Self-Follow              │  │
│  │    (Cannot follow self)           │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Check Existing Follow          │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Toggle Follow                  │  │
│  │    If following:                  │  │
│  │      → Delete (Unfollow)          │  │
│  │    If not following:              │  │
│  │      → Create (Follow)            │  │
│  │      → Create Notification        │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Redirect    │
│  Back        │
└──────────────┘
```

### Follow Code Example

```php
// UserController.php
public function follow(User $user)
{
    $currentUser = auth()->user();

    // Cannot follow self
    abort_if($currentUser->id === $user->id, 403);

    // Check if already following
    $follow = $currentUser->following()
        ->where('followed_id', $user->id)
        ->first();

    if ($follow) {
        // Unfollow
        $follow->delete();
        $following = false;
    } else {
        // Follow
        $currentUser->following()->create([
            'followed_id' => $user->id,
        ]);
        $following = true;

        // Create notification
        Notification::create([
            'user_id' => $user->id,
            'type' => 'follow',
            'data' => [
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->name,
            ],
        ]);
    }

    return back()->with('success', 
        $following ? 'Following!' : 'Unfollowed.'
    );
}
```

---

## Notifications

### Overview

Real-time notifications for all user activities.

| Type | Trigger |
|------|---------|
| **like** | Someone likes your post |
| **comment** | Someone comments on your post |
| **follow** | Someone follows you |
| **mention** | Someone mentions you |
| **message** | New chat message |

---

### Notification Creation Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    Notification Creation Flow                            │
└─────────────────────────────────────────────────────────────────────────┘

Event Trigger (like, comment, follow, mention, message)
       │
       ▼
┌─────────────────────────────────────────┐
│  Notification::create([                 │
│    'user_id' => $recipientId,           │
│    'type' => 'like',                    │
│    'data' => [                          │
│      'user_id' => $actorId,             │
│      'user_name' => $actorName,         │
│      'post_id' => $postId,              │
│    ],                                   │
│    'related_id' => $postId,             │
│    'related_type' => Post::class,       │
│  ])                                     │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Real-time   │
│  Broadcast   │
│  (optional)  │
└──────────────┘
```

---

## Admin Panel

### Overview

Admin panel for platform management and content moderation.

| Feature | Description |
|---------|-------------|
| **Dashboard** | Platform statistics |
| **User Management** | View, edit, suspend users |
| **Content Moderation** | Delete posts, comments, stories |
| **Admin Creation** | Create new admin accounts |

---

### Admin Dashboard Flow

```
┌──────────────┐
│   Admin      │
│   Visits     │
│  /admin      │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  AdminMiddleware                        │
│  ┌───────────────────────────────────┐  │
│  │ Check is_admin = true             │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  AdminController@dashboard              │
│  ┌───────────────────────────────────┐  │
│  │ Get Statistics:                   │  │
│  │ • Total users                     │  │
│  │ • Total posts                     │  │
│  │ • Total comments                  │  │
│  │ • Total stories                   │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Render      │
│  Dashboard   │
│  View        │
└──────────────┘
```

---

## AI Assistant

### Overview

Menu-based AI chatbot for user assistance.

| Feature | Description |
|---------|-------------|
| **Chat Interface** | Conversational UI |
| **Menu Options** | Pre-defined prompts |
| **Context Aware** | Remembers conversation |

---

### AI Chat Flow

```
┌──────────────┐
│   User       │
│   Visits     │
│  /ai         │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  AI Chat     │
│  Interface   │
│  • Menu      │
│  • Chat      │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  User Sends  │
│  Message     │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  POST        │
│  /ai/chat    │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  AiController@chat                      │
│  ┌───────────────────────────────────┐  │
│  │ 1. Validate Message               │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 2. Process with AI Service        │  │
│  │    (Integration point for AI API) │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 3. Store Conversation             │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 4. Return Response                │  │
│  └───────────────────────────────────┘  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌──────────────┐
│  Display     │
│  AI Response │
└──────────────┘
```

---

## Next Steps

Continue reading:

- [API Reference](API.md) - RESTful API documentation
- [Database Schema](DATABASE.md) - Table definitions
- [Frontend Guide](FRONTEND.md) - Vue.js architecture
