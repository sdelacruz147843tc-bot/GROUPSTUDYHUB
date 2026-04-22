@extends('studyhub.layout')

@php
    $icons = [
        'home' => '<svg viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/></svg>',
        'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'resources' => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'discussion' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>',
        'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
        'message' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'bell' => '<svg viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>',
        'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
        'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
        'download' => '<svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>',
        'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>',
        'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24"><path d="M12 3l1.7 2.7 3.1.5.5 3.1L20 11l-2.7 1.7-.5 3.1-3.1.5L12 19l-1.7-2.7-3.1-.5-.5-3.1L4 11l2.7-1.7.5-3.1 3.1-.5L12 3z"/><circle cx="12" cy="11" r="3"/></svg>',
        'trend' => '<svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7"/><path d="M14 8h6v6"/></svg>',
        'map-pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s-6-4.5-6-10a6 6 0 1 1 12 0c0 5.5-6 10-6 10z"/><circle cx="12" cy="11" r="2.5"/></svg>',
        'video' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3"/></svg>',
        'arrow-left' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>',
    ];

    $studentNav = [
        ['label' => 'Dashboard', 'route' => 'studyhub.student.dashboard', 'icon' => 'home'],
        ['label' => 'Study Groups', 'route' => 'studyhub.student.groups', 'icon' => 'users'],
        ['label' => 'Resources', 'route' => 'studyhub.student.resources', 'icon' => 'resources'],
        ['label' => 'Discussions', 'route' => 'studyhub.student.discussions', 'icon' => 'discussion'],
        ['label' => 'Sessions', 'route' => 'studyhub.student.sessions', 'icon' => 'calendar'],
    ];

    $currentRoute = request()->route()?->getName();
@endphp

