@extends('studyhub.admin.layout')

@section('title', 'Group Monitoring')

@section('page')
    @php
        $summaryTextClasses = [
            '#0F4C75' => 'text-[#0F4C75]',
            '#06D6A0' => 'text-[#06D6A0]',
            '#FF6B35' => 'text-[#FF6B35]',
        ];
    @endphp

    <div class="toolbar">
        <div>
            <h2 class="page-title">Group Monitoring</h2>
            <p class="page-subtitle">Keep an eye on group activity and status.</p>
        </div>
    </div>

    <section class="stats-grid">
        @foreach ($summary as $item)
            <article class="stat-card">
                <div class="mb-2 text-[var(--text-muted)]">{{ $item['label'] }}</div>
                <div class="text-[2rem] font-extrabold {{ $summaryTextClasses[$item['color']] ?? 'text-[#0F4C75]' }}">{{ $item['value'] }}</div>
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
                <div class="flex items-center justify-between gap-3.5">
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
