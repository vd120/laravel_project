@extends('layouts.app')

@section('title', 'Create Story')

@section('content')
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('{{ session('success') }}', 'success');
        });
    </script>
@endif

<div class="create-story-page">
    <div class="page-header">
        <h1>Create Story</h1>
        <a href="{{ route('stories.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Cancel
        </a>
    </div>

    <div class="create-story-form">
        <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" id="story-form">
            @csrf

            <div class="upload-section">
                <label for="media" class="upload-label">
                    <div class="upload-placeholder" id="upload-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Click to upload image or video</span>
                        <small>Max 50MB • JPG, PNG, GIF, MP4</small>
                    </div>
                    <input type="file" id="media" name="media" accept="image/*,video/*" required onchange="previewMedia(this)">
                </label>

                <div class="media-preview" id="media-preview" style="display: none;">
                    <img id="image-preview" src="" alt="Preview" style="display: none;">
                    <div id="video-container" style="display: none;">
                        <video id="video-preview" controls></video>
                        <div class="video-trimmer" id="video-trimmer">
                            <div class="trim-info">
                                <span>Trim Video (max 60 seconds)</span>
                                <span id="trim-duration">0s</span>
                            </div>
                            <div class="trim-controls">
                                <div class="trim-input-group">
                                    <label>Start:</label>
                                    <input type="range" id="trim-start" min="0" max="60" step="0.1" value="0" oninput="updateTrimRange()">
                                    <span id="trim-start-time">0:00</span>
                                </div>
                                <div class="trim-input-group">
                                    <label>End:</label>
                                    <input type="range" id="trim-end" min="0" max="60" step="0.1" value="60" oninput="updateTrimRange()">
                                    <span id="trim-end-time">1:00</span>
                                </div>
                            </div>
                            <div class="trim-preview">
                                <button type="button" class="btn-trim-preview" onclick="previewTrim()">
                                    <i class="fas fa-play"></i> Preview Trim
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
                <label for="content">Caption (optional)</label>
                <textarea name="content" id="content" rows="3" maxlength="5000" placeholder="Add a caption to your story..."></textarea>
                <span class="char-count" id="char-count">0/280</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="submit-btn" disabled>
                    <i class="fas fa-paper-plane"></i>
                    Post Story
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.create-story-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 24px 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.create-story-form {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.upload-section {
    margin-bottom: 24px;
}

.upload-label {
    display: block;
    cursor: pointer;
}

.upload-label input {
    display: none;
}

.upload-placeholder {
    border: 2px dashed var(--border-color);
    border-radius: 16px;
    padding: 60px 40px;
    text-align: center;
    transition: all 0.2s ease;
    background: var(--twitter-light);
}

.upload-placeholder:hover {
    border-color: var(--twitter-blue);
    background: var(--hover-bg);
}

.upload-placeholder i {
    font-size: 48px;
    color: var(--twitter-blue);
    margin-bottom: 16px;
    display: block;
}

.upload-placeholder span {
    display: block;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 8px;
}

.upload-placeholder small {
    display: block;
    font-size: 13px;
    color: var(--twitter-gray);
}

.media-preview {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: #000;
}

.media-preview img,
.media-preview video {
    width: 100%;
    max-height: 500px;
    object-fit: contain;
    display: block;
}

.remove-media {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.remove-media:hover {
    background: rgba(0,0,0,0.8);
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: var(--twitter-dark);
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
    font-family: inherit;
    background: var(--input-bg);
    color: var(--twitter-dark);
    resize: vertical;
    min-height: 80px;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

.char-count {
    display: block;
    text-align: right;
    font-size: 12px;
    color: var(--twitter-gray);
    margin-top: 6px;
}

.form-actions {
    display: flex;
    justify-content: center;
}

.btn-primary {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 24px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
    background: #1991DB;
    transform: translateY(-2px);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: var(--card-bg);
    color: var(--twitter-gray);
    border: 2px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: var(--hover-bg);
    border-color: var(--twitter-blue);
}

@media (max-width: 768px) {
    .create-story-page {
        padding: 16px;
    }

    .create-story-form {
        padding: 20px;
    }

    .upload-placeholder {
        padding: 40px 20px;
    }
}

/* Video Trimmer Styles */
#video-container {
    width: 100%;
}

#video-container video {
    width: 100%;
    max-height: 400px;
    object-fit: contain;
    background: #000;
}

.video-trimmer {
    background: rgba(0, 0, 0, 0.9);
    padding: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.trim-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    color: white;
    font-size: 14px;
}

.trim-info span:first-child {
    font-weight: 600;
}

#trim-duration {
    background: var(--twitter-blue);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.trim-controls {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 12px;
}

.trim-input-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.trim-input-group label {
    color: white;
    font-size: 14px;
    min-width: 50px;
}

.trim-input-group input[type="range"] {
    flex: 1;
    height: 6px;
    -webkit-appearance: none;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
    cursor: pointer;
}

.trim-input-group input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: var(--twitter-blue);
    border-radius: 50%;
    cursor: pointer;
}

.trim-input-group span {
    color: white;
    font-size: 13px;
    min-width: 45px;
    text-align: right;
}

.trim-preview {
    display: flex;
    justify-content: center;
}

.btn-trim-preview {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}

.btn-trim-preview:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>

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
