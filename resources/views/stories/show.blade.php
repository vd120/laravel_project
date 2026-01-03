@extends('layouts.app')

@section('title', '@' . $user->name . ' - Stories')

@section('content')
<div class="story-viewer">
    <div class="story-overlay" onclick="closeViewer()"></div>

    <div class="story-container">
        <!-- Story Header -->
        <div class="story-header">
            <div class="story-user-info">
                @if($user->profile && $user->profile->avatar)
                    <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="{{ "@" . $user->name }}" class="story-avatar">
                @else
                    <div class="story-avatar-placeholder">{{ substr($user->name, 0, 1) }}</div>
                @endif
                <div class="story-details">
                    <span class="story-username">{{ "@" . $user->name }}</span>
                    @if($user->id === auth()->id() && $stories->count() > 0)
                    <a href="{{ route('stories.viewers', [$user, $stories->first()]) }}" class="story-views">
                        <i class="fas fa-eye"></i> <span id="current-views">{{ $stories->first()->views ?? 0 }}</span>
                    </a>
                    @endif
                    <span class="story-time" id="story-time"></span>
                </div>
            </div>

            @if($user->id === auth()->id())
            <div class="story-actions">
                <button onclick="deleteCurrentStory()" class="delete-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endif
        </div>

        <!-- Story Content -->
        <div class="story-content" id="story-content">
            @foreach($stories as $index => $story)
            <div class="story-slide {{ $index === 0 ? 'active' : '' }}" data-story-id="{{ $story->id }}" data-index="{{ $index }}">
                @if($story->media_type === 'image')
                    <img src="{{ asset('storage/' . $story->media_path) }}" alt="Story" class="story-media">
                @else
                    <video autoplay muted class="story-media" playsinline>
                        <source src="{{ asset('storage/' . $story->media_path) }}" type="video/mp4">
                    </video>
                @endif

                @if($story->content)
                <div class="story-caption">
                    {{ $story->content }}
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Story Progress -->
        <div class="story-progress" id="story-progress">
            @for($i = 0; $i < $stories->count(); $i++)
            <div class="progress-bar {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}">
                <div class="progress-fill"></div>
            </div>
            @endfor
        </div>

        <!-- Video Progress (only for videos) -->
        <div class="video-progress" id="video-progress" style="display: none;">
            <div class="video-progress-bar">
                <div class="video-progress-fill" id="video-progress-fill"></div>
            </div>
        </div>

        <!-- Navigation -->
        <button class="nav-btn prev-btn" onclick="previousStory()">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="nav-btn next-btn" onclick="nextStory()">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Audio Controls (only for videos) -->
        <div class="audio-controls" id="audio-controls" style="display: none;">
            <button class="audio-btn" id="mute-btn" onclick="toggleMute()">
                <i class="fas fa-volume-mute"></i>
            </button>
        </div>

        <!-- Reaction Controls -->
        <div class="reaction-controls" id="reaction-controls">
            <button class="reaction-btn" id="reaction-btn" onclick="toggleReactions()">
                <i class="far fa-heart"></i>
            </button>
            <div class="reaction-picker" id="reaction-picker" style="display: none;">
                <button class="emoji-btn" onclick="reactToStory('❤️')">❤️</button>
                <button class="emoji-btn" onclick="reactToStory('👍')">👍</button>
                <button class="emoji-btn" onclick="reactToStory('😊')">😊</button>
                <button class="emoji-btn" onclick="reactToStory('😮')">😮</button>
                <button class="emoji-btn" onclick="reactToStory('😢')">😢</button>
                <button class="emoji-btn" onclick="reactToStory('😡')">😡</button>
            </div>
        </div>
    </div>
</div>

<style>
.story-viewer {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.story-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.story-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 100%;
    max-height: 700px;
    background: black;
    border-radius: 12px;
    overflow: hidden;
    z-index: 10001;
}

.story-header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 10002;
    padding: 20px;
    background: linear-gradient(180deg, rgba(0,0,0,0.5) 0%, transparent 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.story-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.story-avatar,
.story-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid white;
}

