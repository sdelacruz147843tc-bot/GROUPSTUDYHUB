@extends('studyhub.student.layout')

@section('title', 'Student Dashboard')

@section('page')
    @php
        $displayName = $studentProfile['display_name'] ?? 'Student';
        $firstName = trim(explode(' ', $displayName)[0] ?? 'Student') ?: 'Student';
        $activityTotal = collect($weeklyActivity ?? [])->sum('count');
    @endphp

    <div class="student-dashboard-page">
        <header class="dashboard-topbar">
            <form class="dashboard-search-form" action="{{ route('studyhub.student.dashboard') }}" method="GET">
                <label class="dashboard-search" for="dashboard-search-input">
                    <span class="icon-box">{!! $icons['search'] !!}</span>
                    <input id="dashboard-search-input" name="q" type="search" value="{{ $search ?? '' }}" placeholder="Search resources, groups, and more...">
                </label>
            </form>

            <span class="dashboard-topbar-spacer" aria-hidden="true"></span>
        </header>

        @if (! empty($search))
            <section class="dashboard-search-results">
                <div class="dashboard-card-header">
                    <div>
                        <h3>Search Results</h3>
                        <span>{{ count($searchResults) }} matches for "{{ $search }}"</span>
                    </div>
                    <a href="{{ route('studyhub.student.dashboard') }}">Clear</a>
                </div>

                <div class="dashboard-result-list">
                    @forelse ($searchResults as $result)
                        <a class="dashboard-result-row" href="{{ $result['url'] }}">
                            <span>{{ $result['type'] }}</span>
                            <strong>{{ $result['title'] }}</strong>
                            <small>{{ $result['meta'] }}</small>
                        </a>
                    @empty
                        <div class="dashboard-empty app-empty-state compact">
                            <span class="app-empty-icon">{!! $icons['search'] !!}</span>
                            <strong>No matches found</strong>
                            <span>Try a group name, resource title, category, or discussion keyword.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif

        <section class="dashboard-hero">
            <div class="dashboard-hero-copy">
                <span class="dashboard-eyebrow">Keep learning, keep sharing.</span>
                <h2>Welcome back, {{ $firstName }}</h2>
                <p>Pick up your study flow, check updates, and jump back into your groups.</p>
                <div class="dashboard-hero-actions">
                    <a class="dashboard-primary-action" href="{{ route('studyhub.student.resources') }}">
                        <span class="icon-box">{!! $icons['plus'] !!}</span>
                        Upload Resource
                    </a>
                    <a class="dashboard-secondary-action" href="{{ route('studyhub.student.groups') }}">
                        Create Group
                    </a>
                </div>
            </div>

            <div class="dashboard-hero-visual" aria-hidden="true">
                <span class="hero-stat-pill">{{ $stats[1]['value'] ?? '0' }} resources</span>
                <img src="{{ asset('images/login.png') }}" alt="">
            </div>
        </section>

        <section class="stats-grid dashboard-stats-grid" aria-label="Dashboard stats">
            @foreach ($stats as $stat)
                <article class="stat-card dashboard-stat-card">
                    <div class="stat-top">
                        <span class="stat-kicker">{{ $stat['label'] }}</span>
                        <div class="icon-box stat-icon">
                            {!! $icons[$stat['icon']] !!}
                        </div>
                    </div>
                    <div class="stat-copy">
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="stat-label">
                            <span>+{{ max(1, (int) $stat['value']) * 3 }}%</span>
                            this week
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="dashboard-main-grid">
            <article class="content-card dashboard-chart-card">
                <div class="dashboard-card-header">
                    <div>
                        <h3>Your Activity</h3>
                        <span>This Week - {{ $activityTotal }} updates</span>
                    </div>
                    <a href="{{ route('studyhub.student.resources') }}">View all</a>
                </div>

                <div class="activity-chart" aria-hidden="true">
                    <div class="chart-grid-lines"></div>
                    <div class="chart-bars">
                        @foreach ($weeklyActivity as $index => $day)
                            <span title="{{ $day['count'] }} updates on {{ $day['label'] }}" style="--bar-height: {{ $day['height'] }}%; --bar-delay: {{ $index * 35 }}ms;"></span>
                        @endforeach
                    </div>
                </div>

                <div class="chart-labels">
                    @foreach ($weeklyActivity as $day)
                        <span>{{ $day['label'] }}</span>
                    @endforeach
                </div>
            </article>

            <aside class="dashboard-side-column">
                <article class="content-card dashboard-side-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h3>Upcoming Sessions</h3>
                            <span>{{ count($upcomingSessions) }} scheduled</span>
                        </div>
                        <a href="{{ route('studyhub.student.sessions') }}">View all</a>
                    </div>

                    <div class="dashboard-session-list">
                        @forelse ($upcomingSessions as $session)
                            <div class="dashboard-session-row">
                                <span class="session-row-icon">{!! $icons['calendar'] !!}</span>
                                <span>
                                    <strong>{{ $session['title'] }}</strong>
                                    <small>{{ $session['date'] }} - {{ $session['time'] }}</small>
                                </span>
                            </div>
                        @empty
                            <div class="dashboard-empty app-empty-state compact">
                                <span class="app-empty-icon">{!! $icons['calendar'] !!}</span>
                                <strong>No sessions yet</strong>
                                <span>New schedules from your groups will appear here.</span>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="content-card dashboard-side-card dashboard-groups-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h3>Study Groups</h3>
                            <span>{{ count($groups) }} active</span>
                        </div>
                        <a href="{{ route('studyhub.student.groups') }}">View all</a>
                    </div>

                    <div class="dashboard-group-list">
                        @forelse ($groups as $group)
                            <a class="dashboard-group-row" href="{{ route('studyhub.student.group.show', $group['id']) }}">
                                <span class="group-badge">{{ $group['initial'] }}</span>
                                <span>
                                    <strong>{{ $group['name'] }}</strong>
                                    <small>Open workspace</small>
                                </span>
                            </a>
                        @empty
                            <div class="dashboard-empty app-empty-state compact">
                                <span class="app-empty-icon">{!! $icons['users'] !!}</span>
                                <strong>No groups yet</strong>
                                <span>Join or create a study group to see it here.</span>
                            </div>
                        @endforelse
                    </div>
                </article>
            </aside>
        </section>

        <section class="content-card activity-card dashboard-activity-feed">
            <div class="dashboard-card-header">
                <div>
                    <h3>Recent Activity</h3>
                    <span>Latest movement across your workspace</span>
                </div>
            </div>
            <div class="activity-list">
                @forelse ($recentActivity as $activity)
                    <div class="activity-row">
                        <span class="activity-dot" aria-hidden="true"></span>
                        <div class="activity-content">
                            <p class="activity-text">
                                <strong>{{ $activity['actor'] }}</strong> {{ $activity['action'] }}
                                <span class="activity-group">{{ $activity['group'] }}</span>
                            </p>
                            <span class="activity-time">{{ $activity['time'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="app-empty-state">
                        <span class="app-empty-icon">{!! $icons['trend'] !!}</span>
                        <strong>No recent activity</strong>
                        <span>Your uploads, replies, and session updates will show up here.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        (function () {
            const shell = document.querySelector('.student-shell');

            if (shell) {
                shell.classList.add('student-dashboard-home');
            }
        })();
    </script>
@endsection
