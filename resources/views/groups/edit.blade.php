@extends('layouts.app')

@section('title', __('chat.edit_group') . ': ' . $group->name)

@section('content')
<link rel="stylesheet" href="{{ asset('css/groups-edit.css') }}">
@vite(['resources/js/legacy/groups-edit.js'])
<script>
window.currentGroupId = {{ $group->id }};
window.groupTranslations = {
    failed_to_add_member: "{{ __('messages.failed_to_add_member') }}",
    error_adding_member: "{{ __('messages.error_adding_member') }}",
    failed_to_remove_member: "{{ __('messages.failed_to_remove_member') }}",
    error_removing_member: "{{ __('messages.error_removing_member') }}"
};
</script>

<div class="group-edit-page">
    <div class="edit-header">
        <a href="{{ route('groups.show', $group->slug) }}" class="back-link">
            <i class="fas fa-arrow-left"></i> {{ __('chat.back_to_group') }}
        </a>
        <h1>{{ __('chat.edit_group') }}</h1>
    </div>

    <form action="{{ route('groups.update', $group) }}" method="POST" enctype="multipart/form-data" class="edit-form">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h3>{{ __('chat.group_info') }}</h3>

            <div class="form-group">
                <label for="name">{{ __('chat.group_name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name', $group->name) }}" required>
            </div>

            <div class="form-group">
                <label for="description">{{ __('chat.description') }}</label>
                <textarea id="description" name="description" rows="4">{{ old('description', $group->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="avatar">{{ __('chat.group_avatar') }}</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
                @if($group->avatar)
                    <p class="current-file">{{ __('chat.current') }}: <img src="{{ asset('storage/' . $group->avatar) }}" alt="Current avatar" class="current-avatar"></p>
                @endif
            </div>
        </div>

        <div class="form-section">
            <h3>{{ __('chat.group_members') }}</h3>
            <p class="section-desc">{{ __('chat.manage_members_desc') }}</p>

            <div class="members-list">
                @foreach($group->members as $member)
                    <div class="member-item">
                        <div class="member-info">
                            <img src="{{ $member->user->avatar_url }}" alt="{{ $member->user->name }}" class="member-avatar">
                            <span class="member-name">{{ $member->user->name }}</span>
                            @if($member->is_admin)
                                <span class="admin-badge">{{ __('chat.admin') }}</span>
                            @endif
                        </div>
                        @if($member->user_id !== auth()->id())
                            <button type="button" class="remove-member-btn" onclick="removeMember({{ $group->id }}, {{ $member->user_id }})">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="add-members">
                <h4>{{ __('chat.add_members') }}</h4>
                <div class="search-users">
                    <input type="text" id="userSearch" placeholder="{{ __('chat.search_users') }}" oninput="searchUsers(this.value)">
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('chat.save_changes') }}</button>
            <a href="{{ route('groups.show', $group) }}" class="btn">{{ __('chat.cancel') }}</a>
        </div>
    </form>

    <div class="danger-zone">
        <h3>{{ __('chat.danger_zone') }}</h3>
        <p>{{ __('chat.danger_zone_desc') }}</p>
        <form action="{{ route('groups.destroy', $group) }}" method="POST" onsubmit="return confirm('{{ __('chat.delete_group_confirm') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">{{ __('chat.delete_group') }}</button>
        </form>
    </div>
</div>
@endsection