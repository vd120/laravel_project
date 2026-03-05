# Feature Documentation

## Table of Contents

1. [Posts](#posts)
2. [Comments](#comments)
3. [Stories](#stories)
4. [Chat & Messaging](#chat--messaging)
5. [Groups](#groups)
6. [Notifications](#notifications)
7. [User Profile](#user-profile)
8. [Search & Explore](#search--explore)
9. [AI Assistant](#ai-assistant)
10. [Admin Panel](#admin-panel)

---

## Posts

### Overview

Posts are the primary content type in Nexus. Users can create text posts with up to 30 media attachments (images or videos).

### Features

- **Text Content**: Maximum 280 characters
- **Media Attachments**: Up to 30 images or videos
- **Privacy Controls**: Public or private posts
- **Engagement**: Likes, saves, comments
- **Mentions**: @username mentions with notifications
- **Slug-based URLs**: 24-character unique slugs

---

### Create Post

**Endpoint:** `POST /posts`

**Controller:** `PostController@store`

**Request:**
```
Content-Type: multipart/form-data

content: "Hello world! This is my first post."
is_private: false
media[]: [file1, file2, ...]  // max 30 files
```

**Validation Rules:**
```php
[
    'content' => ['required_without:media', 'string', 'max:280'],
    'is_private' => ['boolean'],
    'media.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'], // 50MB max
]
```

**Logic Flow:**
```
1. Validate input
2. Create post record with slug
3. Process media uploads (if any)
   - Validate file type and size
   - Generate unique filename
   - Store in storage/app/public/posts/
   - Create PostMedia records
4. Process @mentions
   - Parse mentions from content
   - Create Mention records
   - Send notifications to mentioned users
5. Return success response
```

**Code:**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'content' => ['required_without:media', 'string', 'max:280'],
        'is_private' => ['boolean'],
        'media.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
    ]);
    
    // Create post
    $post = $request->user()->posts()->create([
        'content' => $validated['content'] ?? '',
        'is_private' => $validated['is_private'] ?? false,
        'slug' => Str::random(24),
    ]);
    
    // Handle media
    if ($request->hasFile('media')) {
        $sortOrder = 1;
        
        foreach ($request->file('media') as $file) {
            $path = $file->store('posts', 'public');
            
            // Determine media type
            $mediaType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
            
            // Create thumbnail for videos
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
        $mentionService->processMentions($post, $validated['content'], auth()->id());
    }
    
    return redirect()->back()->with('success', 'Post created successfully!');
}
```

---

### Post Feed

**Endpoint:** `GET /`

**Controller:** `PostController@index`

**Feed Logic:**
```php
public function index(Request $request)
{
    $user = $request->user();
    
    $posts = Post::with(['user.profile', 'media', 'likes', 'comments'])
        ->whereHas('user', function ($query) use ($user) {
            $query->where('id', $user->id)  // Own posts
                  ->orWhere('is_private', false)  // Public accounts
                  ->orWhereHas('followers', function ($q) use ($user) {
                      $q->where('follower_id', $user->id);  // Followed users
                  });
        })
        ->whereDoesntHave('user', function ($query) use ($user) {
            $query->whereHas('blockedBy', function ($q) use ($user) {
                $q->where('blocker_id', $user->id);  // Exclude blocked users
            });
        })
        ->latest()
        ->paginate(15);
    
    return inertia('Posts/Index', compact('posts'));
}
```

**Privacy Filtering:**
- User's own posts (all)
- Posts from public accounts
- Posts from followed users (even if private)
- Excludes posts from blocked users

---

### Like Post

**Endpoint:** `POST /posts/{post}/like`

**Controller:** `PostController@like`

**Logic:**
```php
public function like(Post $post)
{
    $user = auth()->user();
    
    // Check if already liked
    $like = $post->likes()->where('user_id', $user->id)->first();
    
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
            NotificationController::createNotification(
                $post->user_id,
                'like',
                ['user' => $user, 'post_id' => $post->id],
                $post
            );
        }
    }
    
    return back()->with('success', $liked ? 'Post liked!' : 'Post unliked.');
}
```

---

### Save Post

**Endpoint:** `POST /posts/{post}/save`

**Controller:** `PostController@save`

**Logic:**
```php
public function save(Post $post)
{
    $user = auth()->user();
    
    // Toggle save
    $savedPost = $post->savedPosts()->where('user_id', $user->id)->first();
    
    if ($savedPost) {
        $savedPost->delete();
        $saved = false;
    } else {
        $post->savedPosts()->create(['user_id' => $user->id]);
        $saved = true;
    }
    
    return back()->with('success', $saved ? 'Post saved!' : 'Post unsaved.');
}
```

---

### Delete Post

**Endpoint:** `DELETE /posts/{post}`

**Controller:** `PostController@destroy`

**Logic:**
```php
public function destroy(Post $post)
{
    $user = auth()->user();
    
    // Authorization check
    abort_unless(
        $user->is_admin || $user->id === $post->user_id,
        403,
        'Unauthorized action.'
    );
    
    // Delete media files
    foreach ($post->media as $media) {
        Storage::disk('public')->delete($media->media_path);
        if ($media->media_thumbnail) {
            Storage::disk('public')->delete($media->media_thumbnail);
        }
    }
    
    // Delete post (cascades to media, likes, comments)
    $post->delete();
    
    return back()->with('success', 'Post deleted successfully!');
}
```

---

### Media Processing

#### Image Compression

```php
use Intervention\Image\Facades\Image;

private function processImage($file)
{
    $image = Image::make($file);
    
    // Resize if too large
    if ($image->width() > 1920) {
        $image->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
    
    // Compress
    $path = 'posts/' . Str::random(40) . '.jpg';
    $image->save(storage_path('app/public/' . $path), 85);
    
    return $path;
}
```

#### Video Thumbnail

```php
private function generateVideoThumbnail($file)
{
    $videoPath = $file->getRealPath();
    $thumbnailPath = 'posts/' . Str::random(40) . '_thumb.jpg';
    
    // Use FFmpeg to extract frame at 1 second
    $command = sprintf(
        'ffmpeg -i %s -ss 00:00:01 -vframes 1 %s',
        escapeshellarg($videoPath),
        escapeshellarg(storage_path('app/public/' . $thumbnailPath))
    );
    
    exec($command);
    
    return file_exists(storage_path('app/public/' . $thumbnailPath)) 
        ? $thumbnailPath 
        : null;
}
```

---

## Comments

### Overview

Comments support nested replies (threaded comments) with likes and mentions.

### Features

- **Nested Replies**: Reply to comments
- **Likes**: Like comments
- **Mentions**: @username in comments
- **Notifications**: Notify post owner and mentioned users

---

### Create Comment

**Endpoint:** `POST /comments`

**Controller:** `CommentController@store`

**Request:**
```json
{
    "post_id": 1,
    "content": "Great post!",
    "parent_id": null  // For replies
}
```

**Logic:**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'post_id' => ['required', 'exists:posts,id'],
        'content' => ['required', 'string', 'max:280'],
        'parent_id' => ['nullable', 'exists:comments,id'],
    ]);
    
    $post = Post::findOrFail($validated['post_id']);
    
    // Check if replying to a comment
    if ($validated['parent_id']) {
        $parentComment = Comment::findOrFail($validated['parent_id']);
        // Ensure parent comment belongs to same post
        abort_if($parentComment->post_id !== $post->id, 403);
    }
    
    $comment = $post->comments()->create([
        'user_id' => auth()->id(),
        'parent_id' => $validated['parent_id'],
        'content' => $validated['content'],
    ]);
    
    // Process mentions
    $mentionService->processMentions($comment, $validated['content'], auth()->id());
    
    // Notify post owner (if not own post)
    if ($post->user_id !== auth()->id()) {
        NotificationController::createNotification(
            $post->user_id,
            'comment',
            [
                'user' => auth()->user(),
                'post_id' => $post->id,
                'comment_id' => $comment->id,
            ],
            $post
        );
    }
    
    return back()->with('success', 'Comment added!');
}
```

---

### Delete Comment

**Endpoint:** `DELETE /comments/{comment}`

**Controller:** `CommentController@destroy`

**Authorization:**
- Comment owner
- Post owner
- Admin

```php
public function destroy(Comment $comment)
{
    $user = auth()->user();
    
    $canDelete = $user->is_admin 
        || $user->id === $comment->user_id
        || $user->id === $comment->post->user_id;
    
    abort_unless($canDelete, 403, 'Unauthorized action.');
    
    $comment->delete();
    
    return back()->with('success', 'Comment deleted!');
}
```

---

## Stories

### Overview

Stories are ephemeral content that expires after 24 hours. They support images and videos with view tracking and reactions.

### Features

- **24-Hour Expiration**: Auto-delete after 24 hours
- **Media Types**: Images and videos
- **View Tracking**: See who viewed your story
- **Reactions**: Emoji reactions
- **Video Trimming**: Videos trimmed to 60 seconds max
- **Privacy**: Only visible to followers (for private accounts)

---

### Create Story

**Endpoint:** `POST /stories`

**Controller:** `StoryController@store`

**Request:**
```
Content-Type: multipart/form-data

media: (file)
content: "Story caption"  // optional, max 280 chars
```

**Validation:**
```php
[
    'media' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
    'content' => ['nullable', 'string', 'max:280'],
]
```

**Logic:**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'media' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm', 'max:51200'],
        'content' => ['nullable', 'string', 'max:280'],
    ]);
    
    $file = $validated['media'];
    $mediaType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
    
    // Process video (trim to 60 seconds)
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
```

---

### Video Processing

```php
private function processVideo($file)
{
    $videoPath = $file->getRealPath();
    $outputPath = 'stories/' . Str::random(40) . '.mp4';
    $outputFullPath = storage_path('app/public/' . $outputPath);
    
    // Check duration and trim if needed
    $duration = $this->getVideoDuration($videoPath);
    
    if ($duration > 60) {
        // Trim to first 60 seconds
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

### View Story

**Endpoint:** `GET /stories/{user}/{story}`

**Controller:** `StoryController@show`

**Logic:**
```php
public function show(User $user, Story $story)
{
    // Check authorization
    abort_if($story->user_id !== $user->id, 404);
    
    // Check if story is expired
    abort_if($story->expires_at < now(), 404, 'Story expired');
    
    // Check privacy (for private accounts)
    if ($user->profile->is_private) {
        $isFollowing = $user->followers()
            ->where('follower_id', auth()->id())
            ->exists();
        
        abort_unless(
            $isFollowing || auth()->id() === $user->id,
            403,
            'This story is private.'
        );
    }
    
    // Track view (if not own story)
    if (auth()->id() !== $story->user_id) {
        StoryView::firstOrCreate([
            'user_id' => auth()->id(),
            'story_id' => $story->id,
        ]);
        
        // Increment view count
        $story->increment('views');
    }
    
    return inertia('Stories/Show', compact('story'));
}
```

---

### Story Reactions

**Add Reaction:**
```php
public function react(Request $request, User $user, Story $story)
{
    $validated = $request->validate([
        'reaction' => ['required', 'string', 'max:10'],
    ]);
    
    // Update or create reaction
    StoryReaction::updateOrCreate(
        [
            'user_id' => auth()->id(),
            'story_id' => $story->id,
        ],
        ['reaction_type' => $validated['reaction']]
    );
    
    return back()->with('success', 'Reaction added!');
}
```

**Get Viewers:**
```php
public function viewers(User $user, Story $story)
{
    // Only story owner can see viewers
    abort_if($story->user_id !== $user->id, 403);
    
    $viewers = $story->storyViews()
        ->with('user.profile')
        ->latest()
        ->get();
    
    return inertia('Stories/Viewers', compact('viewers'));
}
```

---

### Cleanup Expired Stories

**Artisan Command:** `php artisan stories:cleanup`

```php
public function handle()
{
    $expiredStories = Story::where('expires_at', '<', now())->get();
    
    foreach ($expiredStories as $story) {
        // Delete media file
        Storage::disk('public')->delete($story->media_path);
        
        // Delete story (cascades to views and reactions)
        $story->delete();
    }
    
    $this->info("Deleted {$expiredStories->count()} expired stories.");
}
```

**Scheduled Task:**
```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('stories:cleanup')->hourly();
}
```

---

## Chat & Messaging

### Overview

Real-time chat with direct messages and group conversations. Features include read receipts, typing indicators, and media sharing.

### Features

- **Direct Messages**: One-on-one conversations
- **Group Chats**: Multiple participants
- **Media Messages**: Images, videos, files
- **Read Receipts**: See when messages are read
- **Typing Indicators**: Real-time typing status
- **Message Deletion**: Delete for me or everyone
- **Invite Links**: Join groups via link

---

### Conversation Model

**Types:**
- **Direct Message**: Between 2 users
- **Group Chat**: Multiple participants via Group

**Fields:**
```php
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user1_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user2_id')->nullable()->constrained()->cascadeOnDelete();
    $table->boolean('is_group')->default(false);
    $table->foreignId('group_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('slug')->unique();
    $table->string('name')->nullable();  // For groups
    $table->string('avatar')->nullable();  // For groups
    $table->timestamp('last_message_at')->nullable();
    $table->timestamps();
});
```

---

### Get Conversations

**Endpoint:** `GET /chat/conversations`

**Controller:** `ChatController@getConversations`

**Logic:**
```php
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

### Send Message

**Endpoint:** `POST /chat/{conversation}`

**Controller:** `ChatController@store`

**Request:**
```
Content-Type: multipart/form-data

content: "Hello!"
type: text  // text, image, file
media: (optional file)
```

**Logic:**
```php
public function store(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'content' => ['required_without:media', 'string'],
        'type' => ['in:text,image,file'],
        'media' => ['nullable', 'file', 'max:51200'],
    ]);
    
    // Check if user is member of conversation
    abort_unless($conversation->isMember(auth()->id()), 403);
    
    $messageData = [
        'conversation_id' => $conversation->id,
        'sender_id' => auth()->id(),
        'content' => $validated['content'] ?? '',
        'type' => $validated['type'] ?? 'text',
    ];
    
    // Handle media
    if ($request->hasFile('media')) {
        $file = $validated['media'];
        $path = $file->store('messages', 'public');
        
        $messageData['media_path'] = json_encode([$path]);
        $messageData['original_filename'] = $file->getClientOriginalName();
        $messageData['media_size'] = $file->getSize();
    }
    
    $message = Message::create($messageData);
    
    // Update conversation last_message_at
    $conversation->update([
        'last_message_at' => now(),
    ]);
    
    // Create notification for recipients
    $recipients = $conversation->getRecipients(auth()->id());
    foreach ($recipients as $recipientId) {
        NotificationController::createMessageNotification(
            $recipientId,
            $message,
            $conversation
        );
    }
    
    return response()->json([
        'message' => $message->load('sender.profile'),
    ]);
}
```

---

### Read Receipts

**Mark as Read:**
```php
public function markAsRead(Conversation $conversation)
{
    abort_unless($conversation->isMember(auth()->id()), 403);
    
    // Mark all unread messages as read
    $conversation->messages()
        ->whereNotNull('created_at')
        ->whereNull('read_at')
        ->where('sender_id', '!=', auth()->id())
        ->update(['read_at' => now()]);
    
    return response()->json(['success' => true]);
}
```

**Message Status:**
```php
// In Message model
public function markAsRead()
{
    $this->update(['read_at' => now()]);
}

// Static method
public static function markConversationAsRead($conversationId, $userId)
{
    Message::where('conversation_id', $conversationId)
        ->where('sender_id', '!=', $userId)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);
}
```

---

### Typing Indicators

**Send Typing Status:**
```php
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
```

**Get Typing Status:**
```php
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

### Delete Message

**Endpoint:** `DELETE /chat/message/{message}`

**Controller:** `ChatController@destroy`

**Options:**
- Delete for me (soft delete)
- Delete for everyone (sender only)

```php
public function destroy(Request $request, Message $message)
{
    $validated = $request->validate([
        'delete_for' => ['in:me,everyone'],
    ]);
    
    $conversation = $message->conversation;
    abort_unless($conversation->isMember(auth()->id()), 403);
    
    if ($validated['delete_for'] === 'everyone' && $message->sender_id === auth()->id()) {
        // Delete for everyone
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

### Group Management

#### Create Group

**Endpoint:** `POST /groups`

**Controller:** `GroupController@store`

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:1000'],
        'is_private' => ['boolean'],
        'avatar' => ['nullable', 'image', 'max:5120'],
        'member_ids' => ['nullable', 'array'],
        'member_ids.*' => ['exists:users,id'],
    ]);
    
    // Create group
    $group = Group::create([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'is_private' => $validated['is_private'] ?? false,
        'creator_id' => auth()->id(),
        'slug' => Str::slug($validated['name']) . '-' . Str::random(10),
        'invite_link' => Str::random(32),
    ]);
    
    // Handle avatar
    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('groups', 'public');
        $group->update(['avatar' => $path]);
    }
    
    // Add creator as admin
    $group->addMember(auth()->user(), 'admin');
    
    // Add other members
    if ($validated['member_ids']) {
        foreach ($validated['member_ids'] as $memberId) {
            if ($memberId !== auth()->id()) {
                $group->addMember(User::find($memberId), 'member');
            }
        }
    }
    
    // Create group conversation
    Conversation::createGroupConversation($group);
    
    return redirect()->route('groups.show', $group)
        ->with('success', 'Group created!');
}
```

---

#### Group Invite Link

**Regenerate Link:**
```php
public function regenerateInvite(Group $group)
{
    abort_unless($group->isAdmin(auth()->user()), 403);
    
    $group->update([
        'invite_link' => Str::random(32),
    ]);
    
    return back()->with('success', 'Invite link regenerated!');
}
```

**Join via Link:**
```php
public function acceptInvite($inviteLink)
{
    $group = Group::where('invite_link', $inviteLink)->firstOrFail();
    
    // Check if already member
    if ($group->hasMember(auth()->user())) {
        return redirect()->route('groups.show', $group)
            ->with('info', 'You are already a member.');
    }
    
    // Add member
    $group->addMember(auth()->user(), 'member');
    
    // Create notification
    NotificationController::createNotification(
        auth()->id(),
        'group_invite',
        ['group_id' => $group->id, 'group_name' => $group->name],
        $group
    );
    
    return redirect()->route('groups.show', $group)
        ->with('success', 'Joined group!');
}
```

---

## Notifications

### Overview

Real-time notifications for user activity including follows, likes, comments, mentions, messages, and group invites.

### Notification Types

| Type | Trigger | Data |
|------|---------|------|
| `follow` | Someone follows you | `{ user: {...} }` |
| `like` | Someone likes your post | `{ user: {...}, post_id: 1 }` |
| `comment` | Someone comments on your post | `{ user: {...}, post_id: 1, comment_id: 1 }` |
| `mention` | Someone mentions you | `{ user: {...}, post_id: 1 }` |
| `message` | New message received | `{ user: {...}, conversation_id: 1 }` |
| `group_invite` | Invited to group | `{ user: {...}, group_id: 1 }` |

---

### Create Notification

**Helper Method:**
```php
// In NotificationController
public static function createNotification(
    int $recipientId,
    string $type,
    array $data,
    ?Model $related = null
): Notification {
    return Notification::create([
        'user_id' => $recipientId,
        'type' => $type,
        'data' => $data,
        'related_id' => $related?->id,
        'related_type' => $related ? get_class($related) : null,
    ]);
}
```

---

### Real-time Updates

**Polling Endpoint:** `GET /api/notifications/realtime-updates`

**Logic:**
```php
public function getRealtimeUpdates(Request $request)
{
    $user = auth()->user();
    $lastUpdate = $request->query('last_update');
    
    $query = Notification::where('user_id', $user->id);
    
    if ($lastUpdate) {
        $query->where('created_at', '>', $lastUpdate);
    }
    
    $newNotifications = $query->unread()->latest()->get();
    
    $unreadCount = Notification::where('user_id', $user->id)
        ->unread()
        ->count();
    
    return response()->json([
        'has_updates' => $newNotifications->count() > 0,
        'unread_count' => $unreadCount,
        'new_notifications' => $newNotifications,
    ]);
}
```

**Frontend Polling:**
```javascript
// Poll every 5 seconds
setInterval(async () => {
    const response = await fetch('/api/notifications/realtime-updates');
    const data = await response.json();
    
    if (data.has_updates) {
        // Update notification badge
        updateUnreadCount(data.unread_count);
        
        // Show toast for new notifications
        data.new_notifications.forEach(notification => {
            showNotificationToast(notification);
        });
    }
}, 5000);
```

---

## User Profile

### Features

- **Avatar & Cover Image**: Customizable profile images
- **Bio & About**: Profile information
- **Social Links**: Twitter, GitHub, LinkedIn, etc.
- **Privacy Settings**: Private account option
- **Followers/Following**: Social graph
- **Saved Posts**: Bookmark posts

---

### Update Profile

**Endpoint:** `POST /profile/{user}/update`

**Controller:** `UserController@updateProfile`

**Validation:**
```php
[
    'name' => ['required', 'string', 'max:255'],
    'bio' => ['nullable', 'string', 'max:255'],
    'location' => ['nullable', 'string', 'max:255'],
    'website' => ['nullable', 'url', 'max:255'],
    'occupation' => ['nullable', 'string', 'max:255'],
    'about' => ['nullable', 'string'],
    'phone' => ['nullable', 'string', 'max:50'],
    'gender' => ['nullable', 'string', 'max:50'],
    'is_private' => ['boolean'],
    'social_links' => ['nullable', 'array'],
    'avatar' => ['nullable', 'image', 'max:5120'],
    'cover_image' => ['nullable', 'image', 'max:10240'],
]
```

---

### Follow/Unfollow

**Endpoint:** `POST /users/{user}/follow`

**Controller:** `UserController@follow`

**Logic:**
```php
public function follow(User $user)
{
    $authUser = auth()->user();
    
    // Cannot follow self
    abort_if($user->id === $authUser->id, 403);
    
    // Toggle follow
    $isFollowing = $authUser->following()->where('followed_id', $user->id)->exists();
    
    if ($isFollowing) {
        $authUser->following()->detach($user->id);
        $followed = false;
    } else {
        $authUser->following()->attach($user->id);
        $followed = true;
        
        // Create notification
        NotificationController::createNotification(
            $user->id,
            'follow',
            ['user' => $authUser]
        );
    }
    
    return back()->with('success', $followed ? 'Following!' : 'Unfollowed.');
}
```

---

## Search & Explore

### Search Users

**Endpoint:** `GET /api/search-users`

**Controller:** `Api\UserController@search`

**Logic:**
```php
public function search(Request $request)
{
    $query = $request->query('q');
    
    if (!$query) {
        return response()->json([]);
    }
    
    $users = User::where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('username', 'like', "%{$query}%");
    })
    ->where('id', '!=', auth()->id())
    ->whereDoesntHave('blockedBy', function ($q) {
        $q->where('blocker_id', auth()->id());
    })
    ->with('profile')
    ->limit(10)
    ->get();
    
    return response()->json($users);
}
```

---

### Explore Page

**Endpoint:** `GET /explore`

**Controller:** `UserController@explore`

**Logic:**
```php
public function explore()
{
    $user = auth()->user();
    
    // Get users not followed by current user
    $users = User::where('id', '!=', $user->id)
        ->whereDoesntHave('followers', function ($q) use ($user) {
            $q->where('follower_id', $user->id);
        })
        ->whereDoesntHave('blockedBy', function ($q) use ($user) {
            $q->where('blocker_id', $user->id);
        })
        ->withCount('followers')
        ->inRandomOrder()
        ->limit(20)
        ->get();
    
    return inertia('Users/Explore', compact('users'));
}
```

---

## AI Assistant

### Overview

Rule-based chatbot with predefined responses for common questions and help topics.

### Features

- **9 Menu Options**: Predefined help topics
- **Follow Suggestions**: Recommend users to follow
- **Privacy Guide**: Explain privacy settings
- **Trending Topics**: Show popular content

---

### Chat Endpoint

**Endpoint:** `POST /ai/chat`

**Controller:** `AiController@chat`

**Menu Options:**
```
1. How to use Nexus
2. Privacy settings guide
3. How to create posts
4. Story tips
5. Chat features
6. Group management
7. Account settings
8. Follow suggestions
9. Report a problem
```

**Logic:**
```php
public function chat(Request $request)
{
    $validated = $request->validate([
        'message' => ['required', 'string'],
        'option' => ['nullable', 'integer', 'min:1', 'max:9'],
    ]);
    
    $option = $validated['option'] ?? null;
    $responses = $this->getResponses();
    
    if ($option && isset($responses[$option])) {
        $response = $responses[$option];
    } else {
        // Default response
        $response = $this->getDefaultResponse();
    }
    
    return response()->json([
        'message' => $response,
        'options' => array_keys($responses),
    ]);
}

private function getResponses(): array
{
    return [
        1 => "Welcome to Nexus! Here's how to get started:\n\n1. Complete your profile\n2. Follow interesting users\n3. Create your first post\n4. Explore the feed",
        2 => "Privacy Settings:\n\n- Set account to private for follower-only content\n- Mark individual posts as private\n- Block users you don't want to interact with",
        3 => "Creating Posts:\n\n1. Click 'Create Post'\n2. Write up to 280 characters\n3. Add up to 30 images/videos\n4. Click 'Post'",
        // ... more responses
    ];
}
```

---

## Admin Panel

### Overview

Comprehensive admin dashboard for user management, content moderation, and platform analytics.

### Features

- **Dashboard**: Statistics and recent activity
- **User Management**: View, edit, suspend, delete users
- **Content Moderation**: Delete posts, comments, stories
- **Admin Creation**: Create new admin accounts

---

### Dashboard

**Endpoint:** `GET /admin`

**Controller:** `AdminController@dashboard`

**Statistics:**
```php
public function dashboard()
{
    $stats = [
        'total_users' => User::count(),
        'total_posts' => Post::count(),
        'total_comments' => Comment::count(),
        'total_groups' => Group::count(),
        'new_users_today' => User::whereDate('created_at', today())->count(),
        'active_users' => User::where('last_active', '>', now()->subMinutes(5))->count(),
    ];
    
    $recentActivity = [
        'recent_users' => User::latest()->limit(5)->get(),
        'recent_posts' => Post::with('user')->latest()->limit(5)->get(),
        'recent_groups' => Group::latest()->limit(5)->get(),
    ];
    
    return inertia('Admin/Dashboard', compact('stats', 'recentActivity'));
}
```

---

### User Management

**Edit User:**
```php
public function updateUser(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
        'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        'is_admin' => ['boolean'],
        'is_suspended' => ['boolean'],
        'bio' => ['nullable', 'string'],
        'avatar' => ['nullable', 'image', 'max:5120'],
        'cover_image' => ['nullable', 'image', 'max:10240'],
    ]);
    
    $user->update([
        'name' => $validated['name'],
        'username' => $validated['username'],
        'email' => $validated['email'],
        'is_admin' => $validated['is_admin'] ?? false,
        'is_suspended' => $validated['is_suspended'] ?? false,
    ]);
    
    $user->profile->update([
        'bio' => $validated['bio'] ?? null,
    ]);
    
    // Handle images
    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->profile->update(['avatar' => $path]);
    }
    
    return redirect()->route('admin.users.show', $user)
        ->with('success', 'User updated!');
}
```

---

### Content Deletion

**Delete Post:**
```php
public function deletePost(Post $post)
{
    // Delete media files
    foreach ($post->media as $media) {
        Storage::disk('public')->delete($media->media_path);
    }
    
    $post->delete();
    
    return back()->with('success', 'Post deleted!');
}
```

**Delete Comment:**
```php
public function deleteComment(Comment $comment)
{
    $comment->delete();
    
    return back()->with('success', 'Comment deleted!');
}
```

**Delete Story:**
```php
public function deleteStory(Story $story)
{
    Storage::disk('public')->delete($story->media_path);
    $story->delete();
    
    return back()->with('success', 'Story deleted!');
}
```

---

**Last Updated**: March 2026
