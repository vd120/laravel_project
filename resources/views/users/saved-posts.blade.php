@extends('layouts.app')

@section('title', 'Saved Posts')

@section('content')
<style>
.saved-posts-container { max-width: 680px; margin: 0 auto; }
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 800; color: var(--text); }
.page-header p { color: var(--text-muted); font-size: 14px; }

.posts-feed { display: flex; flex-direction: column; gap: 20px; }

.empty-state { text-align: center; padding: 60px 20px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); position: relative; }
.empty-state i { font-size: 64px; color: var(--text-muted); margin-bottom: 20px; opacity: 0.5; }
body.light-theme .empty-state { background: #ffffff; }
</style>

<div class="saved-posts-container">
    <div class="page-header">
        <h1><i class="fas fa-bookmark"></i> Saved Posts</h1>
        <p>{{ $savedPosts->count() }} saved post{{ $savedPosts->count() !== 1 ? 's' : '' }}</p>
    </div>

    <div class="posts-feed">
        @forelse($savedPosts as $saved)
            @include('partials.post', ['post' => $saved->post])
        @empty
        <div class="empty-state">
            <i class="fas fa-bookmark"></i>
            <h3>No saved posts</h3>
            <p style="color: var(--text-muted);">Posts you save will appear here.</p>
            <a href="{{ route('home') }}" class="btn btn-primary" style="margin-top: 16px; position: relative; z-index: 1;">Browse Posts</a>
        </div>
        @endforelse
    </div>

    @if($savedPosts->hasPages())
    <div style="margin-top: 24px;">
        {{ $savedPosts->links() }}
    </div>
    @endif
</div>
@endsection
