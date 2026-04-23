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

        $widthClass = function (int $value, int $max): string {
            $percent = $max > 0 ? (int) round(($value / $max) * 100 / 5) * 5 : 0;
            $percent = max(0, min(100, $percent));

            return [
                0 => 'w-[0%]', 5 => 'w-[5%]', 10 => 'w-[10%]', 15 => 'w-[15%]', 20 => 'w-[20%]',
                25 => 'w-[25%]', 30 => 'w-[30%]', 35 => 'w-[35%]', 40 => 'w-[40%]', 45 => 'w-[45%]',
                50 => 'w-[50%]', 55 => 'w-[55%]', 60 => 'w-[60%]', 65 => 'w-[65%]', 70 => 'w-[70%]',
                75 => 'w-[75%]', 80 => 'w-[80%]', 85 => 'w-[85%]', 90 => 'w-[90%]', 95 => 'w-[95%]',
                100 => 'w-full',
            ][$percent];
        };
    @endphp

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
                @foreach ($monthlyUserData as $entry)
                    <div class="bar-row">
                        <div class="flex justify-between gap-3.5">
                            <strong>{{ $entry['month'] }}</strong>
                            <span class="text-[var(--text-muted)]">{{ $entry['students'] }} students | {{ $entry['admins'] }} admins</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill {{ $widthClass((int) $entry['students'], 1230) }}"></div>
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
                            <span class="color-dot {{ $dotColorClasses[$entry['color']] ?? 'bg-[#0F4C75]' }}"></span>
                            <strong>{{ $entry['name'] }}</strong>
                        </div>
                        <span class="text-[var(--text-muted)]">{{ $entry['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="content-card report-panel mt-6">
        <h3>Platform Activity</h3>
        <div class="activity-list">
            @foreach ($activityData as $item)
                <div class="activity-row">
                    <strong>{{ $item['category'] }}</strong>
                    <span class="text-[1.4rem] font-extrabold text-[#2f5540]">{{ $item['count'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endsection
