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

@section('content')
    <div
        class="studyhub-shell app-shell student-shell"
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
                <div class="sidebar-brand-lockup">
                    <span class="sidebar-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M48 8 16 22l32 14 32-14L48 8Z" fill="#122D5D"/>
                            <path d="M31 27c0-3 8-8 17-8s17 5 17 8v11H31V27Z" fill="#122D5D"/>
                            <path d="M48 31c2 0 4 2 5 5l4 18H39l4-18c1-3 3-5 5-5Z" fill="#122D5D"/>
                            <path d="M21 44 15 71c10-4 22-6 33-6V50c-9 0-18-2-27-6Z" fill="#1F8BFF"/>
                            <path d="M75 44c-9 4-18 6-27 6v15c11 0 23 2 33 6l-6-27Z" fill="#4CCB68"/>
                            <path d="M48 50v15c-12 0-25 2-37 7l3-13c10-4 22-6 34-6v-3Z" fill="#122D5D"/>
                            <path d="M48 50v15c12 0 25 2 37 7l-3-13c-10-4-22-6-34-6v-3Z" fill="#122D5D"/>
                            <path d="M80 30a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z" fill="#122D5D"/>
                            <path d="M80 35v14" stroke="#122D5D" stroke-width="3" stroke-linecap="round"/>
                            <path d="M80 49c-3 0-4 4-4 7h8c0-3-1-7-4-7Z" fill="#4CCB68"/>
                        </svg>
                    </span>
                    <span class="sidebar-brand-copy">
                        <h1>Study<span>Hub</span></h1>
                    </span>
                </div>
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
                    <button class="sidebar-link sidebar-logout-button" type="submit">
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