.story-avatar {
    object-fit: cover;
}

.story-avatar-placeholder {
    background: var(--twitter-blue);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.story-details {
    color: white;
}

.story-username {
    display: block;
    font-weight: 600;
    font-size: 16px;
}

.story-time {
    display: block;
    font-size: 12px;
    opacity: 0.8;
}

.story-views {
    display: inline;
    font-size: 12px;
    color: #ffffff;
    font-weight: 500;
    margin-left: 8px;
    text-decoration: none;
}

.story-views:hover {
    color: #e0e0e0;
}

.story-views i {
    margin-right: 4px;
}

.delete-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.delete-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.story-content {
    width: 100%;
    height: 100%;
    position: relative;
}

.story-slide {
    position: absolute;
    top: 0;
    left: 0;
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
    bottom: 80px;
    left: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    word-wrap: break-word;
}

.story-progress {
    position: absolute;
    top: 70px;
    left: 20px;
    right: 20px;
    z-index: 10002;
    display: flex;
    gap: 4px;
}

.progress-bar {
    flex: 1;
    height: 3px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar.active .progress-fill {
    animation: progress linear forwards;
}

.progress-fill {
    height: 100%;
    background: white;
    width: 0%;
    border-radius: 2px;
}

.video-progress {
    position: absolute;
    top: 10px;
    left: 20px;
    right: 20px;
    z-index: 10002;
}

.video-progress-bar {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    overflow: hidden;
}

.video-progress-fill {
    height: 100%;
    background: white;
    width: 0%;
    border-radius: 2px;
    transition: width 0.1s linear;
}

@keyframes progress {
    to {
        width: 100%;
    }
}

.nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    z-index: 10002;
    transition: background-color 0.2s ease;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.prev-btn {
    left: 10px;
}

.next-btn {
    right: 10px;
}

.audio-controls {
    position: absolute;
    bottom: 20px;
    right: 20px;
    z-index: 10002;
}

.audio-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background-color 0.2s ease;
}

.audio-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.audio-btn i {
    margin: 0;
}

.reaction-controls {
    position: absolute;
    bottom: 20px;
    left: 20px;
    z-index: 10003;
}

.reaction-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background-color 0.2s ease;
}

.reaction-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.reaction-btn i {
    margin: 0;
}

.reaction-picker {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.emoji-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.2s ease;
}

.emoji-btn:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: scale(1.1);
}

