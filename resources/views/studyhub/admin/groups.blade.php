@extends('studyhub.admin.layout')

@section('title', 'Group Monitoring')

@section('page')
    @php
        $summaryTextClasses = [
            '#0F4C75' => 'text-[#0F4C75]',
            '#3282B8' => 'text-[#3282B8]',
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
        @forelse ($groups as $group)
            <article class="content-card monitor-card">
                <div class="monitor-top">
                    <h3>{{ $group['name'] }}</h3>
                    <span class="status-badge {{ strtolower($group['status']) }}">{{ $group['status'] }}</span>
                </div>
                <p class="monitor-description">{{ $group['description'] }}</p>
                <div class="monitor-meta">
                    <span>{{ $group['category'] }}</span>
                    <span>Owner: {{ $group['owner'] }}</span>
                    <span>{{ $group['members'] }} members</span>
                    <span>{{ $group['resources'] }} resources</span>
                    <span>{{ $group['discussions'] }} discussions</span>
                    <span>{{ $group['sessions'] }} sessions</span>
                    <span>Created {{ $group['created'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-3.5">
                    <span class="activity-badge {{ strtolower(str_replace(' ', '-', $group['activity'])) }}">{{ $group['activity'] }} activity</span>
                    <div class="monitor-actions">
                        <a class="secondary-button" href="{{ route('studyhub.admin.groups.show', $group['id']) }}">
                            <span class="icon-box">{!! $icons['eye'] !!}</span>
                            <span>Review</span>
                        </a>
                        <a class="action-button" href="{{ route('studyhub.admin.groups.show', $group['id']) }}#manage-group">
                            <span class="icon-box">{!! $icons['edit'] !!}</span>
                            <span>Manage</span>
                        </a>
                        <form method="POST" action="{{ route('studyhub.admin.groups.delete', $group['id']) }}" onsubmit="return confirm('Delete {{ addslashes($group['name']) }} and all of its StudyHub records?')">
                            @csrf
                            @method('DELETE')
                            <button class="action-chip delete" type="submit">
                                <span class="icon-box">{!! $icons['trash'] !!}</span>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <article class="content-card monitor-card empty-panel">
                No study groups have been created yet.
            </article>
        @endforelse
    </section>
@endsection
