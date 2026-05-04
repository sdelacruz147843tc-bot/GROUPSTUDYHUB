@extends('studyhub.student.layout')

@section('title', 'Profile')

@section('page')
    @php
        $profileInitials = strtoupper(substr($studentProfile['display_name'], 0, 1)).strtoupper(substr(trim(strrchr($studentProfile['display_name'], ' ')) ?: $studentProfile['display_name'], 0, 1));
    @endphp

    <div class="profile-page-header">
        <div>
            <h2 class="page-title">Profile</h2>
            <p class="page-subtitle">Shape how classmates see you across groups, resources, and discussions.</p>
        </div>
        <a class="secondary-button profile-theme-link" href="{{ route('studyhub.student.theme') }}">Customize Theme</a>
    </div>

    <section class="content-card profile-banner">
        <div class="profile-hero-visual">
            <div class="profile-banner-avatar">
                @if (! empty($studentProfile['avatar_url']))
                    <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $studentProfile['display_name'] }}">
                @else
                    {{ $profileInitials }}
                @endif
            </div>
            <span class="profile-hero-status">Student</span>
        </div>
        <div class="profile-banner-main">
            <span class="profile-eyebrow">StudyHub Identity</span>
            <h3>{{ $studentProfile['display_name'] }}</h3>
            <p>{{ $studentProfile['bio'] ?: 'No bio yet.' }}</p>
            <div class="profile-banner-meta">
                <span class="profile-banner-chip">{{ $studentProfile['email'] }}</span>
                <span class="profile-banner-chip">{{ $studentProfile['theme'] === 'dark' ? 'Dark' : 'Light' }}</span>
            </div>
        </div>
        <div class="profile-hero-summary">
            <span>Focus Group</span>
            <strong>{{ $profileHighlights['primary_group'] }}</strong>
        </div>
    </section>

    <section class="profile-stat-grid">
        @foreach ($profileStats as $stat)
            <article class="profile-stat-card">
                <strong>{{ $stat['value'] }}</strong>
                <span>{{ $stat['label'] }}</span>
                <small>{{ $stat['hint'] }}</small>
            </article>
        @endforeach
    </section>

    <div class="profile-layout">
        <form method="POST" action="{{ route('studyhub.student.profile.update') }}">
            @csrf
            @method('PUT')

            <section class="content-card profile-form-card">
                <div class="profile-form-header">
                    <div>
                        <span class="profile-eyebrow">Profile Details</span>
                        <h3>Edit Details</h3>
                    </div>
                    <button class="action-button" type="submit" data-loading-label="Saving...">Save</button>
                </div>

                <div class="profile-form-grid">
                    <label class="profile-field" for="display_name">
                        <span>Name</span>
                        <input id="display_name" name="display_name" type="text" value="{{ old('display_name', $studentProfileForm['display_name']) }}">
                    </label>

                    <label class="profile-field" for="email">
                        <span>Email</span>
                        <input id="email" name="email" type="email" value="{{ old('email', $studentProfileForm['email']) }}">
                    </label>

                    <label class="profile-field profile-field-full" for="avatar_url">
                        <span>Photo URL</span>
                        <input id="avatar_url" name="avatar_url" type="url" placeholder="https://example.com/avatar.jpg" value="{{ old('avatar_url', $studentProfileForm['avatar_url']) }}">
                    </label>

                    <label class="profile-field profile-field-full" for="bio">
                        <span>Bio</span>
                        <textarea id="bio" name="bio" maxlength="240" placeholder="Share what you are studying, what you can help with, or what kind of group you like joining.">{{ old('bio', $studentProfileForm['bio']) }}</textarea>
                    </label>
                </div>
            </section>
        </form>

        <aside class="profile-side-panel">
            <article class="content-card profile-insight-card">
                <span class="profile-eyebrow">Workspace</span>
                <h3>Current Setup</h3>
                <div class="profile-insight-list">
                    <div>
                        <span>Theme</span>
                        <strong>{{ $profileHighlights['theme'] }}</strong>
                    </div>
                    <div>
                        <span>Next Session</span>
                        <strong>{{ $profileHighlights['next_session'] }}</strong>
                    </div>
                </div>
                <a class="secondary-button" href="{{ route('studyhub.student.theme') }}">Adjust Look</a>
            </article>

            <article class="content-card profile-insight-card">
                <span class="profile-eyebrow">Profile Strength</span>
                <h3>{{ $studentProfile['bio'] && $studentProfile['avatar_url'] ? 'Looking Complete' : 'Add More Detail' }}</h3>
                <p>{{ $studentProfile['bio'] && $studentProfile['avatar_url'] ? 'Your profile has the key pieces classmates need.' : 'A short bio and photo make it easier for classmates to recognize you.' }}</p>
            </article>
        </aside>
    </div>
@endsection
