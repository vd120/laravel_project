@extends('layouts.app')

@section('title', $group->name)

@section('content')
<link rel="stylesheet" href="{{ asset('css/groups-show.css') }}">
@vite(['resources/js/legacy/groups-show.js'])

<div class="group-page">
    <div class="group-container">
        <!-- Header with Back Button -->
        <div class="group-header">
            <a href="{{ route('chat.show', $group->conversation) }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="group-info">
                @if($group->avatar)
                    <img src="{{ asset('storage/' . $group->avatar) }}" alt="{{ $group->name }}" class="group-avatar">
                @else
                    <div class="group-avatar-placeholder">
                        <i class="fas fa-users"></i>
                    </div>
                @endif
                <div class="group-details">
                    <h1>{{ $group->name }}</h1>
                    <span class="group-meta">{{ __('chat.members_count', ['count' => $group->members->count()]) }}</span>
                </div>
            </div>
            @if($group->isAdmin(auth()->user()))
                <div class="header-actions">
                    <a href="{{ route('groups.edit', $group->slug) }}" class="action-btn" title="{{ __('chat.edit') }}">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- Group Description -->
        @if($group->description)
        <div class="group-description">
            <p>{{ $group->description }}</p>
        </div>
        @endif

        <!-- Invite Link Section -->
        <div class="invite-section">
            <div class="invite-header">
                <i class="fas fa-link"></i>
                <span>{{ __('chat.invite_via_link') }}</span>
            </div>
            <div class="invite-link-box">
                <input type="text" readonly value="{{ url('/join/' . $group->invite_link) }}" id="inviteLink">
                <button class="copy-btn" onclick="copyInviteLink()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            @if($group->isAdmin(auth()->user()))
            <form action="{{ route('groups.regenerate-invite', $group->slug) }}" method="POST" class="regenerate-form">
                @csrf
                <button type="submit" class="regenerate-btn" onclick="confirmAction('Generate a new invite link? The old link will stop working.')">
                    <i class="fas fa-sync-alt"></i> {{ __('chat.edit') }}
                </button>
            </form>
            @endif
        </div>

        <!-- Quick Invite Section -->
        @if($group->isAdmin(auth()->user()))
        <div class="quick-invite-section">
            <div class="quick-invite-header">
                <i class="fas fa-paper-plane"></i>
                <span>{{ __('chat.quick_invite') }}</span>
            </div>
            <p class="quick-invite-desc">{{ __('chat.quick_invite_desc') }}</p>
            <button class="quick-invite-btn" onclick="showQuickInviteModal()">
                <i class="fas fa-user-plus"></i> {{ __('chat.send_invites') }}
            </button>
        </div>
        @endif

        <!-- Members Section -->
        <div class="members-section">
            <div class="members-header">
                <h2>{{ __('chat.members') }}</h2>
                @if($group->isAdmin(auth()->user()))
                    <button class="add-member-btn" onclick="showAddMemberModal()">
                        <i class="fas fa-user-plus"></i> {{ __('chat.add_member') }}
                    </button>
                @endif
            </div>

            <div class="members-list">
                <!-- Admins -->
                @foreach($group->admins as $member)
                <div class="member-card">
                    <div class="member-avatar">
                        <img src="{{ $member->user->avatar_url }}" alt="{{ $member->user->name }}">
                    </div>
                    <div class="member-info">
                        <span class="member-name">{{ $member->user->name }}</span>
                        <span class="member-role admin">{{ __('chat.group_admin') }}</span>
                    </div>
                    @if($member->user->id !== auth()->id() && $group->isAdmin(auth()->user()))
                        <div class="member-actions">
                            <form action="{{ route('groups.remove-admin', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="demote-btn" title="{{ __('chat.demote') }}" onclick="return confirm('{{ __('chat.remove_admin_confirm') }}')">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                            <form action="{{ route('groups.remove-member', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="remove-btn" onclick="return confirm('{{ __('chat.remove_admin_from_group_confirm') }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                @endforeach

                <!-- Regular Members -->
                @foreach($group->regularMembers as $member)
                <div class="member-card">
                    <div class="member-avatar">
                        <img src="{{ $member->user->avatar_url }}" alt="{{ $member->user->name }}">
                    </div>
                    <div class="member-info">
                        <span class="member-name">{{ $member->user->name }}</span>
                        <span class="member-role">{{ __('chat.member') }}</span>
                    </div>
                    @if($group->isAdmin(auth()->user()))
                        <div class="member-actions">
                            <form action="{{ route('groups.make-admin', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="promote-btn" title="{{ __('chat.promote') }}" onclick="return confirm('{{ __('chat.make_admin_confirm', ['name' => $member->user->name]) }}')">
                                    <i class="fas fa-crown"></i>
                                </button>
                            </form>
                            <form action="{{ route('groups.remove-member', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="remove-btn" onclick="return confirm('{{ __('chat.remove_member_confirm') }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Leave/Delete Group Section -->
        <div class="danger-section">
            @if($group->isAdmin(auth()->user()) && $group->admins->count() === 1)
                <p class="warning-text">{{ __('chat.only_admin_warning') }}</p>
                <form action="{{ route('groups.destroy', $group->slug) }}" method="POST" onsubmit="return confirm('{{ __('chat.delete_group_confirm_admin') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="delete-group-btn">
                        <i class="fas fa-trash"></i> {{ __('chat.delete_group') }}
                    </button>
                </form>
            @else
                <form action="{{ route('groups.remove-member', [$group->slug, auth()->id()]) }}" method="POST" onsubmit="return confirm('{{ __('chat.leave_group_confirm') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="leave-group-btn">
                        <i class="fas fa-sign-out-alt"></i> {{ __('chat.leave_group') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>{{ __('chat.add_members_modal_title') }}</h3>
            <button type="button" class="close-modal" onclick="hideAddMemberModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('groups.add-members', $group->slug) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="search-users">
                    <input type="text" placeholder="{{ __('chat.search_friends') }}" id="memberSearch" oninput="searchFriends(this.value)">
                </div>
                <div id="friendsList" class="friends-list">
                    @php
                        $friends = auth()->user()->following()->whereNotIn('users.id', $group->members->pluck('user_id'))->get();
                    @endphp
                    @foreach($friends as $friend)
                    <label class="friend-option">
                        <input type="checkbox" name="members[]" value="{{ $friend->id }}">
                        <div class="friend-info">
                            <img src="{{ $friend->avatar_url }}" alt="{{ $friend->name }}">
                            <span>{{ $friend->name }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="hideAddMemberModal()">{{ __('chat.cancel') }}</button>
                <button type="submit" class="btn-add">{{ __('chat.add_selected') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Invite Modal -->
<div id="quickInviteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-paper-plane"></i> {{ __('chat.quick_invite_modal_title') }}</h3>
            <button type="button" class="close-modal" onclick="hideQuickInviteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('groups.quick-invite', $group->slug) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p class="invite-info">{{ __('chat.select_friends_invite') }}</p>
                <div class="search-users">
                    <input type="text" placeholder="{{ __('chat.search_friends') }}" id="inviteSearch" oninput="searchInviteFriends(this.value)">
                </div>
                <div id="inviteFriendsList" class="friends-list">
                    @php
                        $inviteFriends = auth()->user()->following()->get();
                    @endphp
                    @if($inviteFriends->count() > 0)
                        @foreach($inviteFriends as $friend)
                        <label class="friend-option {{ $group->hasMember($friend) ? 'already-member' : '' }}">
                            <input type="checkbox" name="users[]" value="{{ $friend->id }}" {{ $group->hasMember($friend) ? 'disabled' : '' }}>
                            <div class="friend-info">
                                <img src="{{ $friend->avatar_url }}" alt="{{ $friend->name }}">
                                <span>{{ $friend->name }}</span>
                                @if($group->hasMember($friend))
                                    <span class="member-badge"><i class="fas fa-check"></i> {{ __('chat.already_member') }}</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    @else
                        <div class="no-friends-message">
                            <i class="fas fa-user-friends"></i>
                            <p>{{ __('chat.not_following_anyone') }}</p>
                            <a href="{{ route('explore') }}" class="explore-link">{{ __('chat.explore_users') }}</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="hideQuickInviteModal()">{{ __('chat.cancel') }}</button>
                <button type="submit" class="btn-send-invite">
                    <i class="fas fa-paper-plane"></i> {{ __('chat.send_invites') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection