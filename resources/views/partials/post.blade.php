<div class="post-card" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-author">
            <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->username }}" class="author-avatar">
            <div class="author-info">
                <a href="{{ route('users.show', $post->user) }}" class="author-name">{{ $post->user->username }}</a>
                @auth
                    @if(auth()->id() !== $post->user->id)
                        @php $isFollowing = auth()->user()->isFollowing($post->user); @endphp
                        <button type="button" class="quick-follow-btn {{ $isFollowing ? 'following' : '' }}" onclick="quickFollow('{{ $post->user->username }}', this)" data-following="{{ $isFollowing ? 'true' : 'false' }}">
                            <span>{{ $isFollowing ? __('messages.following') : __('messages.follow') }}</span>
                        </button>
                    @endif
                @endauth
                <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
            </div>
            @if($post->is_private)
                <span class="privacy-badge"><i class="fas fa-lock"></i> {{ __('messages.private') }}</span>
            @endif
        </div>
        <div class="post-header-actions">
            @auth
            <button type="button" class="post-menu-btn" onclick="togglePostMenu('{{ $post->id }}')" title="{{ __('messages.options') }}">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="post-menu-dropdown" id="post-menu-{{ $post->id }}" style="display: none;">
                @if($post->user_id === auth()->id())
                <button type="button" class="menu-item" onclick="deletePost('{{ $post->slug }}', this)">
                    <i class="fas fa-trash"></i> {{ __('messages.delete_post') }}
                </button>
                @else
                <button type="button" class="menu-item" onclick="openReportModal('{{ $post->slug }}', '{{ $post->id }}')">
                    <i class="fas fa-flag"></i> {{ __('messages.report_post') }}
                </button>
                @endif
            </div>
            @else
            @if($post->user_id === auth()->id())
            <button type="button" class="delete-post-btn" onclick="deletePost('{{ $post->slug }}', this)" title="{{ __('messages.delete_post') }}">
                <i class="fas fa-trash"></i>
            </button>
            @endif
            @endauth
        </div>
    </div>

    @if($post->content)
        <div class="post-content">
            @php
                $content = $post->content_html;
                $contentLength = strlen(strip_tags($post->content));
                $shouldTruncate = $contentLength > 300;
                $truncatedContent = $shouldTruncate ? substr(strip_tags($post->content), 0, 300) . '...' : $post->content;
                if ($shouldTruncate) {
                    $truncatedContent = app(\App\Services\HashtagService::class)->linkify(app(\App\Services\MentionService::class)->convertMentionsToLinks($truncatedContent));
                }
            @endphp
            <p class="post-text {{ $shouldTruncate ? 'truncated' : '' }}"
               data-full-content="{{ htmlspecialchars($content, ENT_QUOTES, 'UTF-8') }}"
               data-truncated-content="{{ htmlspecialchars($truncatedContent, ENT_QUOTES, 'UTF-8') }}">
                {!! $shouldTruncate ? $truncatedContent : $content !!}
            </p>
            @if($shouldTruncate)
                <button type="button" class="show-more-btn" onclick="togglePostContent(this)">
                    <span class="show-more-text">{{ __('messages.show_more') }}</span>
                    <span class="show-less-text" style="display: none;">{{ __('messages.show_less') }}</span>
                </button>
            @endif
        </div>
    @endif

    @if($post->media && $post->media->count() > 0)
        @php
            $mediaCount = $post->media->count();
            $remainingCount = $mediaCount - 4;
            $mediaData = $post->media->map(function($m, $index) {
                return [
                    'index' => $index,
                    'type' => $m->media_type,
                    'src' => asset('storage/' . $m->media_path)
                ];
            });
        @endphp
        <div class="post-media fb-grid fb-grid-{{ $mediaCount }}" 
             data-post-id="{{ $post->id }}" 
             data-media-count="{{ $mediaCount }}"
             data-media-list="{{ json_encode($mediaData) }}">
            @foreach($post->media as $index => $media)
                @if($index < 4)
                    @if($media->media_type === 'image')
                        <div class="media-item {{ $index === 3 && $remainingCount > 0 ? 'has-more' : '' }}" onclick="openMediaModal('{{ $post->id }}', '{{ $index }}')">
                            <img src="{{ asset('storage/' . $media->media_path) }}" alt="Post image" loading="lazy" data-media-index="{{ $index }}">
                            @if($index === 3 && $remainingCount > 0)
                                <div class="more-overlay">
                                    <span class="more-count">+{{ $remainingCount }}</span>
                                </div>
                            @endif
                        </div>
                    @elseif($media->media_type === 'video')
                        <div class="media-item">
                            <video controls preload="metadata">
                                <source src="{{ asset('storage/' . $media->media_path) }}" type="video/mp4">
                            </video>
                        </div>
                    @endif
                @endif
            @endforeach
        </div>
    @endif

    <div class="post-actions">
        @if(auth()->check())
            <button type="button" class="action-btn like-btn {{ $post->likedBy(auth()->user()) ? 'liked' : '' }}" onclick="toggleLike('{{ $post->slug }}', this)">
                <i class="fas fa-heart"></i>
                <span class="count">{{ $post->likes->count() }}</span>
            </button>
            <button type="button" class="action-btn save-btn {{ $post->savedBy(auth()->user()) ? 'saved' : '' }}" onclick="toggleSave('{{ $post->slug }}', this)">
                <i class="fas fa-bookmark"></i>
                <span>{{ $post->savedBy(auth()->user()) ? __('messages.saved_post') : __('messages.save_post') }}</span>
            </button>
        @else
            <button type="button" class="action-btn" onclick="showLoginModal('like', '{{ __('messages.like_posts_prompt') }}')">
                <i class="fas fa-heart"></i>
                <span>{{ $post->likes->count() }}</span>
            </button>
            <button type="button" class="action-btn" onclick="showLoginModal('save', '{{ __('messages.save_posts_prompt') }}')">
                <i class="fas fa-bookmark"></i>
                <span>{{ __('messages.save_post') }}</span>
            </button>
        @endif
        <button type="button" class="action-btn" onclick="copyPostLink('{{ $post->slug }}')">
            <i class="fas fa-share"></i>
            <span>{{ __('messages.share') }}</span>
        </button>
        <button type="button" class="action-btn likers-btn" onclick="showLikers('{{ $post->slug }}')">
            <i class="fas fa-users"></i>
            <span class="likers-count">{{ $post->likes->count() }}</span>
        </button>
    </div>

    <div class="post-comments-section">
        <h4>{{ __('messages.comments_count', ['count' => $post->comments->count()]) }}</h4>
        
        @if(auth()->check())
            <div class="comment-form">
                <textarea id="comment-content-{{ $post->slug }}" placeholder="{{ __('messages.write_a_comment') }}" maxlength="5000"></textarea>
                <button type="button" onclick="submitComment('{{ $post->slug }}', {{ $post->id }})">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        @else
            <div class="guest-message">
                <p><a href="{{ route('login') }}">{{ __('messages.login') }}</a> {{ __('messages.to_comment') }}</p>
            </div>
        @endif

        <div class="comments-list">
            @php
                $sortedComments = $post->comments->sortByDesc('created_at');
                $visibleComments = $sortedComments->take(2);
                $hasMore = $sortedComments->count() > 2;
            @endphp
            
            @foreach($visibleComments as $comment)
                @include('partials.comment', ['comment' => $comment])
            @endforeach
            
            @if($hasMore)
                <div class="show-more-comments">
                    <button type="button" onclick="toggleComments({{ $post->id }}, true)">
                        {{ __('messages.show_more_comments', ['count' => $sortedComments->count() - 2]) }}
                    </button>
                </div>
                <div class="hidden-comments" id="hidden-comments-{{ $post->id }}" style="display: none;">
                    @foreach($sortedComments->skip(2) as $comment)
                        @include('partials.comment', ['comment' => $comment])
                    @endforeach
                    <button type="button" class="hide-comments" onclick="toggleComments({{ $post->id }}, false)">
                        {{ __('messages.hide_comments') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- Media Modal -->
<div id="media-modal" class="media-modal" onclick="closeMediaModal(event)">
    <div class="media-modal-content" onclick="event.stopPropagation()">
        <button class="media-modal-close" onclick="closeMediaModal()" title="Close">
            <i class="fas fa-times"></i>
        </button>
        <button class="media-modal-nav media-modal-prev" onclick="navigateMedia(-1)" title="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div id="media-modal-image"></div>
        <button class="media-modal-nav media-modal-next" onclick="navigateMedia(1)" title="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="media-modal-counter" id="media-modal-counter"></div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/partial-posts.css') }}">
@vite(['resources/js/legacy/posts.js'])
<div id="post-translations" style="display:none;">{"delete_post_confirm":"{{ __('messages.delete_post_confirm') }}","post_deleted":"{{ __('messages.post_deleted') }}","failed_to_delete_post":"{{ __('messages.failed_to_delete_post') }}","delete_comment_confirm":"{{ __('messages.delete_comment_confirm') }}"}</div>