@media (max-width: 480px) {
    .story-container {
        max-width: 100%;
        max-height: 100%;
        border-radius: 0;
    }

    .story-header {
        padding: 15px;
    }

    .story-progress {
        top: 60px;
        left: 15px;
        right: 15px;
    }

    .story-caption {
        bottom: 100px;
        left: 15px;
        right: 15px;
    }

    .audio-controls {
        bottom: 15px;
        right: 15px;
    }

    .reaction-controls {
        bottom: 15px;
        left: 15px;
    }

    .reaction-picker {
        gap: 6px;
        margin-top: 8px;
    }

    .emoji-btn {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
}
</style>

<script>
let currentStoryIndex = 0;
let stories = @json($stories);
let storyTimer;
let imageProgressInterval;
let imageStartTime;

function initializeStory() {
    updateStoryDisplay();
    startTimer();
}

function updateStoryDisplay() {
    // Update active slide
    document.querySelectorAll('.story-slide').forEach((slide, index) => {
        slide.classList.toggle('active', index === currentStoryIndex);
    });

    // Update progress bars visibility based on media type
    const currentStory = stories[currentStoryIndex];
    const storyProgress = document.getElementById('story-progress');
    const videoProgress = document.getElementById('video-progress');

    if (currentStory.media_type === 'video') {
        // Show video progress bar, hide story progress bars
        storyProgress.style.display = 'none';
        videoProgress.style.display = 'block';

        // Clear any image progress interval
        clearInterval(imageProgressInterval);
    } else {
        // Show story progress bars, hide video progress bar
        storyProgress.style.display = 'flex';
        videoProgress.style.display = 'none';

        // Update progress bars - remove animation and set to real-time progress
        document.querySelectorAll('.progress-bar').forEach((bar, index) => {
            const progressFill = bar.querySelector('.progress-fill');

            if (index < currentStoryIndex) {
                // Completed stories - full progress
                bar.classList.remove('active');
                progressFill.style.width = '100%';
                progressFill.style.animationDuration = '';
            } else if (index === currentStoryIndex) {
                // Current story - start from 0%
                bar.classList.add('active');
                progressFill.style.width = '0%';
                progressFill.style.animationDuration = '';
            } else {
                // Future stories - empty
                bar.classList.remove('active');
                progressFill.style.width = '0%';
                progressFill.style.animationDuration = '';
            }
        });
    }

    // Update time
    const timeElement = document.getElementById('story-time');
    const createdAt = new Date(currentStory.created_at);
    timeElement.textContent = getTimeAgo(createdAt);

    // Handle video autoplay and audio controls
    const activeSlide = document.querySelector('.story-slide.active');
    const video = activeSlide.querySelector('video');
    const audioControls = document.getElementById('audio-controls');

    if (video) {
        // Show audio controls for videos
        audioControls.style.display = 'block';
        video.currentTime = 0;
        video.muted = true; // Start muted
        updateMuteButton(true);

        // Remove any existing event listeners to avoid duplicates
        video.removeEventListener('ended', handleVideoEnd);
        video.removeEventListener('timeupdate', updateVideoProgress);

        // Add event listener for when video ends
        video.addEventListener('ended', handleVideoEnd);

        // Add event listener for video progress tracking
        video.addEventListener('timeupdate', updateVideoProgress);

        video.play();
    } else {
        // Hide audio controls for images
        audioControls.style.display = 'none';
    }
}

function startTimer() {
    clearTimeout(storyTimer);
    clearInterval(imageProgressInterval);

    // Check if current story is a video
    const currentStory = stories[currentStoryIndex];
    const duration = currentStory.media_type === 'video' ? 30000 : 5000; // 30 seconds for videos, 5 seconds for images

    if (currentStory.media_type === 'image') {
        // Start real-time progress tracking for images
        imageStartTime = Date.now();
        imageProgressInterval = setInterval(updateImageProgress, 100); // Update every 100ms
    }

    storyTimer = setTimeout(() => {
        nextStory();
    }, duration);
}

function nextStory() {
    if (currentStoryIndex < stories.length - 1) {
        currentStoryIndex++;
        updateStoryDisplay();
        startTimer();
    } else {
        closeViewer();
    }
}

function previousStory() {
    if (currentStoryIndex > 0) {
        currentStoryIndex--;
        updateStoryDisplay();
        startTimer();
    }
}

function closeViewer() {
    clearTimeout(storyTimer);
    window.location.href = '{{ route("stories.index") }}';
}

function deleteCurrentStory() {
    if (confirm('Are you sure you want to delete this story?')) {
        // Immediately stop the video and clear timers
        const activeSlide = document.querySelector('.story-slide.active');
        const video = activeSlide.querySelector('video');
        if (video) {
            video.pause();
            video.currentTime = 0;
        }

        // Clear any running timers
        clearTimeout(storyTimer);
        clearInterval(imageProgressInterval);

        const currentStory = stories[currentStoryIndex];

        // Make the delete request and redirect after success
        const deleteUrl = '/stories/' + currentStory.id;
        console.log('Delete URL:', deleteUrl);
        console.log('Full URL:', window.location.origin + deleteUrl);
        console.log('Story ID:', currentStory.id);
        console.log('Current page URL:', window.location.href);

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            console.log('Response headers:', response.headers);

            // Check if the response is successful (status 200-299)
            if (response.status >= 200 && response.status < 300) {
                console.log('Story deleted successfully');
                // Redirect to stories index after successful deletion
                window.location.href = '{{ route("stories.index") }}';
            } else {
                console.error('Delete request failed with status:', response.status);
                return response.text().then(text => {
                    console.error('Response body:', text);
                    alert('Failed to delete story. Please try again.');
                });
            }
        })
        .catch(error => {
            console.error('Delete request failed:', error);
            alert('Failed to delete story. Please try again.');
        });
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
    return Math.floor(diffInSeconds / 86400) + 'd ago';
}

