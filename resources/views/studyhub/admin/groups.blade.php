@extends('studyhub.admin.layout')

@section('title', 'Group Monitoring')

@push('page-styles')
    <style>
        .group-monitor-list {
            display: grid;
            gap: 14px;
        }

        .monitor-card {
            padding: 18px 20px;
        }

        .monitor-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 10px;
        }

        .monitor-top h3 {
            margin: 0;
            font-size: 1.15rem;
        }

        .status-badge,
        .activity-badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
        }

        .status-badge.active {
            background: #eaf7ee;
            color: var(--green-main);
        }

        .status-badge.flagged {
            background: #f6ece8;
            color: #c45d40;
        }

        .activity-badge.very-high {
            background: #e8f8ef;
            color: #1e8f54;
        }

        .activity-badge.high {
            background: #eef6ff;
            color: #4d6e93;
        }

        .activity-badge.medium {
            background: #fff6df;
            color: #aa7a00;
        }

        .activity-badge.low {
            background: #f9ebe4;
            color: #c45d40;
        }

        .monitor-meta {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .monitor-actions {
            display: flex;
            gap: 10px;
        }
    </style>
@endpush

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
