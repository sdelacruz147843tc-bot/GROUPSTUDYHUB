@extends('studyhub.student.layout')

@section('title', 'Profile')

@section('page')
    <div class="profile-page-header">
        <h2 class="page-title">Profile</h2>
        <p class="page-subtitle">Update your student identity, contact details, short bio, and profile picture without mixing it into appearance settings.</p>
    </div>

    <section class="content-card profile-banner">
        <div class="profile-banner-avatar">
            @if (! empty($studentProfile['avatar_url']))
                <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $studentProfile['display_name'] }}">
            @else
                {{ strtoupper(substr($studentProfile['display_name'], 0, 1)) }}{{ strtoupper(substr(trim(strrchr($studentProfile['display_name'], ' ')) ?: $studentProfile['display_name'], 0, 1)) }}
            @endif
        </div>
        <div>
            <h3>{{ $studentProfile['display_name'] }}</h3>
            <p>{{ $studentProfile['bio'] ?: 'Set a short bio so your StudyHub space feels a bit more like your own workspace.' }}</p>
            <div class="profile-banner-meta">
                <span class="profile-banner-chip">{{ $studentProfile['email'] }}</span>
                <span class="profile-banner-chip">{{ ucfirst($studentProfile['theme']) }} theme</span>
                <span class="profile-banner-chip">{{ ucfirst($studentProfile['surface_style']) }} surfaces</span>
                <span class="profile-banner-chip">{{ ucfirst($studentProfile['interface_density']) }} spacing</span>
            </div>
        </div>
    </section>

    <div class="profile-layout">
        <div>
            <form method="POST" action="{{ route('studyhub.student.profile.update') }}">
                @csrf
                @method('PUT')

                <section class="content-card settings-card">
                    <h3>Student Identity</h3>
                    <p class="settings-card-copy">Keep your display information current so the portal, sidebar profile card, and future collaboration features feel personalized.</p>

                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="display_name">Display name</label>
                            <input id="display_name" name="display_name" type="text" value="{{ old('display_name', $studentProfileForm['display_name']) }}">
                        </div>

                        <div class="settings-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $studentProfileForm['email']) }}">
                        </div>

                        <div class="settings-field">
                            <label for="avatar_url">Profile photo URL</label>
                            <input id="avatar_url" name="avatar_url" type="url" placeholder="https://example.com/avatar.jpg" value="{{ old('avatar_url', $studentProfileForm['avatar_url']) }}">
                            <span class="field-help">Paste an image link to use as your profile picture in the sidebar and profile page.</span>
                        </div>

                        <div class="settings-field">
                            <label for="bio">Short bio</label>
                            <textarea id="bio" name="bio">{{ old('bio', $studentProfileForm['bio']) }}</textarea>
                            <span class="field-help">A short line about your study focus, goals, or working style.</span>
                        </div>
                    </div>
                </section>

                <section class="content-card settings-card">
                    <h3>Save Profile</h3>
                    <p class="settings-card-copy">When you save here, the sidebar card and your student identity update immediately.</p>
                    <div class="settings-actions">
                        <button class="action-button" type="submit">Save Profile</button>
                        <a class="secondary-button" href="{{ route('studyhub.student.theme') }}">Open Theme Page</a>
                    </div>
                </section>
            </form>
        </div>

        <aside class="preview-panel">
            <h3>Profile Card Preview</h3>
            <p class="settings-card-copy">This mirrors how your profile button appears in the sidebar.</p>

            <div class="preview-shell">
                <div class="preview-sidebar">
                    <div class="preview-card">
                        <div class="preview-avatar">
                            @if (! empty($studentProfile['avatar_url']))
                                <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $studentProfile['display_name'] }}">
                            @else
                                {{ strtoupper(substr($studentProfile['display_name'], 0, 1)) }}{{ strtoupper(substr(trim(strrchr($studentProfile['display_name'], ' ')) ?: $studentProfile['display_name'], 0, 1)) }}
                            @endif
                        </div>
                        <h4>{{ $studentProfile['display_name'] }}</h4>
                        <p>{{ $studentProfile['email'] }}</p>
                        <span class="preview-button">Open Profile</span>
                    </div>
                </div>

                <div class="preview-card theme-link-card">
                    <h4>Theme Settings</h4>
                    <p>Appearance choices now live on their own dedicated page so profile details stay separate.</p>
                    <a class="action-button" href="{{ route('studyhub.student.theme') }}">Go To Theme Page</a>
                </div>
            </div>
        </aside>
    </div>
@endsection

