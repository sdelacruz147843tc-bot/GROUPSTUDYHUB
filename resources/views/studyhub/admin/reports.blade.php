@extends('studyhub.admin.layout')

@section('title', 'Reports & Analytics')

@section('page')
    @php
        $statTextClasses = [
            '#0F4C75' => 'text-[#0F4C75]',
            '#3282B8' => 'text-[#3282B8]',
            '#06D6A0' => 'text-[#06D6A0]',
            '#FF6B35' => 'text-[#FF6B35]',
            '#79532d' => 'text-[#79532d]',
        ];

        $dotColorClasses = [
            '#0F4C75' => 'bg-[#0F4C75]',
            '#3282B8' => 'bg-[#3282B8]',
            '#06D6A0' => 'bg-[#06D6A0]',
            '#FF6B35' => 'bg-[#FF6B35]',
            '#79532d' => 'bg-[#79532d]',
        ];
        $isSixMonthRange = ($range ?? '6_months') !== 'all';
    @endphp

    <div class="toolbar">
        <div>
            <h2 class="page-title">Reports & Analytics</h2>
            <p class="page-subtitle">Platform insights and statistics.</p>
        </div>
        <div class="toolbar-actions">
            <a class="secondary-button {{ $isSixMonthRange ? 'is-active' : '' }}" href="{{ route('studyhub.admin.reports', ['range' => '6_months']) }}">Last 6 Months</a>
            <a class="secondary-button {{ ! $isSixMonthRange ? 'is-active' : '' }}" href="{{ route('studyhub.admin.reports', ['range' => 'all']) }}">All Time</a>
            <a class="action-button" href="{{ route('studyhub.admin.reports.export') }}">
                <span class="icon-box">{!! $icons['download'] !!}</span>
                <span>Export Report</span>
            </a>
        </div>
    </div>

    <section class="stats-grid">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <div class="mb-2 text-[var(--text-muted)]">{{ $stat['label'] }}</div>
                <div class="text-[2rem] font-extrabold {{ $statTextClasses[$stat['color']] ?? 'text-[#0F4C75]' }}">{{ $stat['value'] }}</div>
                <div class="mt-2 font-bold text-[#2f5540]">{{ $stat['change'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="report-grid">
        <article class="content-card report-panel">
            <h3>User Growth Trend</h3>
            <div class="bar-list">
                @forelse ($monthlyUserData as $entry)
                    <div class="bar-row">
                        <div class="flex justify-between gap-3.5">
                            <strong>{{ $entry['month'] }}</strong>
                            <span class="text-[var(--text-muted)]">{{ $entry['students'] }} students | {{ $entry['admins'] }} admins</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ (int) $entry['percent'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-panel">No user growth data is available for this range.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card report-panel">
            <h3>Resource Distribution</h3>
            <div class="pie-list">
                @forelse ($resourceTypeData as $entry)
                    <div class="pie-row">
                        <div>
                            <span class="color-dot {{ $dotColorClasses[$entry['color']] ?? 'bg-[#0F4C75]' }}"></span>
                            <strong>{{ $entry['name'] }}</strong>
                        </div>
                        <span class="text-[var(--text-muted)]">{{ $entry['value'] }}</span>
                    </div>
                @empty
                    <div class="empty-panel">No resource categories have been recorded yet.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="content-card report-panel mt-6">
        <h3>Platform Activity</h3>
        <div class="platform-activity-list">
            @foreach ($activityData as $item)
                <div class="platform-activity-card">
                    <span class="platform-activity-label">{{ $item['category'] }}</span>
                    <strong class="platform-activity-value">{{ $item['count'] }}</strong>
                </div>
            @endforeach
        </div>
    </section>
@endsection
