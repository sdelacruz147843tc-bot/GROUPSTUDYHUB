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
                <span>Create Session</span>
            </button>
        </div>
    </div>

    <section class="sessions-stat-grid">
        @foreach ($sessionStats as $stat)
            <article class="content-card sessions-stat-card">
                <span class="icon-box sessions-stat-icon">{!! $icons[$stat['icon']] !!}</span>
                <div>
                    <span class="sessions-stat-label">{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </div>
                <span class="sessions-stat-watermark icon-box">{!! $icons[$stat['icon']] !!}</span>
            </article>
        @endforeach
    </section>

    <h3 class="section-title">Upcoming Sessions</h3>
    <section class="session-grid">
        @forelse ($upcomingSessions as $session)
            @php
                $isJoined = in_array($studentProfile['display_name'], $session['attendee_names'] ?? [], true);
                $isFull = (int) $session['attendees'] >= (int) $session['max_attendees'];
                $attendeeProgress = min(100, (int) round(((int) $session['attendees'] / max((int) $session['max_attendees'], 1)) * 100));
            @endphp
            <article class="content-card session-card">
                <span class="session-card-accent" aria-hidden="true"></span>
                <div class="session-body">
                    <div class="session-header">
                        <div class="session-title-lockup">
                            <span class="session-subject-icon">{{ Str::of($session['group'])->substr(0, 2)->upper() }}</span>
                            <div>
                                <h3>{{ $session['title'] }}</h3>
                                <div class="session-group">{{ $session['group'] }}</div>
                            </div>
                        </div>
                        <div class="session-card-badges">
                            <span class="session-date-pill">
                                <span class="icon-box">{!! $icons['calendar'] !!}</span>
                                <span>{{ $session['date'] }}</span>
                            </span>
                            @if ($session['type'] === 'online')
                                <span class="tag">
                                    <span class="icon-box">{!! $icons['video'] !!}</span>
                                    <span>Online</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="session-meta">
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['clock'] !!}</span><span>{{ $session['time'] }}</span></div>
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['map-pin'] !!}</span><span>{{ $session['location'] }}</span></div>
                        <div class="session-meta-row"><span class="icon-box">{!! $icons['users'] !!}</span><span>{{ $session['attendees'] }} / {{ $session['max_attendees'] }} attendees</span></div>
                        <div class="session-progress" aria-label="{{ $attendeeProgress }}% capacity">
                            <span style="width: {{ $attendeeProgress }}%"></span>
                        </div>
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
                                <button class="session-primary" type="submit" data-loading-label="{{ $session['type'] === 'online' ? 'Joining...' : 'Saving RSVP...' }}" @if ($isFull) disabled @endif>{{ $isFull ? 'Full' : ($session['type'] === 'online' ? 'Join Session' : 'RSVP') }}</button>
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
                            data-session-meeting-url="{{ $session['meeting_url'] ?? '' }}"
                            data-session-type="{{ ucfirst($session['type']) }}"
                            data-session-attendees="{{ $session['attendees'] }} / {{ $session['max_attendees'] }}"
                            data-session-host="{{ $session['created_by'] ?? 'StudyHub Member' }}"
                            data-session-notes="{{ $session['notes'] ?? 'No extra notes yet.' }}"
                        >Details</button>
                    </div>
                </div>
            </article>
        @empty
            <div class="session-empty app-empty-state">
                <span class="app-empty-icon">{!! $icons['calendar'] !!}</span>
                <strong>No upcoming sessions</strong>
                <span>Plan a study block for one of your groups.</span>
                <button class="app-empty-action" type="button" data-session-open>Create session</button>
            </div>
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
                <span>Create Session</span>
            </button>
        </div>
    </section>

    <h3 class="section-title">Past Sessions</h3>
    <section class="content-card past-list">
        @forelse ($pastSessions as $session)
            <article class="past-row">
                <span class="past-session-icon icon-box">{!! $icons[$session['type'] === 'online' ? 'video' : 'calendar'] !!}</span>
                <div>
                    <h3>{{ $session['title'] }}</h3>
                    <p><strong class="text-[var(--student-accent)]">{{ $session['group'] }}</strong> | {{ $session['date'] }} | {{ $session['time'] }} | {{ $session['attendees'] }} attendees</p>
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
                    data-session-meeting-url="{{ $session['meeting_url'] ?? '' }}"
                    data-session-type="{{ ucfirst($session['type'] ?? 'In-person') }}"
                    data-session-attendees="{{ $session['attendees'] }} attendees"
                    data-session-host="{{ $session['created_by'] ?? 'StudyHub Member' }}"
                    data-session-notes="{{ $session['notes'] ?? 'Session completed successfully.' }}"
                >View Notes</button>
            </article>
        @empty
            <div class="session-empty app-empty-state compact">
                <span class="app-empty-icon">{!! $icons['clock'] !!}</span>
                <strong>No past sessions</strong>
                <span>Completed sessions will appear here.</span>
            </div>
        @endforelse
    </section>

    @if (isset($sessionsPaginator) && method_exists($sessionsPaginator, 'links'))
        <div class="mt-6">
            {{ $sessionsPaginator->links() }}
        </div>
    @endif

    <x-studyhub.modal
        title="Create session"
        subtitle="Plan a new study block for one of your joined groups."
        close-data="data-sessions-close"
        :open="$showScheduleModal"
        size="lg"
        data-sessions-modal
    >
                @if ($showScheduleModal)
                    <div class="sessions-errors" role="alert" aria-live="polite">
                        <strong>Session was not scheduled</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="grid grid-cols-1 gap-3 md:grid-cols-2" method="POST" action="{{ route('studyhub.student.sessions.store') }}">
                    @csrf
                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2">
                        <span class="text-sm font-extrabold text-[#244231]">Title</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Algorithms Review Session" required>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Study group</span>
                        <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="group_id" required>
                            <option value="">Choose group</option>
                            @foreach ($sessionGroups as $group)
                                <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Type</span>
                        <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="type" required data-session-type-input>
                            <option value="">Choose type</option>
                            <option value="in-person" @selected(old('type') === 'in-person')>In person</option>
                            <option value="online" @selected(old('type') === 'online')>Online</option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Date</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="date" name="date" value="{{ old('date') }}" required>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]" data-session-location-label>Location</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="location" maxlength="255" value="{{ old('location') }}" placeholder="Library Room 204" required data-session-location-input>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Start time</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="time" name="start_time" value="{{ old('start_time') }}" required>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">End time</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="time" name="end_time" value="{{ old('end_time') }}" required>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2">
                        <span class="text-sm font-extrabold text-[#244231]">Max attendees</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="number" name="max_attendees" min="2" max="100" value="{{ old('max_attendees', 12) }}" required>
                    </label>

                    <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6 md:col-span-2">
                        <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Scheduling...">Create Session</button>
                    </div>
                </form>
    </x-studyhub.modal>

    <x-studyhub.modal
        title="Session Details"
        close-data="data-session-details-close"
        data-session-details-modal
    >
                <span class="sr-only" data-details-title>Session Details</span>
                <p class="mb-4 text-sm font-semibold text-[#5f776b]" data-details-group></p>
                <div class="sessions-detail-grid">
                    <div class="sessions-detail-card">
                        <strong>Date & Time</strong>
                        <span><span data-details-date></span> | <span data-details-time></span></span>
                    </div>
                    <div class="sessions-detail-card">
                        <strong data-details-location-label>Location</strong>
                        <span data-details-location></span>
                        <a class="meeting-link-button" href="#" target="_blank" rel="noopener noreferrer" data-details-meeting-link hidden>Open meeting</a>
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
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scheduleModal = document.querySelector('[data-sessions-modal]');
            const scheduleOpenButtons = document.querySelectorAll('[data-session-open]');
            const scheduleCloseButtons = document.querySelectorAll('[data-sessions-close]');
            const detailsModal = document.querySelector('[data-session-details-modal]');
            const detailsOpenButtons = document.querySelectorAll('[data-session-details]');
            const detailsCloseButtons = document.querySelectorAll('[data-session-details-close]');
            const sessionTypeInput = document.querySelector('[data-session-type-input]');
            const sessionLocationLabel = document.querySelector('[data-session-location-label]');
            const sessionLocationInput = document.querySelector('[data-session-location-input]');

            const setModalState = function (modal, isOpen) {
                if (! modal) {
                    return;
                }

                modal.classList.toggle('is-open', isOpen);
                document.body.classList.toggle('overflow-hidden', scheduleModal?.classList.contains('is-open') || detailsModal?.classList.contains('is-open'));
            };

            const syncSessionLocationField = function () {
                const isOnline = sessionTypeInput?.value === 'online';

                if (sessionLocationLabel) {
                    sessionLocationLabel.textContent = isOnline ? 'Meeting link' : 'Location';
                }

                if (sessionLocationInput) {
                    sessionLocationInput.type = isOnline ? 'url' : 'text';
                    sessionLocationInput.placeholder = isOnline ? 'https://meet.google.com/abc-defg-hij' : 'Library Room 204';
                }
            };

            scheduleOpenButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    syncSessionLocationField();
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
                    const meetingUrl = button.dataset.sessionMeetingUrl || '';
                    const isOnline = (button.dataset.sessionType || '').toLowerCase() === 'online';
                    const meetingLink = detailsModal.querySelector('[data-details-meeting-link]');
                    detailsModal.querySelector('[data-details-location-label]').textContent = isOnline ? 'Meeting link' : 'Location';
                    detailsModal.querySelector('[data-details-location]').textContent = button.dataset.sessionLocation || '';
                    if (meetingLink) {
                        meetingLink.hidden = ! isOnline || ! meetingUrl;
                        meetingLink.href = meetingUrl || '#';
                    }
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
                document.body.classList.add('overflow-hidden');
            }

            sessionTypeInput?.addEventListener('change', syncSessionLocationField);
            syncSessionLocationField();
        });
    </script>
@endsection
