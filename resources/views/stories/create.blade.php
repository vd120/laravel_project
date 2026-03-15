@extends('layouts.app')

@section('title', __('messages.create_story'))

@section('content')
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    </script>
@endif

<link rel="stylesheet" href="{{ asset('css/stories-create.css') }}">

<div class="create-story-page">
    <div class="page-header">
        <h1>{{ __('messages.create_story') }}</h1>
        <a href="{{ route('stories.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            {{ __('messages.cancel') }}
        </a>
    </div>

    <div class="create-story-form">
        <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" id="story-form">
            @csrf

            <div class="upload-section">
                <label for="media" class="upload-label">
                    <div class="upload-placeholder" id="upload-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>{{ __('messages.click_to_upload') }}</span>
                        <small>{{ __('messages.max_50mb') }}</small>
                    </div>
                    <input type="file" id="media" name="media" accept="image/*,video/*" required onchange="previewMedia(this)">
                </label>

                <div class="media-preview" id="media-preview" style="display: none;">
                    <img id="image-preview" src="" alt="{{ __('messages.story') }}" style="display: none;">
                    <div id="video-container" style="display: none;">
                        <video id="video-preview" controls></video>
                        <div class="video-trimmer" id="video-trimmer">
                            <div class="trim-info">
                                <span>{{ __('messages.trim_video') }}</span>
                                <span id="trim-duration">0s</span>
                            </div>
                            <div class="trim-controls">
                                <div class="trim-input-group">
                                    <label>{{ __('messages.trim_start') }}</label>
                                    <input type="range" id="trim-start" min="0" max="60" step="0.1" value="0" oninput="updateTrimRange()">
                                    <span id="trim-start-time">0:00</span>
                                </div>
                                <div class="trim-input-group">
                                    <label>{{ __('messages.trim_end') }}</label>
                                    <input type="range" id="trim-end" min="0" max="60" step="0.1" value="60" oninput="updateTrimRange()">
                                    <span id="trim-end-time">1:00</span>
                                </div>
                            </div>
                            <div class="trim-preview">
                                <button type="button" class="btn-trim-preview" onclick="previewTrim()">
                                    <i class="fas fa-play"></i> {{ __('messages.preview_trim') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="remove-media" onclick="removeMedia()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="content">{{ __('messages.caption_optional') }}</label>
                <textarea name="content" id="content" rows="3" maxlength="5000" placeholder="{{ __('messages.add_caption') }}"></textarea>
                <span class="char-count" id="char-count">0/280</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="submit-btn" disabled>
                    <i class="fas fa-paper-plane"></i>
                    {{ __('messages.post_story') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let videoDuration = 0;

function previewMedia(input) {
    const preview = document.getElementById('media-preview');
    const placeholder = document.getElementById('upload-placeholder');
    const imagePreview = document.getElementById('image-preview');
    const videoContainer = document.getElementById('video-container');
    const submitBtn = document.getElementById('submit-btn');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileType = file.type;

        placeholder.style.display = 'none';
        preview.style.display = 'block';

        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                videoContainer.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else if (fileType.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const videoPreview = document.getElementById('video-preview');
                videoPreview.src = e.target.result;
                videoContainer.style.display = 'block';
                imagePreview.style.display = 'none';
                
                // Get video duration when metadata loads
                videoPreview.onloadedmetadata = function() {
                    videoDuration = videoPreview.duration;
                    initVideoTrimmer(videoDuration);
                };
            };
            reader.readAsDataURL(file);
        }

        submitBtn.disabled = false;
    }
}

function initVideoTrimmer(duration) {
    const maxTrim = 60; // max 60 seconds
    const actualDuration = Math.min(duration, maxTrim);
    
    videoDuration = actualDuration;
    
    const trimStart = document.getElementById('trim-start');
    const trimEnd = document.getElementById('trim-end');
    const trimDuration = document.getElementById('trim-duration');
    const trimStartTime = document.getElementById('trim-start-time');
    const trimEndTime = document.getElementById('trim-end-time');
    
    // Update max values based on video duration
    trimStart.max = actualDuration;
    trimEnd.max = actualDuration;
    
    // Reset to max 60 seconds or video duration
    if (actualDuration > 60) {
        trimEnd.value = 60;
    } else {
        trimEnd.value = actualDuration;
    }
    trimStart.value = 0;
    
    updateTrimRange();
}

function updateTrimRange() {
    const trimStart = document.getElementById('trim-start');
    const trimEnd = document.getElementById('trim-end');
    const trimDuration = document.getElementById('trim-duration');
    const trimStartTime = document.getElementById('trim-start-time');
    const trimEndTime = document.getElementById('trim-end-time');
    
    let start = parseFloat(trimStart.value);
    let end = parseFloat(trimEnd.value);
    
    // Ensure start is before end
    if (start >= end) {
        start = end - 1;
        trimStart.value = start;
    }
    
    // Ensure end is at most 60 seconds after start
    if (end - start > 60) {
        end = start + 60;
        trimEnd.value = end;
    }
    
    const duration = end - start;
    trimDuration.textContent = duration.toFixed(1) + 's';
    trimStartTime.textContent = formatTime(start);
    trimEndTime.textContent = formatTime(end);
}

function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return mins + ':' + (secs < 10 ? '0' : '') + secs;
}

function previewTrim() {
    const videoPreview = document.getElementById('video-preview');
    const start = parseFloat(document.getElementById('trim-start').value);
    const end = parseFloat(document.getElementById('trim-end').value);
    
    videoPreview.currentTime = start;
    videoPreview.play();
    
    // Stop at end time
    const checkTime = setInterval(function() {
        if (videoPreview.currentTime >= end) {
            videoPreview.pause();
            videoPreview.currentTime = start;
            clearInterval(checkTime);
        }
    }, 100);
}

// Add hidden fields for trim values
document.getElementById('story-form').addEventListener('submit', function(e) {
    const videoContainer = document.getElementById('video-container');
    if (videoContainer.style.display !== 'none') {
        // Video is being uploaded - add trim values
        const trimStart = document.createElement('input');
        trimStart.type = 'hidden';
        trimStart.name = 'trim_start';
        trimStart.value = document.getElementById('trim-start').value;
        
        const trimEnd = document.createElement('input');
        trimEnd.type = 'hidden';
        trimEnd.name = 'trim_end';
        trimEnd.value = document.getElementById('trim-end').value;
        
        this.appendChild(trimStart);
        this.appendChild(trimEnd);
    }
});

function removeMedia() {
    const input = document.getElementById('media');
    const preview = document.getElementById('media-preview');
    const placeholder = document.getElementById('upload-placeholder');
    const imagePreview = document.getElementById('image-preview');
    const videoContainer = document.getElementById('video-container');
    const submitBtn = document.getElementById('submit-btn');

    input.value = '';
    preview.style.display = 'none';
    placeholder.style.display = 'block';
    imagePreview.src = '';
    document.getElementById('video-preview').src = '';
    videoContainer.style.display = 'none';
    submitBtn.disabled = true;
    videoDuration = 0;
}

// Character counter
document.getElementById('content').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('char-count').textContent = count + '/280';
});
</script>
@endsection
