@extends('studyhub.admin.layout')

@section('title', 'Admin Dashboard')

@section('page')
    <h2 class="page-title">Admin Dashboard</h2>
    <p class="page-subtitle">Monitor and manage the StudyHub platform.</p>

    <section class="stats-grid">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;">
                    <div class="icon-box" style="width:44px;height:44px;border-radius:12px;background: {{ $stat['color'] }}20; color: {{ $stat['color'] }};">
                        {!! $icons[$stat['icon']] !!}
                    </div>
                    <span style="color:#2f5540;font-weight:700;">{{ $stat['change'] }}</span>
                </div>
                <div style="font-size:2rem;font-weight:800;margin:16px 0 4px;">{{ $stat['value'] }}</div>
                <div style="color:var(--text-muted);">{{ $stat['label'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="admin-grid">
        <article class="content-card panel">
            <h3>User Activity Trend</h3>
            <div class="chart-list">
                @foreach ($userActivityData as $entry)
                    <div class="chart-row">
                        <div style="display:flex;justify-content:space-between;gap:14px;">
                            <strong>{{ $entry['month'] }}</strong>
                            <span class="muted">{{ $entry['users'] }} users</span>
                        </div>
                        <div class="chart-bar-track">
                            <div class="chart-bar-fill" style="width: {{ ($entry['users'] / 1248) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="content-card panel">
            <h3>Recent Alerts</h3>
            <div class="alert-list">
                @foreach ($recentAlerts as $alert)
                    <div class="alert-row">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                            <span class="icon-box" style="color: {{ $alert['type'] === 'warning' ? '#e76f51' : '#2a9d8f' }}">
                                {!! $icons[$alert['type'] === 'warning' ? 'warning' : 'info'] !!}
                            </span>
                            <strong>{{ ucfirst($alert['type']) }}</strong>
                        </div>
                        <div>{{ $alert['message'] }}</div>
                        <div class="alert-meta">{{ $alert['time'] }}</div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="content-card panel">
        <h3>Resource Distribution</h3>
        <div class="resource-list">
            @foreach ($resourceData as $resource)
                <div class="resource-row">
                    <strong>{{ $resource['category'] }}</strong>
                    <span class="muted">{{ $resource['count'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endsection

