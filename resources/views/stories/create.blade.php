@extends('layouts.app')

@section('title', 'Create Story')

@section('content')
<div class="create-story-page">
    <div class="create-story-container">
        <h1>Create Story</h1>

        <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" id="story-form">
            @csrf

            <div class="form-group">
                <label for="media">Upload Image or Video</label>
                <input type="file" id="media" name="media" accept="image/*,video/*" required onchange="previewMedia(this)">
                <small>Supported formats: JPG, PNG, GIF, MP4, MOV, AVI, WebM (Max 50MB)</small>
            </div>

            <div class="media-preview" id="media-preview" style="display: none;">
                <div class="preview-container">
                    <img id="image-preview" src="" alt="Preview" style="display: none; max-width: 100%; max-height: 300px; border-radius: 8px;">
                    <video id="video-preview" controls style="display: none; max-width: 100%; max-height: 300px; border-radius: 8px;"></video>
                </div>
            </div>

            <div class="form-group">
                <label for="content">Add a caption (optional)</label>
                <textarea name="content" id="content" rows="3" maxlength="280" placeholder="Write something..."></textarea>
                <small id="char-count">0/280</small>
            </div>

            <div class="form-actions">
                <a href="{{ route('stories.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn" id="submit-btn" disabled>Create Story</button>
            </div>
        </form>
    </div>
</div>

<style>
.create-story-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.create-story-container {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--shadow);
}

.create-story-container h1 {
    margin-bottom: 30px;
    text-align: center;
    color: var(--twitter-dark);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.form-group input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    background: var(--twitter-light);
    cursor: pointer;
    transition: border-color 0.2s ease;
}

.form-group input[type="file"]:hover {
    border-color: var(--twitter-blue);
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: var(--twitter-gray);
    font-size: 12px;
}

.media-preview {
    margin: 20px 0;
    text-align: center;
}

.preview-container {
    display: inline-block;
    position: relative;
}

.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--twitter-blue);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

#char-count {
    display: block;
    text-align: right;
    margin-top: 5px;
    color: var(--twitter-gray);
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}

.btn-secondary {
    background: var(--twitter-gray);
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 20px;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.btn-secondary:hover {
    background: #657786;
}

#submit-btn:disabled {
    background: var(--twitter-gray);
    cursor: not-allowed;
}

#submit-btn:disabled:hover {
    background: var(--twitter-gray);
}
</style>

<script>
function previewMedia(input) {
    const preview = document.getElementById('media-preview');
    const imagePreview = document.getElementById('image-preview');
    const videoPreview = document.getElementById('video-preview');
    const submitBtn = document.getElementById('submit-btn');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileType = file.type;

        // Hide both previews initially
        imagePreview.style.display = 'none';
        videoPreview.style.display = 'none';

        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                preview.style.display = 'block';
                submitBtn.disabled = false;
            };
            reader.readAsDataURL(file);
        } else if (fileType.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                videoPreview.src = e.target.result;
                videoPreview.style.display = 'block';
                preview.style.display = 'block';
                submitBtn.disabled = false;
            };
            reader.readAsDataURL(file);
        }
    } else {
        preview.style.display = 'none';
        submitBtn.disabled = true;
    }
}

// Character count for textarea
document.getElementById('content').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('char-count').textContent = count + '/280';
});
</script>
@endsection
