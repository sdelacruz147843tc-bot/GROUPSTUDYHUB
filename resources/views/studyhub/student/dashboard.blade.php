@extends('studyhub.student.layout')

@section('title', 'Student Dashboard')

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
                <div class="panel-heading-wrap">
                    <span class="icon-box">{!! $icons['bell'] !!}</span>
                    <h3>Notification</h3>
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
                    <div class="activity-content">
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

