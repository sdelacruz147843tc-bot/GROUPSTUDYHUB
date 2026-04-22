@extends('studyhub.student.layout')

@section('title', 'Profile')

@push('page-styles')
    <style>
        .profile-page-header {
            margin-bottom: 24px;
        }

        .profile-page-header .page-title {
            letter-spacing: -0.04em;
        }

        .profile-page-header .page-subtitle {
            max-width: 760px;
            margin-bottom: 0;
        }

        .profile-banner {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            gap: 20px;
            align-items: center;
            padding: 24px;
            margin-bottom: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            border-radius: 24px;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 72%, white 28%) 0%, rgba(255,255,255,0.96) 100%);
        }

        .profile-banner-avatar {
            width: 96px;
            height: 96px;
            border-radius: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: var(--student-accent-text);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 38%, white 62%) 0%, color-mix(in srgb, var(--student-accent-pale) 74%, white 26%) 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 18px 28px rgba(79, 111, 91, 0.12);
            overflow: hidden;
        }

        .profile-banner-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .profile-banner h3 {
            margin: 0 0 6px;
            font-size: 1.8rem;
            letter-spacing: -0.03em;
            color: #173223;
        }

        .profile-banner p {
            margin: 0 0 10px;
            color: #62746a;
            line-height: 1.55;
        }

        .profile-banner-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .profile-banner-chip {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--student-accent-text);
            background: rgba(255,255,255,0.8);
            border: 1px solid color-mix(in srgb, var(--student-accent-soft) 30%, white 70%);
        }

        .profile-status {
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent-soft) 42%, white 58%);
            background: color-mix(in srgb, var(--student-accent-pale) 78%, white 22%);
            color: var(--student-accent-text);
            font-weight: 700;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 22px;
        }

        .settings-card {
            padding: 22px;
            border-radius: 24px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
        }

        .settings-card + .settings-card {
            margin-top: 18px;
        }

        .settings-card h3 {
            margin: 0 0 8px;
            font-size: 1.45rem;
            letter-spacing: -0.03em;
            color: #173223;
        }

        .settings-card-copy {
            margin: 0 0 18px;
            color: #66776d;
            line-height: 1.55;
        }

        .settings-grid {
            display: grid;
            gap: 16px;
        }

        .settings-field {
            display: grid;
            gap: 8px;
        }

        .settings-field label,
        .option-group-title {
            color: #294437;
            font-size: 0.94rem;
            font-weight: 700;
        }

        .settings-field input,
        .settings-field textarea {
            width: 100%;
            border: 1px solid rgba(192, 214, 200, 0.95);
            border-radius: 16px;
            background: rgba(255,255,255,0.92);
            padding: 13px 15px;
            font: inherit;
            color: #173223;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.75);
        }

        .settings-field textarea {
            min-height: 108px;
            resize: vertical;
        }

        .field-help {
            color: #7a8a81;
            font-size: 0.84rem;
        }

        .settings-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .settings-actions button {
            min-height: 48px;
            padding: 0 18px;
        }

        .preview-panel {
            padding: 22px;
            border-radius: 24px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            position: sticky;
            top: 26px;
        }

        .preview-shell {
            padding: 16px;
            border-radius: 22px;
            background: var(--student-page-bg);
            border: 1px solid rgba(198, 216, 205, 0.88);
        }

        .preview-sidebar {
            padding: 16px;
            border-radius: 18px;
            background: var(--student-sidebar-bg);
            color: white;
            margin-bottom: 14px;
        }

        .preview-card {
            padding: 18px;
            border-radius: var(--student-card-radius);
            border: 1px solid var(--student-card-border);
            background: rgba(255,255,255,0.94);
            box-shadow: var(--student-card-shadow);
        }

        .preview-avatar {
            width: 64px;
            height: 64px;
            margin-bottom: 14px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--student-accent-text);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 40%, white 60%) 0%, color-mix(in srgb, var(--student-accent-pale) 74%, white 26%) 100%);
        }

        .preview-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .preview-card h4 {
            margin: 0 0 8px;
            font-size: 1rem;
            color: #173223;
        }

        .preview-card p {
            margin: 0 0 16px;
            color: #69796f;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .preview-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.86rem;
            font-weight: 800;
        }

        @media (max-width: 1100px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }

            .preview-panel {
                position: static;
            }
        }

        .theme-link-card {
            display: grid;
            gap: 10px;
        }

        .theme-link-card .secondary-button,
        .theme-link-card .action-button {
            justify-content: center;
        }

        @media (max-width: 760px) {
            .profile-banner {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

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
