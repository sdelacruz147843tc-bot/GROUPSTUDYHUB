@extends('studyhub.student.layout')

@section('title', 'Study Sessions')

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
            <h3 class="calendar-title">Planner view for study sessions.</h3>
            <p class="calendar-subtitle">Review upcoming sessions and RSVP quickly.</p>
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

