@extends('studyhub.admin.layout')

@section('title', 'Group Monitoring')

@section('page')
    <div class="toolbar">
        <div>
            <h2 class="page-title">Group Monitoring</h2>
            <p class="page-subtitle">Keep an eye on group activity and status.</p>
        </div>
    </div>

    <section class="stats-grid">
        @foreach ($summary as $item)
            <article class="stat-card">
                <div style="color:var(--text-muted);margin-bottom:8px;">{{ $item['label'] }}</div>
                <div style="font-size:2rem;font-weight:800;color:{{ $item['color'] }};">{{ $item['value'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="group-monitor-list">
        @foreach ($groups as $group)
            <article class="content-card monitor-card">
                <div class="monitor-top">
                    <h3>{{ $group['name'] }}</h3>
                    <span class="status-badge {{ strtolower($group['status']) }}">{{ $group['status'] }}</span>
                </div>
                <div class="monitor-meta">
                    <span>{{ $group['members'] }} members</span>
                    <span>{{ $group['resources'] }} resources</span>
                    <span>{{ $group['discussions'] }} discussions</span>
                    <span>Created {{ $group['created'] }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;">
                    <span class="activity-badge {{ strtolower(str_replace(' ', '-', $group['activity'])) }}">{{ $group['activity'] }} activity</span>
                    <div class="monitor-actions">
                        <a class="secondary-button" href="#">Review</a>
                        <a class="action-button" href="#">Manage</a>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
@endsection

