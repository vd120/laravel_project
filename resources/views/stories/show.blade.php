<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Story - {{ $user->username }}</title>
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

        .story-sending-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            padding: 6px 12px;
            background: rgba(29, 155, 240, 0.3);
            border-radius: 20px;
            backdrop-filter: blur(8px);
        }

        .story-sending-indicator i {
            font-size: 14px;
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

        /* Reaction Button State */
        .reaction-btn.has-reaction i {
            color: #ef4444;
            animation: heartBeat 1.5s infinite;
        }

        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .user-reaction-display {
            position: absolute;
            bottom: 80px;
            right: 80px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: reactionPop 0.3s ease-out;
            z-index: 20;
        }

        .user-reaction-emoji {
            animation: reactionPulse 2s infinite;
        }

        @keyframes reactionPop {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes reactionPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
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
                    <img src="{{ $user->avatar_url }}" alt="Avatar" class="story-avatar">
                    <div class="story-info">
                        <span class="story-username">{{ $user->username }}</span>
                        <span class="story-time" id="story-time"></span>
                    </div>
                </div>
                <div class="story-header-actions">
                    @if($user->id === auth()->id())
                        <button class="story-delete" onclick="deleteStory('{{ $stories->first()->slug }}')" title="Delete story">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="story-content">
                @foreach($stories as $index => $story)
                    <div class="story-slide {{ $index === 0 ? 'active' : '' }}" data-story-slug="{{ $story->slug }}" data-index="{{ $index }}" data-created-at="{{ $story->created_at }}">
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
                    <a href="{{ route('stories.viewers', [$user, $stories->first()]) }}" class="control-btn viewers-btn" title="View who watched">
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
                    <div class="story-sending-indicator" id="story-sending-indicator" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Sending reply...</span>
                    </div>
                </div>
                @endif

                <button class="control-btn reaction-btn" onclick="toggleReaction()" id="reaction-btn">
                    <i class="fas fa-heart"></i>
                </button>
            </div>

        </div>
    </div>

    <script>
        let currentIndex = 0;
        const stories = document.querySelectorAll('.story-slide');
        const progressBars = document.querySelectorAll('.progress-bar');
        let storyTimer = null;
        let isPaused = false;
        let messageInput = null;


        // Clear timer completely
        function clearStoryTimer() {
            if (storyTimer) {
                clearTimeout(storyTimer);
                storyTimer = null;
            }
        }

        // Initialize message input reference
        function initMessageInput() {
            messageInput = document.getElementById('story-message');
            if (messageInput) {
                // Focus: Pause timer immediately
                messageInput.addEventListener('focus', function() {
                    isPaused = true;
                    clearStoryTimer();
                    
                    // Also pause CSS animation
                    const activeBar = progressBars[currentIndex];
                    if (activeBar) {
                        const fill = activeBar.querySelector('.progress-fill');
                        if (fill) {
                            fill.classList.add('paused');
                            fill.style.animationPlayState = 'paused';
                        }
                    }
                });

                // Blur: Resume only if empty
                messageInput.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        isPaused = false;
                        
                        // Resume CSS animation
                        const activeBar = progressBars[currentIndex];
                        if (activeBar) {
                            const fill = activeBar.querySelector('.progress-fill');
                            if (fill) {
                                fill.classList.remove('paused');
                                fill.style.animationPlayState = 'running';
                            }
                        }
                        
                        startTimer();
                    }
                });

                // Input: Keep timer paused while typing
                messageInput.addEventListener('input', function() {
                    isPaused = true;
                    clearStoryTimer();
                });

                // Also pause on click/touch
                messageInput.addEventListener('click', function() {
                    isPaused = true;
                    clearStoryTimer();
                });
            }
        }

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
            clearStoryTimer();
            // Also pause the CSS animation
            const activeBar = progressBars[currentIndex];
            if (activeBar) {
                const fill = activeBar.querySelector('.progress-fill');
                if (fill) {
                    fill.classList.add('paused');
                    fill.style.animationPlayState = 'paused';
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
                    fill.style.animationPlayState = 'running';
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
                        
                        // If paused, keep it paused
                        if (isPaused) {
                            fill.classList.add('paused');
                            fill.style.animationPlayState = 'paused';
                        } else {
                            fill.classList.remove('paused');
                            fill.style.animationPlayState = 'running';
                        }
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
            // Check if user came from home page
            const urlParams = new URLSearchParams(window.location.search);
            const from = urlParams.get('from');
            if (from === 'home') {
                window.location.href = '{{ route("home") }}';
            } else {
                window.location.href = '{{ route("stories.index") }}';
            }
        }

        function toggleReaction() {
            const story = stories[currentIndex];
            const storySlug = story.dataset.storySlug;
            const username = '{{ $user->username }}';
            
            // Check if user already has a reaction
            fetch('/stories/' + username + '/' + storySlug + '/check-reaction')
                .then(response => response.json())
                .then(data => {
                    if (data.has_reaction) {
                        // User already reacted - REMOVE reaction
                        removeReaction(storySlug, username);
                    } else {
                        // User hasn't reacted - ADD default heart reaction
                        addReaction(storySlug, username, '❤️');
                    }
                })
                .catch(err => console.error('Error checking reaction:', err));
        }

        function addReaction(storySlug, username, emoji) {
            fetch('/stories/' + username + '/' + storySlug + '/react', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ reaction_type: emoji })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update reaction button to red heart
                    updateReactionButton(true);
                }
            })
            .catch(err => console.error('Error adding reaction:', err));
        }

        function removeReaction(storySlug, username) {
            fetch('/stories/' + username + '/' + storySlug + '/react', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update reaction button to white heart
                    updateReactionButton(false);
                }
            })
            .catch(err => console.error('Error removing reaction:', err));
        }

        function updateReactionButton(hasReaction) {
            const btn = document.getElementById('reaction-btn');
            if (!btn) return;

            if (hasReaction) {
                btn.classList.add('has-reaction');
            } else {
                btn.classList.remove('has-reaction');
            }
        }

        function checkUserReaction() {
            const story = stories[currentIndex];
            if (!story) return;
            const storySlug = story.dataset.storySlug;
            const username = '{{ $user->username }}';

            fetch('/stories/' + username + '/' + storySlug + '/check-reaction')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.has_reaction) {
                        updateReactionButton(true);
                    }
                })
                .catch(err => console.error('Error checking reaction:', err));
        }

        function showUserReaction(emoji) {
            const display = document.getElementById('user-reaction-display');
            const emojiSpan = document.getElementById('user-reaction-emoji');

            emojiSpan.textContent = emoji;
            display.style.display = 'flex';

            // Hide after 3 seconds
            setTimeout(() => {
                display.style.display = 'none';
            }, 3000);
        }

        function deleteStory(storySlug) {
            if (!confirm('Are you sure you want to delete this story?')) return;

            const username = '{{ $user->username }}';
            fetch('/stories/' + username + '/' + storySlug, {
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
            const sendBtn = document.getElementById('story-send-btn');
            const sendingIndicator = document.getElementById('story-sending-indicator');
            const message = input.value.trim();

            if (!message) return;

            // Pause timer while sending
            pauseTimer();

            // Hide send button, show sending indicator
            sendBtn.style.display = 'none';
            sendingIndicator.style.display = 'flex';

            const storyAuthorId = '{{ $user->id }}';
            const storyAuthorName = '{{ $user->username }}';
            const currentStorySlug = stories[currentIndex].dataset.storySlug;

            // Add "from story" indicator to message
            const messageWithIndicator = `📸 Reply to your story: ${message}`;

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
                    sendBtn.style.display = 'flex';
                    sendingIndicator.style.display = 'none';
                    resumeTimer();
                    return;
                }

                // Use slug for route (Conversation model uses slug as route key)
                const conversationSlug = data.slug;

                if (conversationSlug) {
                    // Now send the message to this conversation
                    const messageResponse = await fetch('/chat/' + conversationSlug, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            content: messageWithIndicator,
                            story_slug: currentStorySlug
                        })
                    });

                    const messageData = await messageResponse.json();

                    if (messageData.success) {
                        // Show success feedback with WhatsApp-style toast
                        // Store in sessionStorage so it persists even if page closes
                        sessionStorage.setItem('storyReplySent', JSON.stringify({
                            message: 'Reply sent to ' + storyAuthorName + ' 📸',
                            type: 'success',
                            time: Date.now()
                        }));

                        if (typeof showToast === 'function') {
                            showToast('Reply sent to ' + storyAuthorName + ' 📸', 'success', 5000);
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('Failed to send message', 'error', 5000);
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('Failed to send message', 'error', 5000);
                }
            }

            // Clear input and restore button
            input.value = '';
            sendBtn.style.display = 'flex';
            sendingIndicator.style.display = 'none';
            
            // Resume timer after sending
            resumeTimer();
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            initMessageInput();
            updateDisplay();
            startTimer();

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') nextStory();
                if (e.key === 'ArrowLeft') previousStory();
                if (e.key === 'Escape') closeViewer();
            });
            
            // Check if there's a pending story reply toast from navigation
            const pendingToast = sessionStorage.getItem('storyReplySent');
            if (pendingToast) {
                const toastData = JSON.parse(pendingToast);
                // Only show if it's recent (within 10 seconds)
                if (Date.now() - toastData.time < 10000) {
                    setTimeout(() => {
                        if (typeof showToast === 'function') {
                            showToast(toastData.message, toastData.type, 5000);
                        }
                        sessionStorage.removeItem('storyReplySent');
                    }, 500);
                } else {
                    sessionStorage.removeItem('storyReplySent');
                }
            }
            
            // Check if current user has already reacted to this story
            checkUserReaction();
        });
    </script>
</body>
</html>
