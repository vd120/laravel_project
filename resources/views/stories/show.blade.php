<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Story - {{ $user->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #000;
            overflow: hidden;
            touch-action: manipulation;
        }

        .story-viewer {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }

        .story-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 1;
        }

        .story-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            height: 100%;
            max-height: 800px;
            background: #000;
            z-index: 2;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .story-container {
                border-radius: 12px;
                height: 90vh;
                max-height: 800px;
            }
        }

        /* Progress Bars */
        .story-progress {
            position: absolute;
            top: 20px;
            left: 12px;
            right: 12px;
            display: flex;
            gap: 4px;
            z-index: 10;
        }

        .progress-bar {
            flex: 1;
            height: 3px;
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #fff;
            width: 0%;
            transition: width 0.1s linear;
        }

        .progress-bar.active .progress-fill {
            animation: progress 5s linear forwards;
        }

        .progress-bar.active .progress-fill.paused {
            animation-play-state: paused;
        }

        .progress-bar.completed .progress-fill {
            width: 100%;
        }

        @keyframes progress {
            to { width: 100%; }
        }

        /* Header */
        .story-header {
            position: absolute;
            top: 40px;
            left: 0;
            right: 0;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(180deg, rgba(0,0,0,0.5) 0%, transparent 100%);
            z-index: 10;
        }

        .story-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .story-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #1d9bf0;
            object-fit: cover;
        }

        .story-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d9bf0, #8B5CF6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid #1d9bf0;
        }

        .story-info {
            color: white;
        }

        .story-username {
            font-weight: 600;
            font-size: 15px;
            display: block;
        }

        .story-time {
            font-size: 12px;
            opacity: 0.8;
        }

        .story-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .story-close:hover {
            opacity: 1;
        }

        .story-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .story-delete {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            opacity: 0.8;
            transition: opacity 0.2s;
            text-decoration: none;
        }

        .story-delete:hover {
            opacity: 1;
            color: #ef4444;
        }

        .story-message {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            opacity: 0.8;
            transition: opacity 0.2s;
            text-decoration: none;
        }

        .story-message:hover {
            opacity: 1;
            color: #1d9bf0;
        }

        /* Content */
        .story-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .story-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .story-slide.active {
            opacity: 1;
        }

        .story-media {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .story-caption {
            position: absolute;
            bottom: 100px;
            left: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            text-align: center;
        }

        /* Navigation */
        .nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .story-container:hover .nav-btn {
            opacity: 1;
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .prev-btn { left: 10px; }
        .next-btn { right: 10px; }

        /* Controls - WhatsApp Style */
        .story-controls {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 10;
        }

        .control-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            text-decoration: none;
            flex-shrink: 0;
        }

        .control-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        .control-btn.viewers-btn {
            width: auto;
            padding: 0 16px;
            border-radius: 24px;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Message Input */
        .story-message-input-wrapper {
            flex: 1;
            margin: 0 12px;
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.2);
            border-radius: 24px;
            padding: 8px 16px;
        }

        .story-message-input {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            font-size: 14px;
            outline: none;
        }

        .story-message-input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .story-send-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .story-send-btn:hover {
            opacity: 1;
        }

        .story-send-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Reaction Picker */
        .reaction-picker {
            position: absolute;
            bottom: 80px;
            right: 16px;
            background: rgba(0,0,0,0.8);
            padding: 12px;
            border-radius: 30px;
            display: none;
            gap: 8px;
            z-index: 20;
        }

        .reaction-picker.show {
            display: flex;
        }

        .emoji-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            padding: 4px;
            transition: transform 0.2s;
        }

        .emoji-btn:hover {
            transform: scale(1.2);
        }

        /* Tap Areas */
        .tap-area {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 30%;
            z-index: 5;
            cursor: pointer;
        }

        .tap-left { left: 0; }
        .tap-right { right: 0; }
    </style>
