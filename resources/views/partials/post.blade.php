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
        @if($post->user_id === auth()->id())
            <button type="button" class="delete-post-btn" onclick="deletePost('{{ $post->slug }}', this)" title="{{ __('messages.delete_post') }}">
                <i class="fas fa-trash"></i>
            </button>
        @endif
    </div>

    @if($post->content)
        <div class="post-content">
            @php
                $content = app(\App\Services\MentionService::class)->convertMentionsToLinks($post->content);
                $contentLength = strlen(strip_tags($post->content));
                $shouldTruncate = $contentLength > 300;
                $truncatedContent = $shouldTruncate ? substr(strip_tags($post->content), 0, 300) . '...' : $post->content;
                if ($shouldTruncate) {
                    $truncatedContent = app(\App\Services\MentionService::class)->convertMentionsToLinks($truncatedContent);
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
<script>
if (typeof window.postFunctionsInitialized === 'undefined') {
    window.postFunctionsInitialized = true;

    function deletePost(slug, btn) {
        if (!confirm('{{ __('messages.delete_post_confirm') }}')) return;

        const postCard = btn.closest('.post-card');

        fetch(`/posts/${slug}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                if (postCard) postCard.remove();
                showToast('{{ __('messages.post_deleted') }}', 'success');
                return { success: true };
            }
            // If not ok, try to parse JSON or reload
            return response.json().catch(() => {
                window.location.reload();
            });
        })
        .then(data => {
            if (data && data.success && postCard) {
                postCard.remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('{{ __('messages.failed_to_delete_post') }}', 'error');
            window.location.reload();
        });
    }

    function toggleLike(slug, btn) {
        // Optimistic update - just toggle 'liked' class (blade uses fas fa-heart always)
        const count = btn.querySelector('.count');
        const isCurrentlyLiked = btn.classList.contains('liked');
        const currentCount = count ? parseInt(count.textContent) || 0 : 0;
        
        if (isCurrentlyLiked) {
            btn.classList.remove('liked');
            if (count) count.textContent = currentCount - 1;
        } else {
            btn.classList.add('liked');
            if (count) count.textContent = currentCount + 1;
        }
        
        fetch(`/posts/${slug}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update likers button count
                var likersBtn = btn.closest('.post-actions').querySelector('.likers-btn');
                if (likersBtn) {
                    var likersCountSpan = likersBtn.querySelector('.likers-count');
                    if (likersCountSpan) {
                        likersCountSpan.textContent = data.likes_count > 0 ? data.likes_count : '';
                    }
                }
            } else {
                // Revert on failure
                if (isCurrentlyLiked) {
                    btn.classList.add('liked');
                    if (count) count.textContent = currentCount;
                } else {
                    btn.classList.remove('liked');
                    if (count) count.textContent = currentCount;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert on error
            if (isCurrentlyLiked) {
                btn.classList.add('liked');
                if (count) count.textContent = currentCount;
            } else {
                btn.classList.remove('liked');
                if (count) count.textContent = currentCount;
            }
        });
    }

    function toggleSave(slug, btn) {
        fetch(`/posts/${slug}/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.saved) {
                btn.classList.add('saved');
                btn.querySelector('span').textContent = window.chatTranslations.saved_post;
                showToast(window.chatTranslations.post_saved_success, 'success');
            } else {
                btn.classList.remove('saved');
                btn.querySelector('span').textContent = window.chatTranslations.save_post;
                showToast(window.chatTranslations.post_removed_from_saved, 'info');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function copyPostLink(slug) {
        const url = window.location.origin + '/posts/' + slug;

        // Use Clipboard API if available (HTTPS / localhost)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showToast(window.chatTranslations.post_link_copied, 'success');
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(ta);
            if (ok) {
                showToast(window.chatTranslations.post_link_copied, 'success');
            } else {
                showToast(window.chatTranslations.failed_to_copy_link, 'error');
            }
        } catch (e) {
            showToast(window.chatTranslations.failed_to_copy_link, 'error');
        }
    }

    function showLikers(slug) {
        fetch(`/posts/${slug}/likers`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.likers && data.likers.length > 0) {
                showLikersModal(data.likers);
            } else {
                showToast(window.chatTranslations.no_likes_yet, 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(window.chatTranslations.could_not_load_likers, 'error');
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showLikersModal(likers) {
        // Remove existing modal if any
        const existingModal = document.getElementById('likers-modal');
        if (existingModal) existingModal.remove();

        const modal = document.createElement('div');
        modal.id = 'likers-modal';
        modal.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        `;

        const content = document.createElement('div');
        content.style.cssText = `
            background: var(--surface, #161616);
            border: 1px solid var(--border, #2a2a2a);
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border, #2a2a2a);
        `;
        header.innerHTML = `
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text);">${window.chatTranslations.likes} (${likers.length})</h3>
            <button onclick="document.getElementById('likers-modal').remove()" style="background: none; border: none; color: var(--text-muted, #86868b); font-size: 24px; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        `;

        const list = document.createElement('div');
        list.style.cssText = 'display: flex; flex-direction: column; gap: 8px;';

        likers.forEach(liker => {
            const avatar = liker.avatar || null;
            const displayName = liker.username || liker.name || 'User';
            const initial = displayName ? displayName.charAt(0).toUpperCase() : '?';

            const item = document.createElement('a');
            item.href = `/users/${liker.username}`;
            item.style.cssText = `
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px;
                border-radius: 12px;
                text-decoration: none;
                color: inherit;
                transition: background 0.2s;
            `;
            item.onmouseover = () => item.style.background = 'var(--surface-hover, #1c1c1e)';
            item.onmouseout = () => item.style.background = 'transparent';

            item.innerHTML = `
                ${avatar
                    ? `<img src="${avatar}" alt="${displayName}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">`
                    : `<div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--primary, #5e60ce), var(--secondary, #4ea8de)); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: white;">${initial}</div>`
                }
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; font-size: 14px; direction: ltr; text-align: left;">@${escapeHtml(displayName)}</div>
                    ${liker.name ? `<div style="font-size: 12px; color: var(--text-muted, #86868b);">${escapeHtml(liker.name)}</div>` : ''}
                </div>
                ${liker.is_verified ? '<i class="fas fa-check-circle" style="color: #22c55e; font-size: 16px; flex-shrink: 0;"></i>' : ''}
            `;

            list.appendChild(item);
        });

        content.appendChild(header);
        content.appendChild(list);
        modal.appendChild(content);
        document.body.appendChild(modal);

        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
    }

    function submitComment(postSlug, postId) {
        const textarea = document.getElementById(`comment-content-${postSlug}`);
        const content = textarea.value.trim();
        
        if (!content) return;
        
        fetch(`/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content: content, post_id: postId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.comment) {
                textarea.value = '';

                // Add comment to the list dynamically without page reload
                const commentsList = document.querySelector(`#post-${postId} .comments-list`);
                if (commentsList && data.comment) {
                    const user = data.comment.user || { username: '{{ auth()->user() ? auth()->user()->username : "user" }}', name: '{{ auth()->user() ? auth()->user()->name : "User" }}', profile: { avatar: null } };
                    const avatar = user.profile && user.profile.avatar
                        ? `/storage/${user.profile.avatar}`
                        : null;
                    const firstLetter = user.username ? user.username.charAt(0).toUpperCase() : '?';

                    const userName = data.comment.user && data.comment.user.username ? data.comment.user.username : '{{ auth()->user() ? auth()->user()->username : "user" }}';
                    const avatarUrl = data.comment.user && data.comment.user.avatar_url ? data.comment.user.avatar_url : null;

                    const commentHtml = `
                        <div class="comment-item level-0" data-comment-id="${data.comment.id}">
                            <div class="comment-header">
                                <div class="comment-author">
                                    ${avatarUrl
                                        ? `<img src="${avatarUrl}" alt="Avatar" class="comment-avatar">`
                                        : `<div class="comment-avatar-placeholder">?</div>`
                                    }
                                    <div class="comment-author-info">
                                        <a href="/users/${userName}" class="comment-name">${userName}</a>
                                        <span class="comment-time">Just now</span>
                                    </div>
                                </div>
                                <button type="button" class="delete-comment-btn" onclick="deleteComment(${data.comment.id}, this)" title="${window.chatTranslations.delete_comment}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="comment-content">
                                <p>${data.comment.content}</p>
                            </div>
                            <div class="comment-actions-bar">
                                <button type="button" class="comment-action-btn" onclick="likeComment(${data.comment.id}, this)">
                                    <i class="fas fa-heart"></i>
                                    <span>0</span>
                                </button>
                                <button type="button" class="comment-action-btn" onclick="toggleReplyForm(${data.comment.id})">
                                    <i class="fas fa-reply"></i>
                                    <span>${window.chatTranslations.reply}</span>
                                </button>
                            </div>
                            <div class="reply-form" id="reply-form-${data.comment.id}" style="display: none;">
                                <div class="reply-input-wrapper">
                                    <textarea id="reply-content-${data.comment.id}" placeholder="${window.chatTranslations.write_a_reply}" maxlength="5000"></textarea>
                                    <button type="button" onclick="submitReply(${data.comment.id}, ${postId})">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <button type="button" class="cancel-reply" onclick="toggleReplyForm(${data.comment.id})">${window.chatTranslations.cancel}</button>
                            </div>
                        </div>
                    `;
                    
                    // Insert at the beginning of comments list
                    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                    
                    // Update comment count
                    const commentCount = document.querySelector(`#post-${postId} .post-comments-section h4`);
                    if (commentCount) {
                        const currentCount = parseInt(commentCount.textContent.match(/\d+/)) || 0;
                        commentCount.textContent = `Comments (${currentCount + 1})`;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('{{ __('messages.failed_to_post_comment') }}', 'error');
        });
    }

    function toggleComments(postId, show) {
        const hiddenComments = document.getElementById(`hidden-comments-${postId}`);
        const showMoreBtn = document.querySelector(`#post-${postId} .show-more-comments`);
        
        if (hiddenComments) {
            hiddenComments.style.display = show ? 'block' : 'none';
        }
        
        if (showMoreBtn) {
            showMoreBtn.style.display = show ? 'none' : 'block';
        }
    }

    function likeComment(commentId, btn) {
        // Optimistic update - just toggle 'liked' class (blade uses fas fa-heart always)
        const countSpan = btn.querySelector('span');
        const isCurrentlyLiked = btn.classList.contains('liked');
        const currentCount = countSpan ? parseInt(countSpan.textContent) || 0 : 0;
        
        if (isCurrentlyLiked) {
            btn.classList.remove('liked');
            if (countSpan) countSpan.textContent = currentCount - 1;
        } else {
            btn.classList.add('liked');
            if (countSpan) countSpan.textContent = currentCount + 1;
        }
        
        fetch(`/comments/${commentId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert on failure
                if (isCurrentlyLiked) {
                    btn.classList.add('liked');
                    if (countSpan) countSpan.textContent = currentCount;
                } else {
                    btn.classList.remove('liked');
                    if (countSpan) countSpan.textContent = currentCount;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert on error
            if (isCurrentlyLiked) {
                btn.classList.add('liked');
                if (countSpan) countSpan.textContent = currentCount;
            } else {
                btn.classList.remove('liked');
                if (countSpan) countSpan.textContent = currentCount;
            }
        });
    }

    function deleteComment(commentId, btn) {
        if (!confirm('{{ __('messages.delete_comment_confirm') }}')) return;
        
        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentItem = btn.closest('.comment-item');
                if (commentItem) commentItem.remove();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function submitReply(commentId, postId) {
        const textarea = document.getElementById(`reply-content-${commentId}`);
        const content = textarea.value.trim();
        
        if (!content) return;
        
        fetch(`/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content: content, post_id: postId, parent_id: commentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comment) {
                textarea.value = '';
                const replyForm = document.getElementById(`reply-form-${commentId}`);
                if (replyForm) replyForm.style.display = 'none';

                // Add reply to the parent comment's replies container
                const parentComment = document.querySelector(`[data-comment-id="${commentId}"]`);
                if (parentComment) {
                    let repliesContainer = parentComment.querySelector('.replies-container');
                    
                    // If no replies container, create one
                    if (!repliesContainer) {
                        repliesContainer = document.createElement('div');
                        repliesContainer.className = 'replies-container';
                        parentComment.appendChild(repliesContainer);
                    }
                    
                    const userName = data.comment.user && data.comment.user.username ? data.comment.user.username : '{{ auth()->user() ? auth()->user()->username : "user" }}';
                    const avatarUrl = data.comment.user && data.comment.user.avatar_url ? data.comment.user.avatar_url : null;
                    const initial = userName.charAt(0).toUpperCase();

                    // Determine the level of the new reply based on parent comment
                    let parentLevel = null;
                    const parentCommentEl = document.querySelector(`[data-comment-id="${commentId}"]`);
                    if (parentCommentEl) {
                        // Check for level classes (level-0, level-1, level-2, etc.)
                        for (let i = 0; i <= 5; i++) {
                            if (parentCommentEl.classList.contains('level-' + i)) {
                                parentLevel = i;
                                break;
                            }
                        }
                    }
                    const newLevel = parentLevel !== null ? parentLevel + 1 : 1;
                    
                    // Check if reply button should be shown (max level is 4, so show for level < 4)
                    const showReplyBtn = newLevel < 4;
                    
                    // Check if we need to show "Hide replies" button (when there's only 1 reply visible)
                    const existingReplies = repliesContainer.querySelectorAll('.comment-item.nested').length;
                    let showHideRepliesBtn = false;
                    let hideRepliesHtml = '';
                    
                    if (existingReplies === 0) {
                        // First reply - add hide button
                        showHideRepliesBtn = true;
                    }
                    
                    const replyHtml = `
                        <div class="comment-item nested level-${newLevel}" data-comment-id="${data.comment.id}">
                            <div class="comment-header">
                                <div class="comment-author">
                                    ${avatarUrl 
                                        ? `<img src="${avatarUrl}" alt="Avatar" class="comment-avatar">`
                                        : `<div class="comment-avatar-placeholder">${initial}</div>`
                                    }
                                    <div class="comment-author-info">
                                        <a href="/users/${userName}" class="comment-name">${userName}</a>
                                        <span class="comment-time">Just now</span>
                                    </div>
                                </div>
                                <button type="button" class="delete-comment-btn" onclick="deleteComment(${data.comment.id}, this)" title="${window.chatTranslations.delete_comment}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="comment-content">
                                <p>${data.comment.content}</p>
                            </div>
                            <div class="comment-actions-bar">
                                <button type="button" class="comment-action-btn" onclick="likeComment(${data.comment.id}, this)">
                                    <i class="fas fa-heart"></i>
                                    <span>0</span>
                                </button>
                                ${showReplyBtn ? `
                                <button type="button" class="comment-action-btn" onclick="toggleReplyForm(${data.comment.id})">
                                    <i class="fas fa-reply"></i>
                                    <span>${window.chatTranslations.reply}</span>
                                </button>
                                ` : ''}
                            </div>
                            ${showReplyBtn ? `
                            <div class="reply-form" id="reply-form-${data.comment.id}" style="display: none;">
                                <div class="reply-input-wrapper">
                                    <textarea id="reply-content-${data.comment.id}" placeholder="${window.chatTranslations.write_a_reply}" maxlength="5000"></textarea>
                                    <button type="button" onclick="submitReply(${data.comment.id}, ${postId})">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <button type="button" class="cancel-reply" onclick="toggleReplyForm(${data.comment.id})">${window.chatTranslations.cancel}</button>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    
                    repliesContainer.insertAdjacentHTML('afterbegin', replyHtml);
                    
                    // Add or update "Show replies" button (show always for 1-3 replies)
                    if (showHideRepliesBtn) {
                        let showMoreDiv = repliesContainer.querySelector('.show-more-replies');
                        let showRepliesAlways = repliesContainer.querySelector('.show-replies-always');
                        
                        if (!showRepliesAlways) {
                            showRepliesAlways = document.createElement('div');
                            showRepliesAlways.className = 'show-replies-always';
                            repliesContainer.appendChild(showRepliesAlways);
                        }
                        showRepliesAlways.innerHTML = '<button type="button" class="show-replies-btn" onclick="toggleNestedReplies(' + commentId + ', true)">Show 1 reply</button>';
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleReplyForm(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        if (form) {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.querySelector('textarea').focus();
            }
        }
    }

    function toggleNestedReplies(commentId, show) {
        const hiddenReplies = document.getElementById('hidden-replies-' + commentId);
        const parentComment = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!parentComment) return;
        
        const showMoreBtn = parentComment.querySelector('.show-more-replies');
        const showRepliesAlways = parentComment.querySelector('.show-replies-always');
        
        if (hiddenReplies) {
            hiddenReplies.style.display = show ? 'block' : 'none';
        }
        
        // Always hide both buttons when clicked
        if (showMoreBtn) {
            showMoreBtn.style.display = 'none';
        }
        
        if (showRepliesAlways) {
            showRepliesAlways.style.display = 'none';
        }
    }

    function showLoginModal(action, message) {
        alert('Please login to ' + action);
    }

    // Detect portrait images on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.fb-grid-1 .media-item img').forEach(function(img) {
            img.addEventListener('load', function() {
                // Check if image is taller than wide (portrait)
                const aspectRatio = this.naturalHeight / this.naturalWidth;
                if (aspectRatio > 1.3) {
                    this.classList.add('portrait');
                }
            });
            // If image is already loaded
            if (this.complete) {
                const aspectRatio = this.naturalHeight / this.naturalWidth;
                if (aspectRatio > 1.3) {
                    this.classList.add('portrait');
                }
            }
        });
    });

    // Media modal functions
    window.currentMediaIndex = 0;
    window.currentMediaArray = [];

    function openMediaModal(postId, index) {
        const mediaContainer = document.querySelector(`.post-media[data-post-id="${postId}"]`);
        if (!mediaContainer) return;
        
        // Get all media from data attribute (includes all images, not just visible ones)
        const mediaListData = mediaContainer.getAttribute('data-media-list');
        if (mediaListData) {
            window.currentMediaArray = JSON.parse(mediaListData);
        } else {
            // Fallback: get from DOM (only visible images)
            const mediaItems = mediaContainer.querySelectorAll('.media-item img, .media-item video');
            window.currentMediaArray = Array.from(mediaItems).map(item => ({
                src: item.src || item.querySelector('source')?.src,
                type: item.tagName === 'VIDEO' ? 'video' : 'image'
            }));
        }
        
        window.currentMediaIndex = parseInt(index);
        showMediaInModal();
        
        document.getElementById('media-modal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function showMediaInModal() {
        const container = document.getElementById('media-modal-image');
        const media = window.currentMediaArray[window.currentMediaIndex];
        
        if (!media) return;
        
        if (media.type === 'video') {
            container.innerHTML = `<video controls autoplay><source src="${media.src}" type="video/mp4"></video>`;
        } else {
            container.innerHTML = `<img src="${media.src}" alt="Media">`;
        }
        
        document.getElementById('media-modal-counter').textContent = 
            `${window.currentMediaIndex + 1} / ${window.currentMediaArray.length}`;
    }

    function closeMediaModal(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('media-modal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function navigateMedia(direction) {
        window.currentMediaIndex += direction;
        if (window.currentMediaIndex < 0) window.currentMediaIndex = window.currentMediaArray.length - 1;
        if (window.currentMediaIndex >= window.currentMediaArray.length) window.currentMediaIndex = 0;
        showMediaInModal();
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('media-modal');
        if (!modal || !modal.classList.contains('active')) return;
        
        if (e.key === 'Escape') closeMediaModal();
        if (e.key === 'ArrowLeft') navigateMedia(-1);
        if (e.key === 'ArrowRight') navigateMedia(1);
    });

    function quickFollow(username, btn) {
        fetch(`/users/${username}/follow`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.following) {
                btn.classList.add('following');
                const icon = btn.querySelector('i');
                const text = btn.querySelector('span');
                if (icon) {
                    icon.classList.remove('fa-user-plus');
                    icon.classList.add('fa-user-minus');
                }
                if (text) text.textContent = window.chatTranslations.following;
                btn.setAttribute('data-following', 'true');
            } else {
                btn.classList.remove('following');
                const icon = btn.querySelector('i');
                const text = btn.querySelector('span');
                if (icon) {
                    icon.classList.remove('fa-user-minus');
                    icon.classList.add('fa-user-plus');
                }
                if (text) text.textContent = window.chatTranslations.follow;
                btn.setAttribute('data-following', 'false');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Toggle post content show more/less
    function togglePostContent(button) {
        const postText = button.previousElementSibling;
        const showMoreText = button.querySelector('.show-more-text');
        const showLessText = button.querySelector('.show-less-text');
        
        const fullContent = postText.getAttribute('data-full-content');
        const truncatedContent = postText.getAttribute('data-truncated-content');

        if (postText.classList.contains('truncated')) {
            // Expand - show full content
            postText.classList.remove('truncated');
            postText.classList.add('expanded');
            postText.innerHTML = fullContent;
            postText.style.display = 'block';
            postText.style.webkitLineClamp = 'unset';
            postText.style.webkitBoxOrient = 'unset';
            postText.style.overflow = 'visible';
            showMoreText.style.display = 'none';
            showLessText.style.display = 'inline';
        } else {
            // Collapse - show truncated content
            postText.classList.remove('expanded');
            postText.classList.add('truncated');
            postText.innerHTML = truncatedContent;
            postText.style.display = '-webkit-box';
            postText.style.webkitLineClamp = '3';
            postText.style.webkitBoxOrient = 'vertical';
            postText.style.overflow = 'hidden';
            showMoreText.style.display = 'inline';
            showLessText.style.display = 'none';
        }
    }
}
</script>