@push('styles')
    <style>
        @keyframes pageFadeIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes sidebarGlow {
            0%,
            100% {
                opacity: 0.7;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.04);
            }
        }

        .app-shell {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            min-height: 100vh;
            background: var(--student-page-bg);
        }

        .app-sidebar {
            background: var(--student-sidebar-bg);
            color: white;
            padding: 24px 18px 18px;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            border-right: 1px solid rgba(255,255,255,0.1);
            box-shadow: 24px 0 48px rgba(23, 54, 33, 0.14);
        }

        .app-sidebar::before {
            content: '';
            position: absolute;
            inset: 20px 18px auto;
            height: 160px;
            border-radius: 28px;
            background: radial-gradient(circle at top, rgba(255,255,255,0.16), transparent 68%);
            pointer-events: none;
        }

        .sidebar-brand {
            position: relative;
            padding: 20px 22px 22px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.07));
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.12);
            overflow: hidden;
        }

        .sidebar-brand::after {
            content: '';
            position: absolute;
            right: -42px;
            top: -34px;
            width: 128px;
            height: 128px;
            border-radius: 50%;
            background: rgba(196, 255, 216, 0.12);
            animation: sidebarGlow 7s ease-in-out infinite;
        }

        .sidebar-brand h1 {
            position: relative;
            margin: 0;
            font-size: 2.1rem;
            letter-spacing: -0.04em;
            font-weight: 800;
        }

        .sidebar-brand p {
            position: relative;
            margin: 4px 0 0;
            font-size: 0.96rem;
            color: rgba(240,255,245,0.82);
        }

        .sidebar-kicker {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            margin-bottom: 12px;
            border-radius: 999px;
            background: rgba(212, 255, 225, 0.14);
            border: 1px solid rgba(255,255,255,0.14);
            color: rgba(242,255,246,0.88);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sidebar-section-label {
            padding: 18px 14px 10px;
            color: rgba(234, 248, 237, 0.62);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: grid;
            gap: var(--student-density-gap);
            padding: 0 4px;
        }

        .sidebar-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 18px;
            color: rgba(245,255,248,0.82);
            font-size: 1rem;
            font-weight: 700;
            transition: transform 180ms ease, background 180ms ease, color 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .sidebar-link .icon-box {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
            transition: inherit;
        }

        .sidebar-link .nav-copy {
            display: grid;
            gap: 2px;
        }

        .sidebar-link .nav-title {
            line-height: 1.1;
        }

        .sidebar-link .nav-meta {
            color: rgba(237, 248, 240, 0.56);
            font-size: 0.8rem;
            font-weight: 500;
            transition: inherit;
        }

        .sidebar-link:hover {
            transform: translateX(4px);
            color: white;
            background: rgba(255,255,255,0.08);
        }

        .sidebar-link:hover .icon-box {
            background: rgba(255,255,255,0.14);
        }

        .sidebar-link.active {
            color: var(--student-accent-text);
            background: linear-gradient(90deg, color-mix(in srgb, var(--student-accent-pale) 82%, white 18%) 0%, color-mix(in srgb, var(--student-accent-pale) 68%, white 32%) 100%);
            box-shadow: 0 18px 30px rgba(10, 35, 18, 0.18);
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 14px;
            bottom: 14px;
            width: 5px;
            border-radius: 999px;
            background: var(--student-accent-soft);
        }

        .sidebar-link.active .icon-box {
            background: color-mix(in srgb, var(--student-accent-soft) 18%, white 82%);
            border-color: color-mix(in srgb, var(--student-accent) 24%, white 76%);
            color: var(--student-accent-text);
        }

        .sidebar-link.active .nav-meta {
            color: color-mix(in srgb, var(--student-accent-text) 68%, transparent 32%);
        }

        .sidebar-profile {
            margin-top: 18px;
            padding: 16px 18px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.08));
            border: 1px solid rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 180ms ease, background 180ms ease, box-shadow 180ms ease;
        }

        .sidebar-profile:hover {
            transform: translateY(-2px);
            background: linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0.08));
            box-shadow: 0 16px 24px rgba(8, 28, 16, 0.12);
        }

        .profile-avatar {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 82%, white 18%) 0%, color-mix(in srgb, var(--student-accent-soft) 42%, white 58%) 100%);
            color: var(--student-accent-text);
            font-size: 1rem;
            font-weight: 800;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
            display: block;
        }

        .profile-copy {
            display: grid;
            gap: 3px;
        }

        .profile-name {
            font-size: 1rem;
            font-weight: 700;
        }

        .profile-role {
            color: rgba(240,255,245,0.68);
            font-size: 0.82rem;
        }

        .profile-card-button {
            text-decoration: none;
        }

        .profile-card-button .profile-copy {
            min-width: 0;
        }

        .profile-card-button .profile-name,
        .profile-card-button .profile-role {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding: 18px 4px 0;
        }

        .sidebar-bottom .sidebar-link {
            border: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-bottom .sidebar-link .icon-box {
            background: rgba(255,255,255,0.1);
        }

        .app-main {
            padding: var(--student-main-padding);
            min-height: 100vh;
        }

        .app-main > * {
            animation: pageFadeIn 420ms ease both;
        }

        .app-main > *:nth-child(2) {
            animation-delay: 70ms;
        }

        .app-main > *:nth-child(3) {
            animation-delay: 140ms;
        }

        .app-main > *:nth-child(4) {
            animation-delay: 210ms;
        }

        .page-title {
            margin: 0;
            font-size: 3rem;
            font-weight: 800;
            color: #111;
        }

        .layout-status {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.88);
            border: 1px solid color-mix(in srgb, var(--student-accent-pale) 80%, white 20%);
            color: var(--student-accent-text);
            box-shadow: var(--student-card-shadow);
        }

        .page-subtitle {
            margin: 8px 0 26px;
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        .content-card,
        .stat-card {
            background: white;
            border: 1px solid var(--student-card-border);
            border-radius: var(--student-card-radius);
            box-shadow: var(--student-card-shadow);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .content-card:hover,
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: rgba(165, 203, 178, 0.9);
            box-shadow: 0 24px 40px rgba(47, 77, 95, 0.14);
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .toolbar-actions,
        .toolbar-filters {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .action-button,
        .secondary-button {
            border: 0;
            border-radius: 12px;
            padding: 12px 18px;
            font: inherit;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease, background 180ms ease;
        }

        .action-button:hover,
        .secondary-button:hover {
            transform: translateY(-2px);
            filter: brightness(1.02);
        }

        .action-button:active,
        .secondary-button:active {
            transform: translateY(0);
        }

        .action-button {
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
        }

        .secondary-button {
            background: rgba(255,255,255,0.92);
            color: #33404a;
            border: 1px solid color-mix(in srgb, var(--student-card-border) 92%, white 8%);
        }

        .search-box {
            position: relative;
            max-width: 560px;
            width: 100%;
        }

        .search-box input {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            border: 1px solid var(--border-soft);
            background: white;
            padding: 0 16px 0 48px;
            font: inherit;
        }

        .search-box .icon-box {
            position: absolute;
            top: 14px;
            left: 14px;
            color: var(--text-muted);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 18px;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            margin-bottom: 16px;
            color: white;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .stat-label {
            color: var(--text-muted);
        }

        .empty-state {
            text-align: center;
            padding: 56px 24px;
            color: var(--text-muted);
        }

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-actions,
            .toolbar-filters {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 800px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .app-sidebar {
                position: static;
                height: auto;
                margin: 0 0 18px;
            }

            .app-main {
                padding: 20px;
            }

            .page-title {
                font-size: 2.25rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('page-styles')
@endpush

@section('content')
    <div
        class="studyhub-shell app-shell"
        style="
            --student-page-bg: {{ $studentTheme['page_bg'] }};
            --student-sidebar-bg: {{ $studentTheme['sidebar_bg'] }};
            --student-accent: {{ $studentTheme['accent'] }};
            --student-accent-soft: {{ $studentTheme['accent_soft'] }};
            --student-accent-pale: {{ $studentTheme['accent_pale'] }};
            --student-accent-text: {{ $studentTheme['accent_text'] }};
            --student-card-radius: {{ $studentTheme['card_radius'] }};
            --student-card-shadow: {{ $studentTheme['card_shadow'] }};
            --student-card-border: {{ $studentTheme['card_border'] }};
            --student-density-gap: {{ $studentProfile['interface_density'] === 'compact' ? '8px' : '10px' }};
            --student-main-padding: {{ $studentProfile['interface_density'] === 'compact' ? '24px 28px 28px' : '30px 34px 34px' }};
        "
    >
        <aside class="app-sidebar">
            <div class="sidebar-brand">
                <span class="sidebar-kicker">Student Workspace</span>
                <h1>StudyHub</h1>
                <p>Collaborative Learning</p>
            </div>

            <div class="sidebar-section-label">Navigation</div>
            <nav class="sidebar-nav">
                @foreach ($studentNav as $item)
                    <a class="sidebar-link {{ $currentRoute === $item['route'] ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <span class="icon-box">{!! $icons[$item['icon']] !!}</span>
                        <span class="nav-copy">
                            <span class="nav-title">{{ $item['label'] }}</span>
                            <span class="nav-meta">{{ $currentRoute === $item['route'] ? 'Current page' : 'Open section' }}</span>
                        </span>
                    </a>
                @endforeach
            </nav>

            <a class="sidebar-profile profile-card-button" href="{{ route('studyhub.student.profile') }}">
                <span class="profile-avatar">
                    @if (! empty($studentProfile['avatar_url']))
                        <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $studentProfile['display_name'] }}">
                    @else
                        {{ strtoupper(substr($studentProfile['display_name'], 0, 1)) }}{{ strtoupper(substr(trim(strrchr($studentProfile['display_name'], ' ')) ?: $studentProfile['display_name'], 0, 1)) }}
                    @endif
                </span>
                <span class="profile-copy">
                    <span class="profile-name">{{ $studentProfile['display_name'] }}</span>
                    <span class="profile-role">{{ $studentProfile['email'] }}</span>
                </span>
            </a>

            <div class="sidebar-bottom">
                <form method="POST" action="{{ route('studyhub.logout') }}">
                    @csrf
                    <button class="sidebar-link" type="submit" style="width: 100%; border: 0; background: transparent; text-align: left;">
                        <span class="icon-box">{!! $icons['logout'] !!}</span>
                        <span class="nav-copy">
                            <span class="nav-title">Logout</span>
                            <span class="nav-meta">Return to sign in</span>
                        </span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="app-main">
            @if (session('status'))
                <div class="layout-status">{{ session('status') }}</div>
            @endif
            @yield('page')
        </main>
    </div>
@endsection