</head>
<body>
    <div class="story-viewer">
        <div class="story-overlay" onclick="closeViewer()"></div>

        <div class="story-container">
            <!-- Progress Bars -->
            <div class="story-progress">
                @for($i = 0; $i < $stories->count(); $i++)
                    <div class="progress-bar {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}">
                        <div class="progress-fill"></div>
                    </div>
                @endfor
            </div>

            <!-- Header -->
            <div class="story-header">
                <div class="story-user">
                    @if($user->profile && $user->profile->avatar)
                        <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar" class="story-avatar">
                    @else
                        <div class="story-avatar-placeholder">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                    <div class="story-info">
                        <span class="story-username">{{ $user->name }}</span>
                        <span class="story-time" id="story-time"></span>
                    </div>
                </div>
                <div class="story-header-actions">
                    @if($user->id !== auth()->id())
                        <a href="{{ route('chat.start', $user->id) }}" class="story-message" title="Send message">
                            <i class="fas fa-paper-plane"></i>
                        </a>
                    @endif
                    @if($user->id === auth()->id())
                        <button class="story-delete" onclick="deleteStory({{ $stories->first()->id }})" title="Delete story">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="story-content">
                @foreach($stories as $index => $story)
                    <div class="story-slide {{ $index === 0 ? 'active' : '' }}" data-story-id="{{ $story->id }}" data-index="{{ $index }}" data-created-at="{{ $story->created_at }}">
                        @if($story->media_type === 'image')
                            <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story" class="story-media">
                        @else
                            <video autoplay muted class="story-media" playsinline>
                                <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                            </video>
                        @endif
                        @if($story->content)
                            <div class="story-caption">{{ $story->content }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Tap Areas -->
            <div class="tap-area tap-left" onclick="previousStory()"></div>
            <div class="tap-area tap-right" onclick="nextStory()"></div>

            <!-- Navigation -->
            <button class="nav-btn prev-btn" onclick="previousStory()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="nav-btn next-btn" onclick="nextStory()">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Controls -->
            <div class="story-controls">
                @if($user->id === auth()->id())
                    <a href="{{ route('stories.viewers', [$user, $stories->first()]) }}" class="control-btn viewers-btn">
                        <i class="fas fa-eye"></i>
                        <span>{{ $stories->first()->storyViews->count() ?? 0 }}</span>
                    </a>
                @else
                    <div></div>
                @endif
                
                <!-- Message Input - Only show if not viewing own story -->
                @if($user->id !== auth()->id())
                <div class="story-message-input-wrapper">
                    <input type="text" class="story-message-input" id="story-message" placeholder="Send message..." onkeypress="handleMessageKeypress(event)">
                    <button class="story-send-btn" onclick="sendStoryMessage()" id="story-send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                @endif
                
                <button class="control-btn" onclick="toggleReactions()">
                    <i class="fas fa-heart"></i>
                </button>
            </div>

            <!-- Reaction Picker -->
            <div class="reaction-picker" id="reaction-picker">
                <button class="emoji-btn" onclick="reactToStory('❤️')">❤️</button>
                <button class="emoji-btn" onclick="reactToStory('👍')">👍</button>
                <button class="emoji-btn" onclick="reactToStory('🔥')">🔥</button>
                <button class="emoji-btn" onclick="reactToStory('👏')">👏</button>
            </div>
        </div>
    </div>

    <script>
        let currentIndex = 0;
        const stories = document.querySelectorAll('.story-slide');
        const progressBars = document.querySelectorAll('.progress-bar');
        let storyTimer;
        let isPaused = false;

        function startTimer() {
            if (isPaused) return;
            clearTimeout(storyTimer);
            const currentStory = stories[currentIndex];
            const isVideo = currentStory.querySelector('video');
            
            if (isVideo) {
                // For videos, wait for video to end or use a minimum timeout
                const video = isVideo;
                
                // Max timeout is 1 minute (60000ms)
                const maxTimeout = 60000;
                
                // If video is already loaded and has a valid duration
                if (video.duration && !isNaN(video.duration) && video.duration > 0) {
                    // Calculate remaining time if video is playing
                    const remainingTime = (video.duration - video.currentTime) * 1000;
                    // Use at least 1 second minimum, at most the remaining video time + buffer, capped at 1 minute
                    const duration = Math.min(maxTimeout, Math.max(1000, remainingTime + 500));
                    
                    storyTimer = setTimeout(() => {
                        nextStory();
                    }, duration);
                    
                    // Also listen for video ended event as backup
                    video.onended = function() {
                        clearTimeout(storyTimer);
                        nextStory();
                    };
                } else {
                    // If video duration not available, use default 30 seconds
                    // and try to detect when video ends
                    const duration = 30000;
                    
                    storyTimer = setTimeout(() => {
                        nextStory();
                    }, duration);
                    
                    // Try to detect when video ends
                    video.addEventListener('loadedmetadata', function() {
                        if (video.duration && !isNaN(video.duration) && video.duration > 0) {
                            clearTimeout(storyTimer);
                            const remainingTime = (video.duration - video.currentTime) * 1000;
                            const videoDuration = Math.min(maxTimeout, Math.max(1000, remainingTime + 500));
                            
                            storyTimer = setTimeout(() => {
                                nextStory();
                            }, videoDuration);
                            
                            video.onended = function() {
                                clearTimeout(storyTimer);
                                nextStory();
                            };
                        }
                    });
                }
            } else {
                // For images, use 5 seconds
                const duration = 5000;
                storyTimer = setTimeout(() => {
                    nextStory();
                }, duration);
            }
        }

        function pauseTimer() {
            isPaused = true;
            clearTimeout(storyTimer);
            // Also pause the CSS animation
            const activeBar = progressBars[currentIndex];
            if (activeBar) {
                const fill = activeBar.querySelector('.progress-fill');
                if (fill) {
                    fill.classList.add('paused');
                }
            }
        }

        function resumeTimer() {
            isPaused = false;
            // Remove the paused class to resume CSS animation
            const activeBar = progressBars[currentIndex];
            if (activeBar) {
                const fill = activeBar.querySelector('.progress-fill');
                if (fill) {
                    fill.classList.remove('paused');
                }
            }
            startTimer();
        }

        function updateDisplay() {
            stories.forEach((slide, i) => {
                slide.classList.toggle('active', i === currentIndex);
            });

            progressBars.forEach((bar, i) => {
                bar.classList.remove('active', 'completed');
                const fill = bar.querySelector('.progress-fill');
                
                if (i < currentIndex) {
                    bar.classList.add('completed');
                    if (fill) {
                        fill.style.width = '100%';
                        fill.style.animation = 'none';
                    }
                } else if (i === currentIndex) {
                    bar.classList.add('active');
                    // Get the story at this index
                    const storySlide = stories[i];
                    const isVideo = storySlide.querySelector('video');
                    
                    // Set animation duration based on content type
                    let duration = 5; // default seconds for images
                    
                    if (isVideo) {
                        // For videos, we'll try to get the duration
                        const video = isVideo;
                        if (video.duration && !isNaN(video.duration) && video.duration > 0) {
                            duration = Math.ceil(video.duration);
                        } else {
                            duration = 30; // default for videos if duration not available
                        }
                    }
                    
                    if (fill) {
                        // Remove old animation and restart
                        fill.style.animation = 'none';
                        fill.offsetHeight; // Trigger reflow
                        fill.style.animation = `progress ${duration}s linear forwards`;
                        fill.classList.remove('paused');
                    }
                } else {
                    if (fill) {
                        fill.style.width = '0%';
                        fill.style.animation = 'none';
                    }
                }
            });

            const activeStory = stories[currentIndex];
            const timeEl = document.getElementById('story-time');
            if (timeEl && activeStory) {
                const createdAt = new Date(activeStory.dataset.createdAt);
                timeEl.textContent = timeAgo(createdAt);
            }

            const video = activeStory.querySelector('video');
            if (video) {
                video.currentTime = 0;
                video.play();
            }

            startTimer();
        }

        function nextStory() {
            if (currentIndex < stories.length - 1) {
                currentIndex++;
                updateDisplay();
            } else {
                closeViewer();
            }
        }

        function previousStory() {
            if (currentIndex > 0) {
                currentIndex--;
                updateDisplay();
            }
        }

        function closeViewer() {
            clearTimeout(storyTimer);
            window.location.href = '{{ route("stories.index") }}';
        }

        function toggleReactions() {
            const picker = document.getElementById('reaction-picker');
            const isShowing = picker.classList.contains('show');
            
            if (!isShowing) {
                // Opening the picker - pause the timer
                pauseTimer();
            } else {
                // Closing the picker without selecting - resume the timer
                resumeTimer();
            }
            
            picker.classList.toggle('show');
        }

        function reactToStory(emoji) {
            // Close the picker and resume the timer
            document.getElementById('reaction-picker').classList.remove('show');
            resumeTimer();
            
            const storyId = stories[currentIndex].dataset.storyId;
            fetch('/stories/' + storyId + '/react', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ reaction_type: emoji })
            });
        }

        function deleteStory(storyId) {
            if (!confirm('Are you sure you want to delete this story?')) return;
            
            fetch('/stories/' + storyId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store flag in localStorage for the redirect page
                    localStorage.setItem('story_deleted', 'true');
                    window.location.href = '{{ route("stories.index") }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Failed to delete story', 'error');
                }
            });
        }

        function timeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);
            if (seconds < 60) return 'Just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + 'm ago';
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + 'h ago';
            return Math.floor(hours / 24) + 'd ago';
        }

        function handleMessageKeypress(e) {
            if (e.key === 'Enter') {
                sendStoryMessage();
            }
        }

        async function sendStoryMessage() {
            const input = document.getElementById('story-message');
            const message = input.value.trim();
            const sendBtn = document.getElementById('story-send-btn');
            
            if (!message) return;
            
            // Disable button to prevent double send
            sendBtn.disabled = true;
            
            const storyAuthorId = {{ $user->id }};
            const storyAuthorName = '{{ $user->name }}';
            
            // Add "from story" indicator to message
            const messageWithIndicator = `📸 ${message}`;
            
            try {
                // First, get or create conversation
                const response = await fetch('/chat/start/' + storyAuthorId, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    // Handle error
                    if (typeof showToast === 'function') {
                        showToast(data.error || 'Failed to send message', 'error');
                    }
                    sendBtn.disabled = false;
                    return;
                }
                
                if (data.conversation_id) {
                    // Now send the message to this conversation
                    const messageResponse = await fetch('/chat/' + data.conversation_id, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: messageWithIndicator })
                    });
                    
                    const messageData = await messageResponse.json();
                    
                    if (messageData.success) {
                        // Show success feedback
                        if (typeof showToast === 'function') {
                            showToast('Message sent! 📸', 'success');
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('Failed to send message', 'error');
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Failed to send message', 'error');
                }
            }
            
            input.value = '';
            sendBtn.disabled = false;
            // Resume timer after sending
            resumeTimer();
        }
        
        // Pause timer when message input is focused (only if element exists)
        const messageInput = document.getElementById('story-message');
        if (messageInput) {
            messageInput.addEventListener('focus', function() {
                pauseTimer();
            });
            
            // Resume timer when message input loses focus (but only if there's no text)
            messageInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    resumeTimer();
                }
            });
            
            // Keep timer paused while typing
            messageInput.addEventListener('input', function() {
                pauseTimer();
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') nextStory();
            if (e.key === 'ArrowLeft') previousStory();
            if (e.key === 'Escape') closeViewer();
        });

        document.addEventListener('DOMContentLoaded', () => {
            updateDisplay();
        });
    </script>
</body>
</html>
