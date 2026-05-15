@extends('studyhub.admin.layout')

@section('title', 'Admin Dashboard')

@section('page')
    @php
        $statColorClasses = [
            '#0F4C75' => 'bg-[#0F4C75]/20 text-[#0F4C75]',
            '#3282B8' => 'bg-[#3282B8]/20 text-[#3282B8]',
            '#06D6A0' => 'bg-[#06D6A0]/20 text-[#06D6A0]',
            '#FF6B35' => 'bg-[#FF6B35]/20 text-[#FF6B35]',
            '#79532d' => 'bg-[#79532d]/20 text-[#79532d]',
        ];
    @endphp

    <div class="admin-dashboard-header">
        <div>
            <h2 class="page-title">Admin Dashboard</h2>
            <p class="page-subtitle">Monitor platform health, jump into admin workflows, and review new activity.</p>
        </div>
    </div>

    <section class="admin-quick-actions" aria-label="Quick admin actions">
        @foreach ($quickActions as $action)
            <a class="admin-quick-action {{ $action['style'] === 'primary' ? 'is-primary' : '' }}" href="{{ $action['route'] }}">
                <span class="icon-box">{!! $icons[$action['icon']] !!}</span>
                <span>
                    <strong>{{ $action['label'] }}</strong>
                    <small>{{ $action['description'] }}</small>
                </span>
            </a>
        @endforeach
    </section>

    <section class="stats-grid admin-dashboard-stats">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <div class="flex items-center justify-between gap-3.5">
                    <div class="icon-box h-11 w-11 rounded-xl {{ $statColorClasses[$stat['color']] ?? 'bg-[#0F4C75]/20 text-[#0F4C75]' }}">
                        {!! $icons[$stat['icon']] !!}
                    </div>
                    <span class="font-bold text-[#2f5540]">{{ $stat['change'] }}</span>
                </div>
                <div class="my-4 mb-1 text-[2rem] font-extrabold">{{ $stat['value'] }}</div>
                <div class="text-[var(--text-muted)]">{{ $stat['label'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="admin-grid">
        <article class="content-card panel">
            <h3>User Activity Trend</h3>
            <div class="chart-list">
                @forelse ($userActivityData as $entry)
                    <div class="chart-row">
                        <div class="flex justify-between gap-3.5">
                            <strong>{{ $entry['month'] }}</strong>
                            <span class="muted">{{ $entry['users'] }} users</span>
                        </div>
                        <div class="chart-bar-track">
                            <div class="chart-bar-fill" style="width: {{ (int) $entry['percent'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-panel">No user activity has been recorded yet.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card panel">
            <h3>Recent Alerts</h3>
            <div class="alert-list">
                @forelse ($recentAlerts as $alert)
                    <div class="alert-row {{ $alert['type'] === 'warning' ? 'is-warning' : 'is-info' }}">
                        <div class="mb-2 flex items-center gap-2.5">
                            <span class="icon-box {{ $alert['type'] === 'warning' ? 'text-[#e76f51]' : 'text-[#2a9d8f]' }}">
                                {!! $icons[$alert['type'] === 'warning' ? 'warning' : 'info'] !!}
                            </span>
                            <strong>{{ $alert['label'] }}</strong>
                        </div>
                        <div>{{ $alert['message'] }}</div>
                        <div class="alert-meta">{{ $alert['time'] }}</div>
                    </div>
                @empty
                    <div class="empty-panel">No alerts need attention right now.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="admin-grid admin-grid-balanced">
        <article class="content-card panel">
            <h3>Recent Activity</h3>
            <div class="admin-activity-feed">
                @forelse ($recentActivity as $activity)
                    <div class="admin-activity-row">
                        <span class="icon-box">{!! $icons[$activity['icon']] !!}</span>
                        <span class="admin-activity-copy">
                            <strong>{{ $activity['message'] }}</strong>
                            <span>{{ $activity['title'] }}</span>
                            <small>{{ $activity['meta'] }}</small>
                        </span>
                        <time>{{ $activity['time'] }}</time>
                    </div>
                @empty
                    <div class="empty-panel">No platform activity has been recorded yet.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card panel">
            <h3>Resource Distribution</h3>
            <div class="resource-list admin-resource-bars">
                @forelse ($resourceData as $resource)
                    <div class="resource-row admin-resource-row">
                        <div class="admin-resource-row-top">
                            <strong>{{ $resource['category'] }}</strong>
                            <span class="muted">{{ $resource['count'] }}</span>
                        </div>
                        <div class="resource-bar-track" aria-hidden="true">
                            <div class="resource-bar-fill" style="width: {{ (int) $resource['percent'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-panel">No resources have been uploaded yet.</div>
                @endforelse
            </div>
        </article>
    </section>
@endsection
