# Nexus - Complete Features Documentation

Comprehensive documentation of all Nexus features with detailed explanations, workflows, and technical implementations.

---

## Table of Contents

1. [Authentication System](#1-authentication-system)
2. [Posts & Content](#2-posts--content)
3. [Stories](#3-stories)
4. [Comments & Reactions](#4-comments--reactions)
5. [Chat & Messaging](#5-chat--messaging)
6. [Groups](#6-groups)
7. [User Profile & Social](#7-user-profile--social)
8. [Notifications](#8-notifications)
9. [Admin Panel](#9-admin-panel)
10. [AI Assistant](#10-ai-assistant)
11. [Push Notifications](#11-push-notifications)
12. [Activity & Analytics](#12-activity--analytics)
13. [Life Events](#13-life-events)
14. [Hashtags & Discovery](#14-hashtags--discovery)
15. [Content Moderation](#15-content-moderation)

---

## 1. Authentication System

### Overview

Nexus provides multiple authentication methods with robust security features including email verification, OAuth integration, and account protection mechanisms.

### 1.1 Registration

**Features:**
- Email/password registration with 6-digit verification code
- Google OAuth single sign-on
- Password strength validation (3 of 5 criteria)
- Reserved username blocking (50 names)
- Disposable email domain blocking (16 domains)
- Automatic profile creation
- Username generation from name

**Password Requirements (3 of 5):**
- Minimum 8 characters
- At least one lowercase letter (a-z)
- At least one uppercase letter (A-Z)
- At least one digit (0-9)
- At least one special character (!@#$%^&*)

**Reserved Usernames:**
```
admin, administrator, root, system, sysadmin
moderator, mod, staff, support, help
bot, robot, api, service
laravel, social, twitter, x, meta, facebook
instagram, linkedin, youtube, tiktok
+ 36 variations (admin1, admin123, etc.)
```

**Blocked Email Domains:**
```
10minutemail.com, guerrillamail.com, mailinator.com
temp-mail.org, throwaway.email, yopmail.com
maildrop.cc, tempail.com, fakeinbox.com
+ 8 additional disposable email providers
```

**Registration Flow:**
1. User fills registration form (name, email, password, password confirmation)
2. Server validates input and checks reserved usernames/blocked domains
3. User account created with hashed password (bcrypt, 12 rounds)
4. Profile automatically created
5. 6-digit verification code generated (10-minute expiry)
6. Verification email sent
7. User redirected to verification page
8. User enters code
9. Account verified and logged in
10. OAuth users without password redirected to set-password page

**Implementation Files:**
- `app/Http/Controllers/Auth/RegisterController.php`
- `app/Http/Controllers/Auth/SocialAuthController.php`
- `app/Mail/VerificationCodeMail.php`
- `resources/views/auth/register.blade.php`
- `resources/js/legacy/auth-register.js`

### 1.2 Login

**Features:**
- Email/password authentication
- Google OAuth login
- Remember me functionality
- Account suspension check
- Email verification requirement
- Rate limiting (5 attempts/minute)
- Session regeneration on login

**Login Flow:**
1. User enters credentials
2. Server validates credentials
3. Rate limit checked (middleware, 5 attempts/minute)
4. If valid: session created
5. Account suspension checked (after login)
6. If suspended: logout and redirect to suspended page
7. Email verification status checked
8. If not verified: redirect to verification page
9. If verified: redirect to home
10. OAuth users without password: will be prompted to set password later

**Implementation Files:**
- `app/Http/Controllers/Auth/LoginController.php`
- `resources/views/auth/login.blade.php`
- `resources/js/legacy/auth-login.js`

### 1.3 Email Verification

**Features:**
- 6-digit numeric code
- 10-minute code expiry
- Rate limiting (3 attempts/hour)
- Resend verification email
- Code invalidation after use
- Required before accessing platform features

**Verification Flow:**
1. User accesses verification page
2. System sends 6-digit code via email
3. User enters code
4. Server validates code format (6 digits)
5. Server checks code match and expiry
6. If valid: email_verified_at set, code cleared
7. If OAuth without password: redirect to set-password
8. If has password: redirect to home

**Implementation Files:**
- `routes/web.php` (verification routes)
- `app/Models/User.php` (verifyCode method)
- `resources/views/auth/verify-email.blade.php`
- `resources/js/legacy/auth-verify-email.js`

### 1.4 Google OAuth

**Features:**
- OAuth 2.0 authentication
- Automatic account creation
- Avatar sync from Google
- Email verification required for new users
- Optional password setup for OAuth accounts

**OAuth Flow:**
1. User clicks "Login with Google"
2. Redirected to Google consent screen
3. User grants permission
4. Google redirects back with code
5. Server exchanges code for user data
6. Find user by email or create new
7. If new: generate username, create account (needs verification)
8. If existing unverified: redirect to verification
9. If existing verified: login user
10. Avatar updated if changed
11. Verified users without password: redirect to set-password
12. New/unverified users: redirect to email verification

**Configuration:**
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

**Implementation Files:**
- `app/Http/Controllers/Auth/SocialAuthController.php`
- `config/services.php` (Google config)

### 1.5 Password Reset

**Features:**
- Email-based password reset
- Secure token generation
- 60-minute token expiry
- Rate limiting (60 seconds between requests)
- Token invalidation after use

**Reset Flow:**
1. User requests reset link
2. Server generates secure token
3. Reset email sent with link
4. User clicks link
5. Token validated
6. User enters new password
7. Password hashed and saved
8. Token invalidated

**Implementation Files:**
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- `app/Http/Controllers/Auth/ResetPasswordController.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/reset-password.blade.php`

### 1.6 Account Security

**Username Change Cooldown:**
- Regular users: 3-day cooldown between changes
- Admins: No cooldown (unlimited changes)
- First change: Always allowed

**Account Suspension:**
- Admin-controlled suspension
- Suspended users cannot login
- Suspended page shown on login attempt
- All sessions terminated on suspension

**Session Management:**
- Database-backed sessions
- 2-hour session lifetime
- HTTP-only cookies
- Secure cookies (production)
- SameSite=lax protection
- Session regeneration on privilege changes

---

## 2. Posts & Content

### Overview

Posts are the primary content type in Nexus, supporting text, images, videos, mentions, hashtags, and privacy controls.

### 2.1 Create Post

**Features:**
- Text content (max 280 characters, optional if media attached)
- Up to 30 media files per post (images/videos)
- 50MB max per file
- Supported formats: JPG, PNG, GIF, WEBP, MP4, MOV, AVI, WEBM
- Public or private privacy setting
- Automatic slug generation (24-character unique)
- @mention processing with notifications
- #hashtag extraction
- Video thumbnail generation (FFmpeg)
- Soft deletes

**Post Creation Flow:**
1. User clicks "New Post"
2. Form displayed with text area and media upload
3. User adds content and/or uploads media
4. Form submitted via POST
5. Server validates content and media
6. Post record created with unique slug
7. Media files uploaded and validated
8. PostMedia records created for each file
9. Video thumbnails generated (FFmpeg)
10. Mentions parsed and Mention records created
11. Notifications created for mentioned users
12. Hashtags extracted and linked
13. User redirected to new post

**Implementation Files:**
- `app/Http/Controllers/PostController.php` (store method)
- `app/Models/Post.php`
- `app/Models/PostMedia.php`
- `app/Services/MentionService.php`
- `app/Services/HashtagService.php`
- `resources/views/posts/create.blade.php`
- `resources/js/legacy/posts.js`

### 2.2 Post Feed

**Features:**
- Algorithmic feed showing:
  - User's own posts
  - Posts from followed users
  - Posts from public accounts
- Excludes:
  - Blocked users' posts
  - Unfollowed private accounts
- Paginated (15 posts per page)
- Load more functionality
- Eager loading for performance

**Feed Query Logic:**
```php
Post::with(['user.profile', 'media', 'likes', 'comments.user.profile'])
    ->whereHas('user', function ($query) use ($user) {
        $query->where('id', $user->id)  // Own posts
              ->orWhere('is_private', false)  // Public accounts
              ->orWhereHas('followers', function ($q) use ($user) {
                  $q->where('follower_id', $user->id);  // Followed users
              });
    })
    ->whereDoesntHave('user', function ($query) use ($user) {
        $query->whereHas('blockedBy', function ($q) use ($user) {
            $q->where('blocker_id', $user->id);  // Exclude blocked
        });
    })
    ->latest()
    ->paginate(15);
```

**Implementation Files:**
- `app/Http/Controllers/PostController.php` (index method)
- `resources/views/posts/index.blade.php`
- `resources/js/legacy/home.js`

### 2.3 Like Post

**Features:**
- Toggle like/unlike
- Real-time counter update
- Notification to post owner
- Prevent self-liking

**Like Flow:**
1. User clicks like button
2. AJAX POST to `/posts/{id}/like`
3. Server checks existing like
4. If exists: delete (unlike)
5. If not exists: create like + notification
6. Return success response
7. UI updated

**Implementation Files:**
- `app/Http/Controllers/PostController.php` (like method)
- `app/Models/Like.php`
- `resources/js/legacy/posts.js`

### 2.4 Save Post

**Features:**
- Bookmark posts for later
- Saved posts collection
- Toggle save/unsave
- Private saved posts list

**Implementation Files:**
- `app/Http/Controllers/PostController.php` (save method)
- `app/Models/SavedPost.php`
- `resources/views/users/saved-posts.blade.php`

### 2.5 Delete Post

**Features:**
- Owner can delete own posts
- Admin can delete any post
- Soft delete (recoverable)
- Cascade delete: media, likes, comments, saved posts
- File cleanup from storage

**Authorization:**
- Post owner
- Admin user

**Implementation Files:**
- `app/Http/Controllers/PostController.php` (destroy method)
- `app/Models/Post.php` (SoftDeletes trait)

### 2.6 Pin Post

**Features:**
- Pin up to 3 posts to profile top
- Pinned posts shown first
- Reorder pinned posts (drag & drop)
- Unpin anytime

**Implementation Files:**
- `app/Http/Controllers/UserController.php` (pinPost, unpinPost, reorderPinnedPosts)
- `app/Models/Post.php` (pinned_at field)

### 2.7 Post Privacy

**Features:**
- Public posts: visible to everyone
- Private posts: visible to owner and followers
- Privacy toggle per post
- Private indicator on posts

**Privacy Check:**
```php
// Show post if:
// 1. Post is public, OR
// 2. User is post owner, OR
// 3. User follows post owner
```

### 2.8 Video Processing

**Features:**
- FFmpeg video processing
- Automatic thumbnail generation (frame at 1 second)
- 60-second max video trimming
- Multiple video formats supported

**Thumbnail Generation:**
```bash
ffmpeg -i video.mp4 -ss 00:00:01 -vframes 1 thumbnail.jpg
```

---

## 3. Stories

### Overview

Ephemeral 24-hour content with view tracking, reactions, and multiple media types.

### 3.1 Create Story

**Features:**
- Image, video, or text-only stories
- 24-hour auto-expiry
- Unique slug per story
- View tracking
- Reaction support
- Multiple active stories per user

**Story Types:**
- **Image**: JPG, PNG, GIF, WEBP (compressed with Intervention Image)
- **Video**: MP4, MOV, AVI, WEBM (50MB max)
- **Text**: Text-only story with background (500 char max)

**Expiry System:**
- Stories automatically expire after 24 hours
- Hourly cleanup command: `CleanupExpiredStories`
- Expired stories soft deleted

**Implementation Files:**
- `app/Http/Controllers/StoryController.php`
- `app/Models/Story.php`
- `app/Console/Commands/CleanupExpiredStories.php`
- `resources/views/stories/create.blade.php`

### 3.2 View Stories

**Features:**
- Story viewer with auto-advance
- View tracking (one view per user per story)
- Story navigation (previous/next)
- Active stories indicator
- Real-time story updates

**Viewer Flow:**
1. User clicks on story
2. Story viewer opens
3. View recorded in StoryViews table
4. Story displayed for 5 seconds (auto-advance)
5. User can navigate manually
6. Viewers list available to story owner

**Implementation Files:**
- `app/Http/Controllers/StoryController.php` (show method)
- `app/Models/StoryView.php`
- `resources/views/stories/show.blade.php`

### 3.3 Story Reactions

**Features:**
- Emoji reactions to stories
- Multiple reactions per story
- View reaction counts
- Remove reactions

**Implementation Files:**
- `app/Http/Controllers/StoryController.php` (react, removeReaction)
- `app/Models/StoryReaction.php`

### 3.4 Story Viewers

**Features:**
- View who watched your story
- Viewer list with timestamps
- Real-time viewer updates

**Implementation Files:**
- `app/Http/Controllers/StoryController.php` (viewers method)
- `resources/views/stories/viewers.blade.php`

---

## 4. Comments & Reactions

### Overview

Threaded comment system with likes, mentions, and notifications.

### 4.1 Create Comment

**Features:**
- Comment on posts
- Reply to comments (nested threads)
- @mention support
- 280 character limit
- Notifications to post owner and mentioned users

**Comment Flow:**
1. User types comment
2. Form submitted
3. Server validates content
4. Comment record created
5. Mentions processed
6. Notifications created:
   - Post owner (if not commenter)
   - Mentioned users
7. UI updated

**Implementation Files:**
- `app/Http/Controllers/CommentController.php`
- `app/Models/Comment.php`
- `resources/js/legacy/comments.js`

### 4.2 Like Comment

**Features:**
- Toggle like/unlike comments
- Real-time counter update
- Notification to comment owner

**Implementation Files:**
- `app/Http/Controllers/CommentController.php` (like method)
- `app/Models/CommentLike.php`

### 4.3 Delete Comment

**Features:**
- Comment owner can delete
- Post owner can delete comments on their post
- Admin can delete any comment
- Cascade delete replies and likes

**Authorization:**
- Comment owner
- Post owner
- Admin user

---

## 5. Chat & Messaging

### Overview

Real-time messaging with direct and group conversations, typing indicators, read receipts, and delivery confirmation.

### 5.1 Conversations

**Features:**
- Direct messages (1-on-1)
- Group conversations (linked to groups)
- Conversation list with last message
- Real-time updates (1-second polling)
- Unread message count

**Conversation Types:**
- **Direct**: Between 2 users
- **Group**: Linked to a group, all members can participate

**Implementation Files:**
- `app/Http/Controllers/ChatController.php`
- `app/Models/Conversation.php`
- `resources/views/chat/index.blade.php`
- `resources/js/legacy/realtime.js`

### 5.2 Send Message

**Features:**
- Text messages
- Image messages
- Voice messages
- Video messages
- System messages (group events)
- Message threading

**Message Types:**
- `text`: Plain text message
- `image`: Image attachment
- `video`: Video attachment
- `voice`: Voice message
- `system`: System-generated message

**Send Flow:**
1. User types message
2. Form submitted via AJAX
3. Server validates content
4. Message record created
5. Conversation last_message_at updated
6. Real-time broadcast to recipients
7. UI updated

**Implementation Files:**
- `app/Http/Controllers/ChatController.php` (store method)
- `app/Models/Message.php`

### 5.3 Message Status

**Features:**
- **Sent**: Message saved to database
- **Delivered**: Recipient received message
- **Read**: Recipient opened conversation

**Status Tracking:**
- `delivered_at`: Timestamp when delivered
- `read_at`: Timestamp when read
- Real-time status updates

**Implementation Files:**
- `app/Http/Controllers/ChatController.php` (markAsRead, confirmDelivery)

### 5.4 Typing Indicators

**Features:**
- Real-time "user is typing" status
- 5-second cache expiry
- Per-conversation tracking
- Polling interval: 1 second

**Implementation:**
- Cache-based typing indicators
- Key format: `typing:{conversation_id}:{user_id}`
- TTL: 5 seconds
- Auto-expiry removes indicator

**Implementation Files:**
- `app/Services/RealtimeService.php` (setTypingIndicator, getTypingUsers)
- `app/Http/Controllers/ChatController.php` (sendTypingIndicator, getTypingStatus)
- `resources/js/legacy/realtime.js`

### 5.5 Message Deletion

**Features:**
- Delete for self
- Delete for everyone
- Soft delete with recovery
- Cascade cleanup

**Delete Options:**
- `delete_for_sender`: Only sender can't see
- `delete_for_everyone`: All participants can't see

### 5.6 Real-time Updates

**Polling Intervals:**
- Messages: 1 second
- Conversations: 1 second
- Typing indicators: 1 second
- Online status: 10 seconds

**Implementation Files:**
- `app/Services/RealtimeService.php`
- `resources/js/legacy/realtime.js`

---

## 6. Groups

### Overview

Community groups with member management, invite links, and group chat.

### 6.1 Create Group

**Features:**
- Public or private groups
- Group avatar
- Group description
- Automatic conversation creation
- Unique slug and invite link

**Create Flow:**
1. User fills create form
2. Group record created
3. Creator added as admin
4. Conversation created
5. Redirect to group page

**Implementation Files:**
- `app/Http/Controllers/GroupController.php`
- `app/Models/Group.php`
- `resources/views/groups/create.blade.php`

### 6.2 Member Management

**Features:**
- Add members (admins only)
- Remove members (admins only)
- Promote to admin (admins only)
- Demote to member (admins only)
- Leave group (any member)
- Member role display

**Member Roles:**
- `admin`: Full management permissions
- `member`: Standard member permissions

**Implementation Files:**
- `app/Http/Controllers/GroupController.php` (addMembers, removeMember, makeAdmin, removeAdmin)
- `app/Models/GroupMember.php`

### 6.3 Invite Links

**Features:**
- Unique invite link per group
- One-click join via link
- Regenerate invite link
- Quick invite (copy link)

**Invite Flow:**
1. Admin clicks "Generate Invite"
2. Unique link created
3. Link shared with users
4. User clicks link
5. Auto-joined to group
6. Redirect to group page

**Implementation Files:**
- `app/Http/Controllers/GroupController.php` (regenerateInvite, acceptInvite, joinViaInvite)

### 6.4 Group Chat

**Features:**
- Automatic conversation for each group
- All members can participate
- Group messages in main chat
- System messages for group events

---

## 7. User Profile & Social

### Overview

User profiles with customization, social features, and privacy controls.

### 7.1 User Profile

**Features:**
- Profile avatar (upload or default)
- Cover image
- Bio (255 characters)
- Website link
- Location
- Social links (JSON)
- Privacy status (private/public)
- Follower/following counts
- Posts grid

**Profile Fields:**
```php
[
    'avatar',
    'cover_image',
    'bio',
    'website',
    'location',
    'social_links',  // JSON: {twitter, facebook, instagram, etc.}
    'is_private',
]
```

**Implementation Files:**
- `app/Http/Controllers/UserController.php`
- `app/Models/Profile.php`
- `resources/views/users/show.blade.php`

### 7.2 Follow System

**Features:**
- Follow/unfollow users
- Follower/following lists
- Follow notifications
- Private account follow requests (future)

**Follow Flow:**
1. User clicks follow button
2. AJAX POST to `/users/{id}/follow`
3. Follow record created
4. Notification created
5. UI updated

**Implementation Files:**
- `app/Http/Controllers/UserController.php` (follow method)
- `app/Models/Follow.php`

### 7.3 User Blocking

**Features:**
- Block/unblock users
- Blocked users' content hidden
- Bidirectional blocking
- Blocked users list

**Block Effects:**
- Blocked user's posts not shown in feed
- Blocked user cannot follow blocker
- Blocked user cannot message blocker
- Blocked user cannot see blocker's profile (private)

**Implementation Files:**
- `app/Http/Controllers/UserController.php` (block method)
- `app/Models/Block.php`
- `resources/views/users/blocked.blade.php`

### 7.4 Online Status

**Features:**
- Real-time online/offline indicators
- Last active timestamp
- Batch status updates
- 10-second polling interval

**Status Update:**
- `is_online`: Boolean flag
- `last_active`: Timestamp

**Implementation Files:**
- `app/Http/Controllers/UserController.php` (updateOnlineStatus, getOnlineStatus)
- `resources/js/legacy/ui-utils.js`

### 7.5 QR Code Profile

**Features:**
- Generate QR code for profile
- Download QR code
- Share profile via QR

**Implementation Files:**
- `app/Http/Controllers/UserController.php` (generateQrCode, downloadQrCode)
- `app/Services/QrCodeService.php`
- `resources/views/users/qr-code.blade.php`

### 7.6 Explore & Search

**Features:**
- Explore page with suggested users
- Search users by name/username
- Filter by mutual followers
- Paginated results

**Implementation Files:**
- `app/Http/Controllers/UserController.php` (explore, searchPage)
- `resources/views/users/explore.blade.php`

---

## 8. Notifications

### Overview

Real-time notifications for all social interactions with push notification support.

### 8.1 Notification Types

**Supported Types:**
- `like`: Someone liked your post
- `comment`: Someone commented on your post
- `follow`: Someone followed you
- `mention`: Someone mentioned you
- `message`: New message received
- `story_reaction`: Someone reacted to your story
- `story_view`: Someone viewed your story (optional)
- `system`: System notifications

### 8.2 Real-time Updates

**Features:**
- Unread notification badge
- New notification toast
- 3-second polling interval
- Mark as read/unread
- Mark all as read

**Implementation Files:**
- `app/Http/Controllers/NotificationController.php`
- `app/Http/Controllers/Api/NotificationController.php`
- `app/Models/Notification.php`
- `resources/views/notifications/index.blade.php`
- `resources/js/legacy/realtime.js`

### 8.3 Notification Settings

**Features:**
- Enable/disable notification types
- Email notification preferences
- Push notification preferences

---

## 9. Admin Panel

### Overview

Complete moderation tools for platform management.

### 9.1 Dashboard

**Features:**
- Platform statistics
- User count
- Post count
- Recent activity
- Reports count

**Implementation Files:**
- `app/Http/Controllers/AdminController.php` (dashboard)
- `resources/views/admin/dashboard.blade.php`

### 9.2 User Management

**Features:**
- View all users
- Search users
- Edit user details
- Suspend/unsuspend users
- Delete users
- Make admin

**Implementation Files:**
- `app/Http/Controllers/AdminController.php` (users, showUser, editUser, updateUser, deleteUser)
- `resources/views/admin/users.blade.php`
- `resources/views/admin/user-detail.blade.php`
- `resources/views/admin/user-edit.blade.php`

### 9.3 Content Moderation

**Features:**
- View all posts
- Delete any post
- View all comments
- Delete any comment
- View all stories
- Delete any story

**Authorization:**
- Admin user only

**Implementation Files:**
- `app/Http/Controllers/AdminController.php` (posts, comments, stories, deletePost, deleteComment, deleteStory)

### 9.4 Report Management

**Features:**
- View pending reports
- Accept reports (delete content, suspend user)
- Reject reports
- Bulk actions
- Report history

**Report Statuses:**
- `pending`: Awaiting review
- `accepted`: Action taken
- `rejected`: No action needed

**Implementation Files:**
- `app/Http/Controllers/ReportController.php`
- `app/Models/PostReport.php`
- `resources/views/admin/reports.blade.php`

### 9.5 Admin Creation

**Features:**
- Create admin accounts
- Admin-only action
- Form validation

**Implementation Files:**
- `app/Http/Controllers/AdminController.php` (createAdminAccount)

---

## 10. AI Assistant

### Overview

Built-in AI chatbot for user support and assistance.

### 10.1 AI Chat

**Features:**
- Conversational AI interface
- Context-aware responses
- Help with platform features
- Troubleshooting assistance

**Implementation Files:**
- `app/Http/Controllers/AiController.php`
- `resources/views/ai/index.blade.php`
- `resources/js/legacy/ai-chat.js`

---

## 11. Push Notifications

### Overview

Browser-based push notifications for real-time updates.

### 11.1 Web Push API

**Features:**
- VAPID key authentication
- Push subscription management
- Notification preferences
- Test notification

**Implementation Files:**
- `app/Http/Controllers/PushNotificationController.php`
- `app/Services/PushNotificationService.php`
- `app/Models/PushSubscription.php`
- `public/sw.js` (Service Worker)
- `resources/js/push-notifications.js`

### 11.2 Notification Types

**Supported Push Notifications:**
- New message
- Like on post
- Comment on post
- New follower
- Story reaction

---

## 12. Activity & Analytics

### Overview

User activity tracking with session management and analytics.

### 12.1 Activity Logs

**Features:**
- Track user actions
- IP address logging
- User agent tracking
- Location data (country, city)
- Session tracking

**Logged Actions:**
- Login/logout
- Post creation
- Comment creation
- Profile updates
- Password changes
- Settings changes

**Implementation Files:**
- `app/Http/Controllers/ActivityController.php`
- `app/Services/ActivityService.php`
- `app/Models/ActivityLog.php`
- `resources/views/activity/index.blade.php`

### 12.2 Session Management

**Features:**
- View active sessions
- Terminate sessions
- Terminate all sessions
- Session details (IP, location, device)

**Implementation Files:**
- `app/Http/Controllers/ActivityController.php` (terminateSession, terminateAllSessions)

### 12.3 Data Export

**Features:**
- Export activity data
- Download personal data
- GDPR compliance

**Implementation Files:**
- `app/Http/Controllers/ActivityController.php` (export)

---

## 13. Life Events

### Overview

Special life events with reactions and memory book.

### 13.1 Create Event

**Features:**
- Event types (birthday, anniversary, etc.)
- Event date
- Associated post (optional)
- Metadata storage

**Implementation Files:**
- `app/Http/Controllers/EventController.php`
- `app/Models/Event.php`
- `app/Models/EventReaction.php`

### 13.2 Event Reactions

**Features:**
- React to events
- Reaction types
- View reactions

### 13.3 Memory Book

**Features:**
- View user's life events
- Timeline view
- Nostalgia feature

---

## 14. Hashtags & Discovery

### Overview

Hashtag system for content discovery and trending topics.

### 14.1 Hashtag Extraction

**Features:**
- Automatic hashtag extraction from posts
- Hashtag pages
- Hashtag suggestions
- Trending hashtags

**Implementation Files:**
- `app/Http/Controllers/HashtagController.php`
- `app/Http/Controllers/Api/HashtagApiController.php`
- `app/Services/HashtagService.php`
- `app/Models/Hashtag.php`
- `resources/views/hashtags/show.blade.php`

### 14.2 Hashtag Pages

**Features:**
- View posts with hashtag
- Hashtag info
- Related hashtags

---

## 15. Content Moderation

### Overview

User-driven content reporting and moderation system.

### 15.1 Report Post

**Features:**
- Report posts for violations
- Report reasons
- Description field
- Anonymous reporting

**Report Reasons:**
- Spam
- Harassment
- Hate speech
- Nudity
- Violence
- Other

**Implementation Files:**
- `app/Http/Controllers/ReportController.php`
- `app/Models/PostReport.php`
- `resources/views/posts/report.blade.php`

### 15.2 My Reports

**Features:**
- View submitted reports
- Report status tracking
- Delete reports
- Report history

**Implementation Files:**
- `app/Http/Controllers/ReportController.php` (myReports, showReport, deleteReport, deleteAllReports)
- `resources/views/reports/my-reports.blade.php`

---

## Feature Matrix

### Guest Users
- ✓ View public posts
- ✓ View profiles
- ✓ Register
- ✓ Login

### Registered Users
- ✓ All Guest features, plus:
- ✓ Create post
- ✓ Like post
- ✓ Comment
- ✓ Follow user
- ✓ Send message
- ✓ Create group
- ✓ Create story
- ✓ View notifications

### Admin Users
- ✓ All User features, plus:
- ✓ Access admin panel
- ✓ Delete any content
- ✓ Suspend users
- ✓ Review reports

---

<div align="center">

**Nexus - Complete Features Documentation**

Last Updated: March 27, 2026 | Laravel 12.x | PHP 8.2+

</div>
