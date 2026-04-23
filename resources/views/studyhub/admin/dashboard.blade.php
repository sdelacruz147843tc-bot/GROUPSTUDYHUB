@extends('studyhub.admin.layout')

@section('title', 'Admin Dashboard')

@section('page')
    @php
        $statColorClasses = [
            '#0F4C75' => 'bg-[#0F4C75]/20 text-[#0F4C75]',
            '#3282B8' => 'bg-[#3282B8]/20 text-[#3282B8]',
            '#06D6A0' => 'bg-[#06D6A0]/20 text-[#06D6A0]',
            '#FF6B35' => 'bg-[#FF6B35]/20 text-[#FF6B35]',
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

    <h2 class="page-title">Admin Dashboard</h2>
    <p class="page-subtitle">Monitor and manage the StudyHub platform.</p>

    <section class="stats-grid">
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
                @foreach ($userActivityData as $entry)
                    <div class="chart-row">
                        <div class="flex justify-between gap-3.5">
                            <strong>{{ $entry['month'] }}</strong>
                            <span class="muted">{{ $entry['users'] }} users</span>
                        </div>
                        <div class="chart-bar-track">
                            <div class="chart-bar-fill {{ $widthClass((int) $entry['users'], 1248) }}"></div>
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
                        <div class="mb-2 flex items-center gap-2.5">
                            <span class="icon-box {{ $alert['type'] === 'warning' ? 'text-[#e76f51]' : 'text-[#2a9d8f]' }}">
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
