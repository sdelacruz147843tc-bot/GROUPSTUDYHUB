@extends('studyhub.student.layout')

@section('title', 'Student Dashboard')

@push('page-styles')
    <style>
        .page-title {
            letter-spacing: -0.04em;
        }

        .page-subtitle {
            max-width: 720px;
            font-size: 1.08rem;
        }

        .stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            display: grid;
            gap: 22px;
            min-height: 188px;
            padding: 24px 22px 20px;
            border: 1px solid rgba(186, 214, 195, 0.85);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 56%, white 44%), transparent 32%),
                linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(246,251,247,0.99) 100%);
            box-shadow: 0 22px 42px rgba(80, 112, 95, 0.11);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--student-accent-soft), var(--student-accent));
        }

        .stat-card::after {
            content: '';
            position: absolute;
            right: -30px;
            bottom: -42px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: color-mix(in srgb, var(--student-accent-pale) 42%, white 58%);
            opacity: 0.7;
        }

        .stat-icon {
            position: relative;
            z-index: 1;
            width: 50px;
            height: 50px;
            border-radius: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.5), 0 14px 24px rgba(80, 126, 101, 0.18);
        }

        .stats-grid .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 86%, white 14%) 0%, var(--student-accent) 100%);
            color: white;
        }

        .stats-grid .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 24%, white 76%) 0%, color-mix(in srgb, var(--student-accent) 72%, white 28%) 100%);
            color: white;
        }

        .stats-grid .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 68%, white 32%) 0%, color-mix(in srgb, var(--student-accent) 88%, white 12%) 100%);
            color: white;
        }

        .stats-grid .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 40%, white 60%) 0%, color-mix(in srgb, var(--student-accent) 58%, white 42%) 100%);
            color: white;
        }

        .stat-top {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .stat-kicker {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.74);
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            color: var(--student-accent-text);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .stat-copy {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 6px;
            align-self: end;
        }

        .dashboard-panels {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .dashboard-panel-card {
            overflow: hidden;
            border: 1px solid rgba(196, 219, 204, 0.95);
            background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(248,252,249,0.98) 100%);
            box-shadow: 0 24px 44px rgba(63, 93, 77, 0.1);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 22px;
            border-bottom: 1px solid rgba(201, 225, 211, 0.94);
            background: linear-gradient(90deg, color-mix(in srgb, var(--student-accent-pale) 52%, white 48%) 0%, color-mix(in srgb, var(--student-accent-pale) 38%, white 62%) 48%, #edf6fb 100%);
        }

        .panel-header h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--student-accent-text);
        }

        .panel-header a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.78);
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            color: var(--student-accent-text);
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
        }

        .panel-body {
            padding: 16px;
            display: grid;
            gap: 14px;
        }

        .notifications-panel {
            display: flex;
            flex-direction: column;
        }

        .notifications-panel .panel-body {
            max-height: 360px;
            overflow-y: auto;
            padding-right: 12px;
            scrollbar-width: thin;
            scrollbar-color: color-mix(in srgb, var(--student-accent) 38%, white 62%) transparent;
        }

        .notifications-panel .panel-body::-webkit-scrollbar {
            width: 10px;
        }

        .notifications-panel .panel-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .notifications-panel .panel-body::-webkit-scrollbar-thumb {
            border-radius: 999px;
            border: 2px solid transparent;
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-soft) 72%, white 28%) 0%, color-mix(in srgb, var(--student-accent) 66%, white 34%) 100%);
            background-clip: padding-box;
        }

        .panel-meta {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.78);
            border: 1px solid color-mix(in srgb, var(--student-accent) 20%, white 80%);
            color: var(--student-accent-text);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .dashboard-empty {
            padding: 28px 22px;
            border-radius: 18px;
            border: 1px dashed color-mix(in srgb, var(--student-accent) 26%, white 74%);
            background: color-mix(in srgb, var(--student-accent-pale) 42%, white 58%);
            color: var(--student-accent-text);
            font-weight: 600;
            text-align: center;
        }

        .group-row,
        .notification-row {
            border: 1px solid rgba(202, 220, 208, 0.88);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fcf9 100%);
            padding: 16px 18px;
            box-shadow: 0 12px 24px rgba(95, 126, 104, 0.06);
        }

        .group-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .group-main {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .group-badge {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 78%, white 22%) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 1.1rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 14px 24px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
        }

        .group-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: #163524;
            line-height: 1.2;
            letter-spacing: -0.03em;
        }

        .open-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 82%, white 18%) 0%, color-mix(in srgb, var(--student-accent-soft) 22%, white 78%) 100%);
            color: var(--student-accent-text);
            border: 1px solid color-mix(in srgb, var(--student-accent) 28%, white 72%);
            font-size: 0.92rem;
            font-weight: 700;
        }

        .notification-row p {
            margin: 0 0 10px;
            font-size: 1.05rem;
            line-height: 1.45;
            color: #203328;
        }

        .notification-row {
            position: relative;
            padding-left: 20px;
        }

        .notification-row::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18px;
            bottom: 18px;
            width: 4px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
        }

        .notification-row span {
            color: #6e7f73;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .activity-card {
            margin-top: 20px;
            overflow: hidden;
            border: 1px solid rgba(196, 215, 204, 0.95);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(249,252,250,0.98) 100%);
            box-shadow: 0 24px 44px rgba(63, 93, 77, 0.1);
        }

        .activity-header {
            padding: 24px 28px;
            border-bottom: 1px solid rgba(213, 224, 216, 0.94);
            background: linear-gradient(90deg, #ffffff 0%, color-mix(in srgb, var(--student-accent-pale) 42%, white 58%) 58%, color-mix(in srgb, var(--student-accent-pale) 58%, white 42%) 100%);
        }

        .activity-header h3 {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--student-accent-text);
        }

        .activity-list {
            padding: 8px 28px 18px;
            background: transparent;
        }

        .activity-row {
            display: grid;
            grid-template-columns: 16px minmax(0, 1fr);
            gap: 14px;
            padding: 22px 0;
            border-bottom: 1px solid rgba(217, 229, 221, 0.9);
        }

        .activity-row:last-child {
            border-bottom: 0;
        }

        .activity-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            margin-top: 10px;
            box-shadow: 0 0 0 6px color-mix(in srgb, var(--student-accent-soft) 18%, transparent 82%);
        }

        .activity-text {
            margin: 0 0 8px;
            font-size: 1.15rem;
            line-height: 1.5;
            color: #173223;
        }

        .activity-text strong {
            font-weight: 800;
        }

        .activity-group {
            color: var(--student-accent);
            font-weight: 700;
        }

        .activity-time {
            color: #728376;
            font-size: 0.94rem;
            font-weight: 600;
        }

        .stat-value {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: 2.5rem;
            line-height: 1;
            font-weight: 800;
            color: #173223;
            letter-spacing: -0.05em;
        }

        .stat-label {
            position: relative;
            z-index: 1;
            color: #67776d;
            font-size: 0.98rem;
            font-weight: 700;
        }

        @media (max-width: 1100px) {
            .dashboard-panels {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('page')
    <h2 class="page-title">Welcome back, Student!</h2>
    <p class="page-subtitle">Here's what's happening with your study groups today.</p>

    <section class="stats-grid">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <div class="stat-top">
                    <div class="icon-box stat-icon">
                        {!! $icons[$stat['icon']] !!}
                    </div>
                    <span class="stat-kicker">{{ str_replace(' ', '-', $stat['label']) }}</span>
                </div>
                <div class="stat-copy">
                    <div class="stat-value">{{ $stat['value'] }}</div>
                    <div class="stat-label">{{ $stat['label'] }}</div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="dashboard-panels">
        <article class="content-card dashboard-panel-card">
            <div class="panel-header">
                <h3>My Study Groups</h3>
                <a href="{{ route('studyhub.student.groups') }}">View all</a>
            </div>
            <div class="panel-body">
                @forelse ($groups as $group)
                    <div class="group-row">
                        <div class="group-main">
                            <div class="group-badge">{{ $group['initial'] }}</div>
                            <div class="group-name">{{ $group['name'] }}</div>
                        </div>
                        <a class="open-pill" href="{{ route('studyhub.student.group.show', $group['id']) }}">Open</a>
                    </div>
                @empty
                    <div class="dashboard-empty">Join a group to see it here.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card dashboard-panel-card notifications-panel">
            <div class="panel-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="icon-box">{!! $icons['bell'] !!}</span>
                    <h3 style="font-size:1.8rem;">Notification</h3>
                </div>
                <span class="panel-meta">{{ count($notifications) }} updates</span>
            </div>
            <div class="panel-body">
                @foreach ($notifications as $notification)
                    <div class="notification-row">
                        <p>{{ $notification['text'] }}</p>
                        <span>{{ $notification['time'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="content-card activity-card">
        <div class="activity-header">
            <h3>Recent Activity</h3>
        </div>
        <div class="activity-list">
            @foreach ($recentActivity as $activity)
                <div class="activity-row">
                    <span class="activity-dot" aria-hidden="true"></span>
                    <div>
                        <p class="activity-text">
                            <strong>{{ $activity['actor'] }}</strong> {{ $activity['action'] }}
                            <span class="activity-group">{{ $activity['group'] }}</span>
                        </p>
                        <span class="activity-time">{{ $activity['time'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
