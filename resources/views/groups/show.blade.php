@extends('layouts.app')

@section('title', $group->name)

@section('content')
<style>
/* Use chat theme variables */
:root {
    --wa-bg: var(--bg, #111b21);
    --wa-panel: var(--surface, #202c33);
    --wa-panel-hover: var(--surface-hover, #2a3942);
    --wa-border: var(--border, #2f3b43);
    --wa-text: var(--text, #e9edef);
    --wa-text-muted: var(--text-muted, #8696a0);
    --wa-accent: var(--primary, #00a884);
    --wa-blue: var(--primary, #53bdeb);
    --wa-green: var(--success, #25d366);
    --wa-red: var(--danger, #f15c6d);
    --wa-yellow: var(--warning, #f7b928);
}

.group-page {
    min-height: calc(100vh - 64px);
    background: var(--wa-bg);
    padding-top: 20px;
}

.group-container {
    max-width: 700px;
    margin: 20px auto;
    background: var(--wa-panel);
    min-height: calc(100vh - 84px);
    border-radius: 16px;
}

/* Modern Chat Header */
.group-header {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
    position: sticky;
    top: 0;
    z-index: 100;
}

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--wa-text-muted);
    text-decoration: none;
    border-radius: 50%;
    margin-right: 12px;
    transition: background 0.2s;
}

.back-btn:hover {
    background: var(--wa-panel-hover);
}

.group-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.group-avatar, .group-avatar-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.group-avatar-placeholder {
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.group-details h1 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--wa-text);
}

.group-meta {
    font-size: 13px;
    color: var(--wa-text-muted);
}

.header-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border-radius: 50%;
    color: var(--wa-text-muted);
    text-decoration: none;
    transition: all 0.2s;
}

.action-btn:hover {
    background: var(--wa-panel-hover);
    color: var(--wa-text);
}

/* Group Description */
.group-description {
    padding: 16px;
    border-bottom: 1px solid var(--wa-border);
}

.group-description p {
    margin: 0;
    color: var(--wa-text);
    font-size: 14px;
    line-height: 1.5;
}

/* Invite Section */
.invite-section {
    padding: 16px;
    border-bottom: 1px solid var(--wa-border);
}

.invite-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--wa-text-muted);
    font-size: 14px;
    margin-bottom: 12px;
}

.invite-link-box {
    display: flex;
    background: var(--wa-bg);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--wa-border);
}

.invite-link-box input {
    flex: 1;
    border: none;
    padding: 12px;
    background: transparent;
    font-size: 13px;
    color: var(--wa-text);
    outline: none;
}

.copy-btn {
    padding: 12px 16px;
    background: var(--wa-accent);
    color: white;
    border: none;
    cursor: pointer;
    transition: opacity 0.2s;
}

.copy-btn:hover {
    opacity: 0.9;
}

.regenerate-form {
    margin-top: 8px;
}

.regenerate-btn {
    background: none;
    border: none;
    color: var(--wa-text-muted);
    font-size: 13px;
    cursor: pointer;
    padding: 4px 8px;
    transition: color 0.2s;
}

.regenerate-btn:hover {
    color: var(--wa-text);
}

/* Quick Invite Section */
.quick-invite-section {
    padding: 16px;
    border-bottom: 1px solid var(--wa-border);
    background: linear-gradient(135deg, rgba(37, 211, 102, 0.08), rgba(18, 140, 126, 0.08));
}

.quick-invite-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--wa-green);
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.quick-invite-desc {
    font-size: 13px;
    color: var(--wa-text-muted);
    margin: 0 0 12px 0;
}

.quick-invite-btn {
    background: linear-gradient(135deg, var(--wa-green), #128c7e);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.quick-invite-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
}

/* Members Section */
.members-section {
    padding: 16px;
}

.members-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.members-header h2 {
    margin: 0;
    font-size: 16px;
    color: var(--wa-text);
}

.add-member-btn {
    background: var(--wa-accent);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: opacity 0.2s;
}

.add-member-btn:hover {
    opacity: 0.9;
}

.member-card {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--wa-border);
}

.member-avatar img, .member-avatar .avatar-fallback {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.member-avatar .avatar-fallback {
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.member-info {
    flex: 1;
}

.member-name {
    display: block;
    font-weight: 500;
    color: var(--wa-text);
    font-size: 15px;
}

.member-role {
    font-size: 12px;
    color: var(--wa-text-muted);
}

.member-role.admin {
    color: var(--wa-blue);
}

.member-actions {
    display: flex;
    gap: 8px;
}

.promote-btn, .remove-btn, .demote-btn {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.promote-btn {
    background: var(--wa-bg);
    color: var(--wa-blue);
}

.promote-btn:hover {
    background: var(--wa-blue);
    color: white;
}

.demote-btn {
    background: var(--wa-bg);
    color: var(--wa-yellow);
}

.demote-btn:hover {
    background: var(--wa-yellow);
    color: var(--wa-bg);
}

.remove-btn {
    background: var(--wa-bg);
    color: var(--wa-red);
}

.remove-btn:hover {
    background: var(--wa-red);
    color: white;
}

/* Danger Section */
.danger-section {
    padding: 24px 16px;
    border-top: 1px solid var(--wa-border);
    text-align: center;
}

.warning-text {
    font-size: 13px;
    color: var(--wa-text-muted);
    margin-bottom: 16px;
}

.leave-group-btn, .delete-group-btn {
    border: none;
    padding: 12px 24px;
    border-radius: 24px;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.leave-group-btn {
    background: var(--wa-bg);
    color: var(--wa-text);
}

.leave-group-btn:hover {
    background: var(--wa-panel-hover);
}

.delete-group-btn {
    background: var(--wa-red);
    color: white;
}

.delete-group-btn:hover {
    opacity: 0.9;
}

/* Modal Styles - Modern Chat Theme */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: var(--wa-panel);
    border-radius: 12px;
    width: 90%;
    max-width: 450px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--wa-border);
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: var(--wa-text);
}

.close-modal {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--wa-text-muted);
    cursor: pointer;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.close-modal:hover {
    background: var(--wa-panel-hover);
}

.modal-body {
    padding: 16px;
    max-height: 400px;
    overflow-y: auto;
}

.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: var(--wa-border);
    border-radius: 3px;
}

.search-users input {
    width: 100%;
    padding: 12px 12px 12px 44px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    background: var(--wa-bg);
    color: var(--wa-text);
    margin-bottom: 12px;
    outline: none;
    position: relative;
}

.search-users input:focus {
    box-shadow: 0 0 0 2px var(--wa-accent);
}

.search-users {
    position: relative;
}

.search-users i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--wa-text-muted);
    font-size: 14px;
}

.friends-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.friend-option {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.friend-option:hover {
    background: var(--wa-panel-hover);
}

.friend-option input {
    margin-right: 12px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.friend-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.friend-info img, .avatar-fallback.small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-fallback.small {
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.friend-info span {
    color: var(--wa-text);
    font-size: 14px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 16px 20px;
    border-top: 1px solid var(--wa-border);
}

.btn-cancel {
    background: var(--wa-bg);
    color: var(--wa-text);
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: opacity 0.2s;
}

.btn-cancel:hover {
    opacity: 0.9;
}

.btn-add, .btn-send-invite {
    background: var(--wa-accent);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: opacity 0.2s;
}

.btn-add:hover, .btn-send-invite:hover {
    opacity: 0.9;
}

/* Already Member Styles */
.friend-option.already-member {
    opacity: 0.5;
    background: var(--wa-bg);
}

.member-badge {
    background: var(--wa-accent);
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 10px;
    margin-left: auto;
}

.no-friends-message {
    text-align: center;
    padding: 40px 20px;
    color: var(--wa-text-muted);
}

.no-friends-message i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.4;
}

.no-friends-message p {
    margin: 0 0 16px 0;
    font-size: 14px;
}

.explore-link {
    color: var(--wa-accent);
    text-decoration: none;
    font-weight: 600;
}

.invite-info {
    font-size: 13px;
    color: var(--wa-text-muted);
    margin: 0 0 12px 0;
    padding: 12px;
    background: var(--wa-bg);
    border-radius: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .group-page {
        padding-top: 0;
    }

    .group-header {
        top: 0;
    }

    .group-container {
        max-width: 100%;
    }
}
</style>

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
                    <span class="group-meta">{{ $group->members->count() }} members</span>
                </div>
            </div>
            @if($group->isAdmin(auth()->user()))
                <div class="header-actions">
                    <a href="{{ route('groups.edit', $group->slug) }}" class="action-btn" title="Edit Group">
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
                <span>Invite via link</span>
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
                <button type="submit" class="regenerate-btn" onclick="return confirm('Generate a new invite link? The old link will stop working.')">
                    <i class="fas fa-sync-alt"></i> Reset link
                </button>
            </form>
            @endif
        </div>

        <!-- Quick Invite Section -->
        @if($group->isAdmin(auth()->user()))
        <div class="quick-invite-section">
            <div class="quick-invite-header">
                <i class="fas fa-paper-plane"></i>
                <span>Quick Invite</span>
            </div>
            <p class="quick-invite-desc">Send group invites directly to your friends. They'll receive a notification to join.</p>
            <button class="quick-invite-btn" onclick="showQuickInviteModal()">
                <i class="fas fa-user-plus"></i> Send Invites
            </button>
        </div>
        @endif

        <!-- Members Section -->
        <div class="members-section">
            <div class="members-header">
                <h2>Members</h2>
                @if($group->isAdmin(auth()->user()))
                    <button class="add-member-btn" onclick="showAddMemberModal()">
                        <i class="fas fa-user-plus"></i> Add Member
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
                        <span class="member-role admin">Group Admin</span>
                    </div>
                    @if($member->user->id !== auth()->id() && $group->isAdmin(auth()->user()))
                        <div class="member-actions">
                            <form action="{{ route('groups.remove-admin', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="demote-btn" title="Remove Admin" onclick="return confirm('Remove admin privileges from this user?')">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                            <form action="{{ route('groups.remove-member', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="remove-btn" onclick="return confirm('Remove this admin from group?')">
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
                        <span class="member-role">Member</span>
                    </div>
                    @if($group->isAdmin(auth()->user()))
                        <div class="member-actions">
                            <form action="{{ route('groups.make-admin', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="promote-btn" title="Make Admin" onclick="return confirm('Make {{ $member->user->name }} an admin?')">
                                    <i class="fas fa-crown"></i>
                                </button>
                            </form>
                            <form action="{{ route('groups.remove-member', [$group->slug, $member->user->id]) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="remove-btn" onclick="return confirm('Remove this member?')">
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
                <p class="warning-text">You are the only admin. Transfer admin rights or delete the group.</p>
                <form action="{{ route('groups.destroy', $group->slug) }}" method="POST" onsubmit="return confirm('Delete this group? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="delete-group-btn">
                        <i class="fas fa-trash"></i> Delete Group
                    </button>
                </form>
            @else
                <form action="{{ route('groups.remove-member', [$group->slug, auth()->id()]) }}" method="POST" onsubmit="return confirm('Leave this group?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="leave-group-btn">
                        <i class="fas fa-sign-out-alt"></i> Leave Group
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
            <h3>Add Members</h3>
            <button type="button" class="close-modal" onclick="hideAddMemberModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('groups.add-members', $group->slug) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="search-users">
                    <input type="text" placeholder="Search friends..." id="memberSearch" oninput="searchFriends(this.value)">
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
                <button type="button" class="btn-cancel" onclick="hideAddMemberModal()">Cancel</button>
                <button type="submit" class="btn-add">Add Selected</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Invite Modal -->
<div id="quickInviteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-paper-plane"></i> Quick Invite</h3>
            <button type="button" class="close-modal" onclick="hideQuickInviteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('groups.quick-invite', $group->slug) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p class="invite-info">Select friends to invite. They'll receive a notification with a link to join the group.</p>
                <div class="search-users">
                    <input type="text" placeholder="Search friends..." id="inviteSearch" oninput="searchInviteFriends(this.value)">
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
                                    <span class="member-badge"><i class="fas fa-check"></i> Member</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    @else
                        <div class="no-friends-message">
                            <i class="fas fa-user-friends"></i>
                            <p>You're not following anyone yet.</p>
                            <a href="{{ route('explore') }}" class="explore-link">Explore Users</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="hideQuickInviteModal()">Cancel</button>
                <button type="submit" class="btn-send-invite">
                    <i class="fas fa-paper-plane"></i> Send Invites
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.group-page {
    min-height: 100vh;
    background: var(--twitter-light);
    padding-top: 60px;
}

.group-container {
    max-width: 600px;
    margin: 0 auto;
    background: var(--card-bg);
    min-height: calc(100vh - 60px);
}

.group-header {
    display: flex;
    align-items: center;
    padding: 16px;
    background: var(--card-bg);
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 60px;
    z-index: 100;
}

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-blue);
    text-decoration: none;
    border-radius: 50%;
    margin-right: 12px;
}

.back-btn:hover {
    background: var(--hover-bg);
}

.group-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.group-avatar, .group-avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.group-avatar-placeholder {
    background: var(--twitter-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.group-details h1 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--twitter-dark);
}

.group-meta {
    font-size: 13px;
    color: var(--twitter-gray);
}

.header-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--hover-bg);
    border-radius: 50%;
    color: var(--twitter-blue);
    text-decoration: none;
}

.group-description {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.group-description p {
    margin: 0;
    color: var(--twitter-dark);
    font-size: 14px;
}

.invite-section {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.invite-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--twitter-gray);
    font-size: 14px;
    margin-bottom: 12px;
}

.invite-link-box {
    display: flex;
    background: var(--twitter-light);
    border-radius: 8px;
    overflow: hidden;
}

.invite-link-box input {
    flex: 1;
    border: none;
    padding: 12px;
    background: transparent;
    font-size: 13px;
    color: var(--twitter-dark);
}

.copy-btn {
    padding: 12px 16px;
    background: var(--twitter-blue);
    color: white;
    border: none;
    cursor: pointer;
}

.copy-btn:hover {
    background: #1a8cd8;
}

.regenerate-form {
    margin-top: 8px;
}

.regenerate-btn {
    background: none;
    border: none;
    color: var(--twitter-gray);
    font-size: 13px;
    cursor: pointer;
    padding: 4px 8px;
}

.regenerate-btn:hover {
    color: var(--twitter-blue);
}

.members-section {
    padding: 16px;
}

.members-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.members-header h2 {
    margin: 0;
    font-size: 16px;
    color: var(--twitter-dark);
}

.add-member-btn {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.member-card {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.member-avatar img, .avatar-fallback {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.avatar-fallback {
    background: var(--twitter-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--twitter-gray);
}

.member-info {
    flex: 1;
}

.member-name {
    display: block;
    font-weight: 500;
    color: var(--twitter-dark);
}

.member-role {
    font-size: 12px;
    color: var(--twitter-gray);
}

.member-role.admin {
    color: var(--twitter-blue);
}

.member-actions {
    display: flex;
    gap: 8px;
}

.promote-btn, .remove-btn, .demote-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.promote-btn {
    background: var(--twitter-light);
    color: var(--twitter-blue);
}

.demote-btn {
    background: var(--twitter-light);
    color: var(--twitter-gray);
}

.demote-btn:hover {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
}

.remove-btn {
    background: var(--twitter-light);
    color: var(--error-color);
}

.danger-section {
    padding: 16px;
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.warning-text {
    font-size: 13px;
    color: var(--twitter-gray);
    margin-bottom: 12px;
}

.leave-group-btn, .delete-group-btn {
    border: none;
    padding: 10px 24px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.leave-group-btn {
    background: var(--twitter-light);
    color: var(--twitter-dark);
}

.delete-group-btn {
    background: var(--error-color);
    color: white;
}

/* Modal Styles */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: var(--card-bg);
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    max-height: 80vh;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: var(--twitter-dark);
}

.close-modal {
    background: none;
    border: none;
    font-size: 18px;
    color: var(--twitter-gray);
    cursor: pointer;
}

.modal-body {
    padding: 16px;
    max-height: 400px;
    overflow-y: auto;
}

.search-users input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
    background: var(--twitter-light);
    color: var(--twitter-dark);
    margin-bottom: 12px;
}

.friends-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.friend-option {
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
}

.friend-option:hover {
    background: var(--hover-bg);
}

.friend-option input {
    margin-right: 12px;
}

.friend-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.friend-info img, .avatar-fallback.small {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-fallback.small {
    font-size: 12px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 16px;
    border-top: 1px solid var(--border-color);
}

.btn-cancel {
    background: var(--twitter-light);
    color: var(--twitter-dark);
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-add {
    background: var(--twitter-blue);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
}

/* Quick Invite Styles */
.quick-invite-section {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    background: linear-gradient(135deg, rgba(37, 211, 102, 0.05), rgba(18, 140, 126, 0.05));
}

.quick-invite-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #25d366;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.quick-invite-desc {
    font-size: 13px;
    color: var(--twitter-gray);
    margin: 0 0 12px 0;
}

.quick-invite-btn {
    background: linear-gradient(135deg, #25d366, #128c7e);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.quick-invite-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
}

.friend-option.already-member {
    opacity: 0.6;
    background: var(--hover-bg);
}

.member-badge {
    background: var(--twitter-blue);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: auto;
}

.no-friends-message {
    text-align: center;
    padding: 30px;
    color: var(--twitter-gray);
}

.no-friends-message i {
    font-size: 40px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.no-friends-message p {
    margin: 0 0 12px 0;
}

.explore-link {
    color: var(--twitter-blue);
    text-decoration: none;
    font-weight: 600;
}

</style>

<script>
function copyInviteLink() {
    const input = document.getElementById('inviteLink');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = document.querySelector('.copy-btn');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        btn.innerHTML = originalHtml;
    }, 2000);
}

function showAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'flex';
}

function hideAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'none';
}

document.getElementById('addMemberModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddMemberModal();
    }
});

function searchFriends(query) {
    const options = document.querySelectorAll('#friendsList .friend-option');
    const q = query.toLowerCase();
    
    options.forEach(option => {
        const name = option.querySelector('.friend-info span').textContent.toLowerCase();
        option.style.display = name.includes(q) ? 'flex' : 'none';
    });
}

// Quick Invite Modal Functions
function showQuickInviteModal() {
    document.getElementById('quickInviteModal').style.display = 'flex';
}

function hideQuickInviteModal() {
    document.getElementById('quickInviteModal').style.display = 'none';
}

document.getElementById('quickInviteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideQuickInviteModal();
    }
});

function searchInviteFriends(query) {
    const options = document.querySelectorAll('#inviteFriendsList .friend-option');
    const q = query.toLowerCase();
    
    options.forEach(option => {
        const name = option.querySelector('.friend-info span').textContent.toLowerCase();
        option.style.display = name.includes(q) ? 'flex' : 'none';
    });
}
</script>
@endsection