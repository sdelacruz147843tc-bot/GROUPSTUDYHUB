@extends('studyhub.layout')

@php
    $icons = [
        'dashboard' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
        'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'groups' => '<svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'reports' => '<svg viewBox="0 0 24 24"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-6"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>',
        'book' => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'activity' => '<svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 8-6-16-3 8H2"/></svg>',
        'discussion' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'trend' => '<svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7"/><path d="M14 8h6v6"/></svg>',
        'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
        'warning' => '<svg viewBox="0 0 24 24"><path d="M12 3l10 18H2L12 3z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
        'info' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6"/><path d="M12 7h.01"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"/></svg>',
        'ban' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg>',
    ];

    $adminNav = [
        ['label' => 'Dashboard', 'route' => 'studyhub.admin.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Users', 'route' => 'studyhub.admin.users', 'icon' => 'users'],
        ['label' => 'Groups', 'route' => 'studyhub.admin.groups', 'icon' => 'groups'],
        ['label' => 'Reports', 'route' => 'studyhub.admin.reports', 'icon' => 'reports'],
    ];

    $currentRoute = request()->route()?->getName();
@endphp

@push('styles')
    <style>
        .app-shell {
            display: grid;
            grid-template-columns: 212px minmax(0, 1fr);
            min-height: 100vh;
            background: #f7f7f5;
        }

        .app-sidebar {
            background: #2f5540;
            color: white;
            padding: 22px 0 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            weight: 100px;
            overflow: hidden;
        }

        .sidebar-brand {
            padding: 0 22px 24px;
        }

        .sidebar-brand h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
        }

        .sidebar-brand p {
            margin: 4px 0 0;
            color: rgba(255,255,255,0.75);
        }

        .sidebar-nav {
            display: grid;
            gap: 12px;
            padding: 12px 14px 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 14px 16px;
            border-radius: 14px;
            color: rgba(255,255,255,0.94);
            font-size: 1rem;
            font-weight: 600;
        }

        .sidebar-link.active {
            background: rgba(255,255,255,0.16);
        }

        .sidebar-bottom {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.12);
            padding: 16px 14px;
        }

        .app-main {
            padding: 28px 30px 32px;
            min-height: 100vh;
        }

        .page-title {
            margin: 0;
            font-size: 2.8rem;
            font-weight: 800;
            color: #111;
        }

        .layout-status {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #eef4ef;
            border: 1px solid #d4e2d7;
            color: #2f5540;
        }

        .page-subtitle {
            margin: 8px 0 26px;
            color: var(--text-muted);
            font-size: 1.02rem;
        }

        .content-card,
        .stat-card {
            background: white;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            box-shadow: var(--card-shadow);
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
        }

        .action-button {
            background: #2f5540;
            color: white;
        }

        .secondary-button {
            background: #edf1f5;
            color: #33404a;
            border: 1px solid var(--border-soft);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 18px;
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

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 800px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .app-sidebar {
                position: static;
                height: auto;
            }

            .app-main {
                padding: 20px;
            }

            .page-title {
                font-size: 2.2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('page-styles')
@endpush

@section('content')
    <div class="studyhub-shell app-shell">
        <aside class="app-sidebar">
            <div class="sidebar-brand">
                <h1>StudyHub</h1>
                <p>Admin Panel</p>
            </div>

            <nav class="sidebar-nav">
                @foreach ($adminNav as $item)
                    <a class="sidebar-link {{ $currentRoute === $item['route'] ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <span class="icon-box">{!! $icons[$item['icon']] !!}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-bottom">
                <form method="POST" action="{{ route('studyhub.logout') }}">
                    @csrf
                    <button class="sidebar-link" type="submit" style="width: 100%; border: 0; background: transparent; text-align: left;">
                        <span class="icon-box">{!! $icons['logout'] !!}</span>
                        <span>Logout</span>
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
