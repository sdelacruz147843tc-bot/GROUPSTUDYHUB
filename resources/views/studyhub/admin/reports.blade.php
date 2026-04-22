@extends('studyhub.admin.layout')

@section('title', 'Reports & Analytics')

@section('page')
    <div class="toolbar">
        <div>
            <h2 class="page-title">Reports & Analytics</h2>
            <p class="page-subtitle">Platform insights and statistics.</p>
        </div>
        <div class="toolbar-actions">
            <button class="secondary-button" type="button">Last 6 Months</button>
            <button class="action-button" type="button">Export Report</button>
        </div>
    </div>

    <section class="stats-grid">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <div style="color:var(--text-muted);margin-bottom:8px;">{{ $stat['label'] }}</div>
                <div style="font-size:2rem;font-weight:800;color:{{ $stat['color'] }};">{{ $stat['value'] }}</div>
                <div style="margin-top:8px;color:#2f5540;font-weight:700;">{{ $stat['change'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="report-grid">
        <article class="content-card report-panel">
            <h3>User Growth Trend</h3>
            <div class="bar-list">
                @foreach ($monthlyUserData as $entry)
                    <div class="bar-row">
                        <div style="display:flex;justify-content:space-between;gap:14px;">
                            <strong>{{ $entry['month'] }}</strong>
                            <span style="color:var(--text-muted);">{{ $entry['students'] }} students · {{ $entry['admins'] }} admins</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ ($entry['students'] / 1230) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="content-card report-panel">
            <h3>Resource Distribution</h3>
            <div class="pie-list">
                @foreach ($resourceTypeData as $entry)
                    <div class="pie-row">
                        <div>
                            <span class="color-dot" style="background: {{ $entry['color'] }};"></span>
                            <strong>{{ $entry['name'] }}</strong>
                        </div>
                        <span style="color:var(--text-muted);">{{ $entry['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="content-card report-panel" style="margin-top:24px;">
        <h3>Platform Activity</h3>
        <div class="activity-list">
            @foreach ($activityData as $item)
                <div class="activity-row">
                    <strong>{{ $item['category'] }}</strong>
                    <span style="font-size:1.4rem;font-weight:800;color:#2f5540;">{{ $item['count'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endsection

