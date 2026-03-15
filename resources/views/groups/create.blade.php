@extends('layouts.app')

@section('title', __('chat.create_group'))

@section('content')
<link rel="stylesheet" href="{{ asset('css/groups-create.css') }}">

<div class="create-group-page">
    <div class="create-group-container">
        <div class="create-group-header">
            <a href="{{ route('chat.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>{{ __('chat.create_new_group') }}</h1>
        </div>

        <form action="{{ route('groups.store') }}" method="POST" enctype="multipart/form-data" class="create-group-form">
            @csrf

            <div class="form-group avatar-upload">
                <label for="avatar" class="avatar-label">
                    <div class="avatar-preview" id="avatarPreview">
                        <i class="fas fa-users"></i>
                        <span>{{ __('chat.add_photo') }}</span>
                    </div>
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(event)" hidden>
            </div>

            <div class="form-group">
                <label for="name">{{ __('chat.group_name_required') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('chat.enter_group_name') }}" required maxlength="100">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">{{ __('chat.description') }}</label>
                <textarea id="description" name="description" placeholder="{{ __('chat.whats_this_group_about') }}" maxlength="500" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_private" value="1" {{ old('is_private') ? 'checked' : '' }}>
                    <span>{{ __('chat.private_group') }}</span>
                </label>
                <p class="help-text">{{ __('chat.private_groups_help') }}</p>
            </div>

            <div class="form-group members-selection">
                <label>{{ __('chat.select_members_optional') }}</label>
                <p class="help-text">{{ __('chat.add_members_now_or_later') }}</p>

                @if($friends->count() > 0)
                <div class="friends-list">
                    @foreach($friends as $friend)
                    <label class="friend-item">
                        <input type="checkbox" name="members[]" value="{{ $friend->id }}" {{ in_array($friend->id, old('members', [])) ? 'checked' : '' }}>
                        <div class="friend-info">
                            <img src="{{ $friend->avatar_url }}" alt="{{ $friend->username }}">
                            <span>{{ $friend->username }}</span>
                        </div>
                        <span class="checkmark"><i class="fas fa-check"></i></span>
                    </label>
                    @endforeach
                </div>
                @else
                <div class="no-friends">
                    <p>{{ __('chat.not_following_anyone') }} {{ __('chat.add_members_later') }}</p>
                    <a href="{{ route('explore') }}" class="btn-explore">{{ __('chat.explore_users') }}</a>
                </div>
                @endif
                @error('members')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('chat.index') }}" class="btn-cancel">{{ __('chat.cancel') }}</a>
                <button type="submit" class="btn-create">
                    <i class="fas fa-users"></i>
                    {{ __('chat.create_group') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection