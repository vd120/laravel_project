@extends('layouts.app')

@section('title', 'Post by ' . $post->user->name)

@section('content')
<div class="post-detail-page">
    <div class="page-header">
        <a href="{{ url()->previous() }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
        <h1>Post</h1>
    </div>

    <div class="post-container">
        @include('partials.post', ['post' => $post])
    </div>
</div>

<style>
.post-detail-page {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.page-header h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--twitter-dark);
}

.back-link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--twitter-blue);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 20px;
    transition: background-color 0.2s ease;
}

.back-link:hover {
    background: var(--twitter-light);
}

.post-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .post-detail-page {
        padding: 16px;
    }

    .page-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
    }

    .post-container {
        padding: 16px;
    }
}
</style>
@endsection
