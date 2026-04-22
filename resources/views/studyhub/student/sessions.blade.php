@extends('studyhub.student.layout')

@section('title', 'Study Sessions')

@push('page-styles')
    <style>
        .section-title {
            margin: 0 0 16px;
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #183425;
        }

        .sessions-cta {
            min-height: 64px;
            padding: 0 34px;
            border-radius: 18px;
            font-size: 1.05rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
        }

        .sessions-status,
        .sessions-errors {
            margin: 0 0 18px;
            padding: 13px 15px;
            border-radius: 16px;
            font-size: 0.94rem;
        }

        .sessions-status {
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
        }

        .sessions-errors {
            border: 1px solid rgba(219, 137, 120, 0.22);
            background: rgba(255, 244, 240, 0.92);
            color: #8a3f32;
        }

        .sessions-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .session-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .session-card {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
        }

        .session-topline {
            height: 10px;
        }

        .session-body {
            padding: 22px;
        }

        .session-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .session-body h3 {
            margin: 0 0 8px;
            font-size: 1.34rem;
            letter-spacing: -0.03em;
            color: #173223;
        }

        .session-group {
            color: var(--student-accent);
            font-weight: 700;
            margin-bottom: 16px;
        }

        .session-meta {
            display: grid;
            gap: 12px;
            color: #6f7f74;
            margin-bottom: 20px;
        }

        .session-meta-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .session-actions {
            display: flex;
            gap: 10px;
        }

        .session-actions form,
        .session-actions button,
        .session-actions a {
            flex: 1;
        }

        .session-actions form {
            margin: 0;
        }

        .session-primary,
        .session-secondary {
            width: 100%;
            min-height: 44px;
            border-radius: 14px;
            font-size: 0.92rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .session-primary {
            border: 0;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            cursor: pointer;
        }

        .session-primary.joined {
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
            border: 1px solid color-mix(in srgb, var(--student-accent) 22%, white 78%);
            cursor: default;
        }

        .session-secondary {
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: rgba(255,255,255,0.94);
            color: #34493b;
            cursor: pointer;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--student-accent-pale) 54%, white 46%);
            color: var(--student-accent-text);
            font-size: 0.86rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .session-note {
            margin: 0 0 18px;
            padding: 15px 16px;
            border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 42%, white 58%) 0%, rgba(255,255,255,0.94) 100%);
            color: #556a5c;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .calendar-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
            gap: 22px;
            align-items: center;
            padding: 32px;
            margin-bottom: 28px;
            border-radius: 24px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 34%),
                linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(246, 251, 247, 0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
        }

        .calendar-card::after {
            content: '';
            position: absolute;
            right: -40px;
            top: -48px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: color-mix(in srgb, var(--student-accent-pale) 72%, white 28%);
            opacity: 0.65;
            pointer-events: none;
        }

        .calendar-copy,
        .calendar-aside {
            position: relative;
            z-index: 1;
        }

        .calendar-copy {
            display: grid;
            gap: 14px;
            text-align: left;
        }

        .calendar-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--student-accent-pale) 66%, white 34%);
            border: 1px solid color-mix(in srgb, var(--student-accent) 16%, white 84%);
            color: var(--student-accent-text);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .calendar-kicker .icon-box {
            width: 16px;
            height: 16px;
            color: var(--student-accent);
        }

        .calendar-title {
            margin: 0;
            font-size: clamp(2rem, 3vw, 2.7rem);
            line-height: 0.96;
            letter-spacing: -0.05em;
            font-weight: 800;
            color: #163223;
        }

        .calendar-subtitle {
            margin: 0;
            max-width: 640px;
            color: #617266;
            font-size: 1.02rem;
            line-height: 1.65;
        }

        .calendar-highlights {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .calendar-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.84);
            border: 1px solid color-mix(in srgb, var(--student-accent) 12%, white 88%);
            color: #385042;
            font-size: 0.86rem;
            font-weight: 700;
            box-shadow: 0 12px 20px rgba(80, 111, 95, 0.06);
        }

        .calendar-pill .icon-box {
            width: 15px;
            height: 15px;
            color: var(--student-accent);
        }

        .calendar-aside {
            display: grid;
            gap: 14px;
            justify-items: stretch;
        }

        .calendar-stat {
            padding: 18px 18px 16px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.94) 0%, rgba(245,250,246,0.94) 100%);
            border: 1px solid color-mix(in srgb, var(--student-accent) 12%, white 88%);
            box-shadow: 0 18px 30px rgba(80, 111, 95, 0.08);
            text-align: left;
        }

        .calendar-stat-label {
            margin: 0 0 6px;
            color: #6d7b72;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .calendar-stat-value {
            margin: 0;
            color: #163223;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .calendar-card .action-button {
            min-height: 56px;
            border-radius: 18px;
            justify-content: center;
            gap: 10px;
            font-size: 0.98rem;
            font-weight: 800;
            box-shadow: 0 18px 30px color-mix(in srgb, var(--student-accent) 22%, transparent 78%);
        }

        .calendar-card .action-button .icon-box {
            width: 18px;
            height: 18px;
        }

        .past-list {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
        }

        .past-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 20px 22px;
            border-bottom: 1px solid rgba(217, 229, 221, 0.9);
        }

        .past-row:last-child {
            border-bottom: 0;
        }

        .past-row h3 {
            margin: 0 0 6px;
            font-size: 1.1rem;
            color: #173223;
        }

        .past-row p {
            margin: 0;
            color: #6f7f74;
        }

        .session-empty {
            padding: 26px 22px;
            border-radius: 20px;
            border: 1px dashed color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 42%, white 58%);
            color: var(--student-accent-text);
            text-align: center;
            font-weight: 600;
        }

        .sessions-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 28px;
            z-index: 40;
        }

        .sessions-modal.is-open {
            display: flex;
        }

        .sessions-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 22, 17, 0.48);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .sessions-panel,
        .sessions-details-panel {
            position: relative;
            z-index: 1;
            width: min(680px, 100%);
            max-height: calc(100vh - 44px);
            overflow: auto;
            border-radius: 28px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 34%),
                linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 40%, white 60%) 0%, rgba(255,255,255,0.98) 100%);
            box-shadow: 0 28px 56px color-mix(in srgb, var(--student-accent-text) 18%, transparent 82%);
        }

        .sessions-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 24px 16px;
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 72%, white 28%) 0%, color-mix(in srgb, var(--student-accent-pale) 68%, white 32%) 100%);
        }

        .sessions-title {
            margin: 0 0 6px;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #183425;
        }

        .sessions-copy {
            margin: 0;
            color: color-mix(in srgb, var(--student-accent-text) 72%, white 28%);
            font-size: 0.95rem;
        }

        .sessions-close {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: color-mix(in srgb, var(--student-accent-pale) 54%, white 46%);
            color: var(--student-accent-text);
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }

        .sessions-body {
            padding: 20px 24px 24px;
        }

        .sessions-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .sessions-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 46%, white 54%) 0%, color-mix(in srgb, var(--student-accent-pale) 20%, white 80%) 100%);
        }

        .sessions-field-full {
            grid-column: 1 / -1;
        }

        .sessions-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: #244231;
        }

        .sessions-input,
        .sessions-select {
            width: 100%;
            height: 54px;
            padding: 0 16px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.92);
            color: #1f3528;
            font: inherit;
        }

        .sessions-actions-row {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }

        .sessions-submit {
            min-height: 54px;
            min-width: 190px;
            padding: 0 24px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.98rem;
            font-weight: 800;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            cursor: pointer;
        }

        .sessions-detail-grid {
            display: grid;
            gap: 14px;
        }

        .sessions-detail-card {
            padding: 16px;
            border-radius: 20px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 42%, white 58%) 0%, rgba(255,255,255,0.94) 100%);
        }

        .sessions-detail-card strong {
            display: block;
            margin-bottom: 6px;
            color: #183425;
        }

        .sessions-detail-card span {
            color: #5f7267;
            line-height: 1.55;
        }

        @media (max-width: 900px) {
            .session-grid,
            .sessions-form {
                grid-template-columns: 1fr;
            }

            .calendar-card {
                grid-template-columns: 1fr;
                padding: 24px 20px;
            }

            .past-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('page')
    @php
        $showScheduleModal = $errors->has('title') || $errors->has('group_id') || $errors->has('date') || $errors->has('start_time') || $errors->has('end_time') || $errors->has('location') || $errors->has('type') || $errors->has('max_attendees');
    @endphp

    <div class="toolbar">
        <div>
            <h2 class="page-title">Study Sessions</h2>
            <p class="page-subtitle">Schedule and manage your study sessions.</p>
        </div>
        <div class="toolbar-actions">
            <button class="action-button sessions-cta" type="button" data-session-open>
                <span class="icon-box">{!! $icons['plus'] !!}</span>
                <span>Schedule Session</span>
            </button>
        </div>
    </div>

    <h3 class="section-title">Upcoming Sessions</h3>
    <section class="session-grid">
        @forelse ($upcomingSessions as $session)
            @php
                $isJoined = in_array($studentProfile['display_name'], $session['attendee_names'] ?? [], true);
                $isFull = (int) $session['attendees'] >= (int) $session['max_attendees'];
            @endphp
            <article class="content-card session-card">
                <div class="session-topline" style="background: {{ $session['status'] === 'confirmed' ? 'linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%)' : 'linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 70%, #f6b066 30%) 0%, color-mix(in srgb, var(--student-accent) 45%, #d98045 55%) 100%)' }}"></div>
                <div class="session-body">
                    <div class="session-header">
                        <div>
                            <h3>{{ $session['title'] }}</h3>
                            <div class="session-group">{{ $session['group'] }}</div>
                        </div>
                        @if ($session['type'] === 'online')
                            <span class="tag">
                                <span class="icon-box">{!! $icons['video'] !!}</span>
                                <span>Online</span>
                            </span>
                        @endif
                    </div>

                    <div class="session-meta">
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['calendar'] !!}</span><span>{{ $session['date'] }}</span></div>
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['clock'] !!}</span><span>{{ $session['time'] }}</span></div>
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['map-pin'] !!}</span><span>{{ $session['location'] }}</span></div>
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['users'] !!}</span><span>{{ $session['attendees'] }} / {{ $session['max_attendees'] }} attendees</span></div>
                    </div>

                    @if (! empty($session['notes']))
                        <p class="session-note">{{ $session['notes'] }}</p>
                    @endif

                    <div class="session-actions">
                        @if ($isJoined)
                            <span class="session-primary joined">{{ $session['type'] === 'online' ? 'Joined' : 'RSVPd' }}</span>
                        @else
                            <form method="POST" action="{{ route('studyhub.student.sessions.rsvp', $session['id']) }}">
                                @csrf
                                <button class="session-primary" type="submit" @if ($isFull) disabled @endif>{{ $isFull ? 'Full' : ($session['type'] === 'online' ? 'Join Session' : 'RSVP') }}</button>
                            </form>
                        @endif
                        <button
                            class="session-secondary"
                            type="button"
                            data-session-details
                            data-session-title="{{ $session['title'] }}"
                            data-session-group="{{ $session['group'] }}"
                            data-session-date="{{ $session['date'] }}"
                            data-session-time="{{ $session['time'] }}"
                            data-session-location="{{ $session['location'] }}"
                            data-session-type="{{ ucfirst($session['type']) }}"
                            data-session-attendees="{{ $session['attendees'] }} / {{ $session['max_attendees'] }}"
                            data-session-host="{{ $session['created_by'] ?? 'StudyHub Member' }}"
                            data-session-notes="{{ $session['notes'] ?? 'No extra notes yet.' }}"
                        >Details</button>
                    </div>
                </div>
            </article>
        @empty
            <div class="session-empty">No upcoming sessions yet. Schedule one to get started.</div>
        @endforelse
    </section>

    <section class="content-card calendar-card">
        <div class="calendar-copy">
            <span class="calendar-kicker">
                <span class="icon-box">{!! $icons['calendar'] !!}</span>
                <span>Planner View</span>
            </span>
            <h3 class="calendar-title">Calendar view, cleaned up for real planning.</h3>
            <p class="calendar-subtitle">Your sessions are already live in the planner. Review upcoming study blocks above, RSVP quickly, and keep momentum going with a polished scheduling flow.</p>
            <div class="calendar-highlights">
                <span class="calendar-pill">
                    <span class="icon-box">{!! $icons['clock'] !!}</span>
                    <span>Fast schedule review</span>
                </span>
                <span class="calendar-pill">
                    <span class="icon-box">{!! $icons['users'] !!}</span>
                    <span>Built for group coordination</span>
                </span>
                <span class="calendar-pill">
                    <span class="icon-box">{!! $icons['bell'] !!}</span>
                    <span>Ready for your next session</span>
                </span>
            </div>
        </div>

        <div class="calendar-aside">
            <div class="calendar-stat">
                <p class="calendar-stat-label">Session Planner</p>
                <p class="calendar-stat-value">{{ count($upcomingSessions) }} upcoming blocks</p>
            </div>
            <button class="action-button" type="button" data-session-open>
                <span class="icon-box">{!! $icons['plus'] !!}</span>
                <span>Schedule Another</span>
            </button>
        </div>
    </section>

    <h3 class="section-title">Past Sessions</h3>
    <section class="content-card past-list">
        @forelse ($pastSessions as $session)
            <article class="past-row">
                <div>
                    <h3>{{ $session['title'] }}</h3>
                    <p><strong style="color:var(--student-accent);">{{ $session['group'] }}</strong> | {{ $session['date'] }} | {{ $session['time'] }} | {{ $session['attendees'] }} attendees</p>
                </div>
                <button
                    class="secondary-button"
                    type="button"
                    data-session-details
                    data-session-title="{{ $session['title'] }}"
                    data-session-group="{{ $session['group'] }}"
                    data-session-date="{{ $session['date'] }}"
                    data-session-time="{{ $session['time'] }}"
                    data-session-location="{{ $session['location'] ?? 'Completed session' }}"
                    data-session-type="{{ ucfirst($session['type'] ?? 'In-person') }}"
                    data-session-attendees="{{ $session['attendees'] }} attendees"
                    data-session-host="{{ $session['created_by'] ?? 'StudyHub Member' }}"
                    data-session-notes="{{ $session['notes'] ?? 'Session completed successfully.' }}"
                >View Notes</button>
            </article>
        @empty
            <div class="session-empty">No past sessions yet.</div>
        @endforelse
    </section>

    <div class="sessions-modal @if ($showScheduleModal) is-open @endif" data-sessions-modal>
        <button class="sessions-backdrop" type="button" aria-label="Close schedule session form" data-sessions-close></button>
        <div class="sessions-panel">
            <div class="sessions-header">
                <div>
                    <h3 class="sessions-title">Schedule session</h3>
                    <p class="sessions-copy">Plan a new study block for one of your joined groups.</p>
                </div>
                <button class="sessions-close" type="button" aria-label="Close schedule session form" data-sessions-close>&times;</button>
            </div>

            <div class="sessions-body">
                @if ($showScheduleModal)
                    <div class="sessions-errors">
                        Please fix the following:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="sessions-form" method="POST" action="{{ route('studyhub.student.sessions.store') }}">
                    @csrf
                    <label class="sessions-field sessions-field-full">
                        <span class="sessions-label">Title</span>
                        <input class="sessions-input" type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Algorithms Review Session" required>
                    </label>

                    <label class="sessions-field">
                        <span class="sessions-label">Study group</span>
                        <select class="sessions-select" name="group_id" required>
                            <option value="">Choose group</option>
                            @foreach ($sessionGroups as $group)
                                <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="sessions-field">
                        <span class="sessions-label">Type</span>
                        <select class="sessions-select" name="type" required>
                            <option value="">Choose type</option>
                            <option value="in-person" @selected(old('type') === 'in-person')>In person</option>
                            <option value="online" @selected(old('type') === 'online')>Online</option>
                        </select>
                    </label>

                    <label class="sessions-field">
                        <span class="sessions-label">Date</span>
                        <input class="sessions-input" type="date" name="date" value="{{ old('date') }}" required>
                    </label>

                    <label class="sessions-field">
                        <span class="sessions-label">Location</span>
                        <input class="sessions-input" type="text" name="location" maxlength="120" value="{{ old('location') }}" placeholder="Library Room 204 or Zoom" required>
                    </label>

                    <label class="sessions-field">
                        <span class="sessions-label">Start time</span>
                        <input class="sessions-input" type="time" name="start_time" value="{{ old('start_time') }}" required>
                    </label>

                    <label class="sessions-field">
                        <span class="sessions-label">End time</span>
                        <input class="sessions-input" type="time" name="end_time" value="{{ old('end_time') }}" required>
                    </label>

                    <label class="sessions-field sessions-field-full">
                        <span class="sessions-label">Max attendees</span>
                        <input class="sessions-input" type="number" name="max_attendees" min="2" max="100" value="{{ old('max_attendees', 12) }}" required>
                    </label>

                    <div class="sessions-actions-row">
                        <button class="sessions-submit" type="submit">Create Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="sessions-modal" data-session-details-modal>
        <button class="sessions-backdrop" type="button" aria-label="Close session details" data-session-details-close></button>
        <div class="sessions-details-panel">
            <div class="sessions-header">
                <div>
                    <h3 class="sessions-title" data-details-title>Session Details</h3>
                    <p class="sessions-copy" data-details-group></p>
                </div>
                <button class="sessions-close" type="button" aria-label="Close session details" data-session-details-close>&times;</button>
            </div>

            <div class="sessions-body">
                <div class="sessions-detail-grid">
                    <div class="sessions-detail-card">
                        <strong>Date & Time</strong>
                        <span><span data-details-date></span> | <span data-details-time></span></span>
                    </div>
                    <div class="sessions-detail-card">
                        <strong>Location</strong>
                        <span data-details-location></span>
                    </div>
                    <div class="sessions-detail-card">
                        <strong>Session Type</strong>
                        <span data-details-type></span>
                    </div>
                    <div class="sessions-detail-card">
                        <strong>Attendees</strong>
                        <span data-details-attendees></span>
                    </div>
                    <div class="sessions-detail-card">
                        <strong>Host</strong>
                        <span data-details-host></span>
                    </div>
                    <div class="sessions-detail-card">
                        <strong>Notes</strong>
                        <span data-details-notes></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scheduleModal = document.querySelector('[data-sessions-modal]');
            const scheduleOpenButtons = document.querySelectorAll('[data-session-open]');
            const scheduleCloseButtons = document.querySelectorAll('[data-sessions-close]');
            const detailsModal = document.querySelector('[data-session-details-modal]');
            const detailsOpenButtons = document.querySelectorAll('[data-session-details]');
            const detailsCloseButtons = document.querySelectorAll('[data-session-details-close]');

            const setModalState = function (modal, isOpen) {
                if (! modal) {
                    return;
                }

                modal.classList.toggle('is-open', isOpen);
                document.body.style.overflow = (scheduleModal?.classList.contains('is-open') || detailsModal?.classList.contains('is-open')) ? 'hidden' : '';
            };

            scheduleOpenButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setModalState(scheduleModal, true);
                });
            });

            scheduleCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setModalState(scheduleModal, false);
                });
            });

            detailsOpenButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    detailsModal.querySelector('[data-details-title]').textContent = button.dataset.sessionTitle || 'Session Details';
                    detailsModal.querySelector('[data-details-group]').textContent = button.dataset.sessionGroup || '';
                    detailsModal.querySelector('[data-details-date]').textContent = button.dataset.sessionDate || '';
                    detailsModal.querySelector('[data-details-time]').textContent = button.dataset.sessionTime || '';
                    detailsModal.querySelector('[data-details-location]').textContent = button.dataset.sessionLocation || '';
                    detailsModal.querySelector('[data-details-type]').textContent = button.dataset.sessionType || '';
                    detailsModal.querySelector('[data-details-attendees]').textContent = button.dataset.sessionAttendees || '';
                    detailsModal.querySelector('[data-details-host]').textContent = button.dataset.sessionHost || '';
                    detailsModal.querySelector('[data-details-notes]').textContent = button.dataset.sessionNotes || '';
                    setModalState(detailsModal, true);
                });
            });

            detailsCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setModalState(detailsModal, false);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setModalState(scheduleModal, false);
                    setModalState(detailsModal, false);
                }
            });

            if (scheduleModal && scheduleModal.classList.contains('is-open')) {
                document.body.style.overflow = 'hidden';
            }
        });
    </script>
@endsection