// Keyboard navigation
document.addEventListener('keydown', function(event) {
    switch(event.key) {
        case 'ArrowRight':
        case ' ':
            event.preventDefault();
            nextStory();
            break;
        case 'ArrowLeft':
            event.preventDefault();
            previousStory();
            break;
        case 'Escape':
            closeViewer();
            break;
    }
});

// Touch navigation
let touchStartX = 0;
document.addEventListener('touchstart', function(event) {
    touchStartX = event.touches[0].clientX;
});

document.addEventListener('touchend', function(event) {
    const touchEndX = event.changedTouches[0].clientX;
    const diff = touchStartX - touchEndX;

    if (Math.abs(diff) > 50) { // Minimum swipe distance
        if (diff > 0) {
            nextStory(); // Swipe left
        } else {
            previousStory(); // Swipe right
        }
    }
});

// Audio controls functions
function toggleMute() {
    const activeSlide = document.querySelector('.story-slide.active');
    const video = activeSlide.querySelector('video');

    if (video) {
        video.muted = !video.muted;
        updateMuteButton(video.muted);
    }
}

// Handle video end event
function handleVideoEnd() {
    clearTimeout(storyTimer);
    nextStory();
}

// Update video progress bar in real-time
function updateVideoProgress(event) {
    const video = event.target;
    const progressFill = document.getElementById('video-progress-fill');

    if (video.duration && video.duration > 0) {
        const progress = (video.currentTime / video.duration) * 100;
        progressFill.style.width = progress + '%';
    }
}

function updateMuteButton(isMuted) {
    const muteBtn = document.getElementById('mute-btn');
    const icon = muteBtn.querySelector('i');

    if (isMuted) {
        icon.className = 'fas fa-volume-mute';
    } else {
        icon.className = 'fas fa-volume-up';
    }
}

// Update image progress bar in real-time
function updateImageProgress() {
    const elapsed = Date.now() - imageStartTime;
    const duration = 5000; // 5 seconds for images
    const progress = Math.min((elapsed / duration) * 100, 100);

    // Find the current active progress bar
    const activeBar = document.querySelector('.progress-bar.active');
    if (activeBar) {
        const progressFill = activeBar.querySelector('.progress-fill');
        if (progressFill) {
            progressFill.style.width = progress + '%';
        }
    }
}

// Reaction functions
function toggleReactions() {
    const picker = document.getElementById('reaction-picker');
    if (picker.style.display === 'none' || picker.style.display === '') {
        picker.style.display = 'flex';
    } else {
        picker.style.display = 'none';
    }
}

function reactToStory(reactionType) {
    const currentStory = stories[currentStoryIndex];
    const reactionBtn = document.getElementById('reaction-btn');
    const icon = reactionBtn.querySelector('i');

    // Change button to filled heart to show reaction was added
    icon.className = 'fas fa-heart';
    reactionBtn.style.color = '#ff4757'; // Red color for reacted state

    // Hide picker after reaction
    document.getElementById('reaction-picker').style.display = 'none';

    // Send reaction to server
    fetch('{{ url("/stories") }}/' + currentStory.id + '/react', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reaction_type: reactionType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Optionally show feedback
            console.log('Reaction added successfully');
        }
    })
    .catch(error => {
        console.error('Error adding reaction:', error);
        // Reset button state on error
        icon.className = 'far fa-heart';
        reactionBtn.style.color = 'white';
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    initializeStory();

    // Initialize real-time manager for story updates
    if (window.realTimeManager) {
        window.realTimeManager.init();
    }
});
</script>
@endsection
