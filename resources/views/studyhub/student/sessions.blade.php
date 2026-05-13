@extends('studyhub.student.layout')

@section('title', 'Study Sessions')

@section('page')
    @php
        $showScheduleModal = $errors->has('title') || $errors->has('group_id') || $errors->has('date') || $errors->has('start_time') || $errors->has('end_time') || $errors->has('location') || $errors->has('type') || $errors->has('max_attendees') || $errors->has('notes');
        $sessionFilters = $sessionFilters ?? ['tab' => 'all', 'view' => 'calendar', 'group_id' => ''];
        $sessionTab = $sessionFilters['tab'] ?? 'all';
        $sessionView = $sessionFilters['view'] ?? 'calendar';
        $sessionGroupId = (string) ($sessionFilters['group_id'] ?? '');
        $sessionWeekStart = $sessionFilters['week_start'] ?? '';
        $sessionFilterQuery = fn (array $overrides = []) => array_filter(array_merge($sessionFilters, $overrides), fn ($value) => $value !== '' && $value !== null);
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

    <section class="sessions-workspace">
        <div class="content-card sessions-calendar-card">
            <form class="sessions-calendar-toolbar" method="GET" action="{{ route('studyhub.student.sessions') }}" data-session-filter-form>
                <div class="sessions-tabs" aria-label="Session views">
                    <a class="{{ $sessionTab === 'all' ? 'is-active' : '' }}" href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['tab' => 'all'])) }}">All Sessions</a>
                    <a class="{{ $sessionTab === 'upcoming' ? 'is-active' : '' }}" href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['tab' => 'upcoming'])) }}">Upcoming</a>
                    <a class="{{ $sessionTab === 'calendar' ? 'is-active' : '' }}" href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['tab' => 'calendar', 'view' => 'calendar'])) }}">Calendar</a>
                    <a class="{{ $sessionTab === 'past' ? 'is-active' : '' }}" href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['tab' => 'past'])) }}">Past Sessions</a>
                </div>
                <div class="sessions-calendar-actions">
                    <input type="hidden" name="tab" value="{{ $sessionTab }}">
                    <input type="hidden" name="view" value="{{ $sessionView }}">
                    <input type="hidden" name="week_start" value="{{ $sessionWeekStart }}">
                    <label class="sessions-filter-pill sessions-group-filter">
                        <span class="icon-box">{!! $icons['users'] !!}</span>
                        <select name="group_id" data-session-filter-auto>
                            <option value="">All Groups</option>
                            @foreach ($sessionGroups as $group)
                                <option value="{{ $group['id'] }}" @selected($sessionGroupId === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <a class="sessions-filter-pill {{ $sessionView === 'calendar' ? 'active' : '' }}" href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['view' => 'calendar'])) }}">Calendar View</a>
                    <a class="sessions-filter-pill {{ $sessionView === 'list' ? 'active' : '' }}" href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['view' => 'list'])) }}">List View</a>
                </div>
            </form>

            <div class="sessions-week-header">
                <h3>{{ $calendarWeekLabel }}</h3>
                <div class="sessions-week-controls">
                    <a href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['week_start' => $calendarPrevWeek])) }}" aria-label="Previous week">&lt;</a>
                    <a href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['week_start' => $calendarCurrentWeek])) }}">Today</a>
                    <a href="{{ route('studyhub.student.sessions', $sessionFilterQuery(['week_start' => $calendarNextWeek])) }}" aria-label="Next week">&gt;</a>
                </div>
            </div>

            <div class="sessions-calendar-grid {{ $sessionView === 'list' ? 'is-hidden' : '' }}" style="--calendar-day-count: {{ count($calendarDays) }};">
                <div class="sessions-calendar-days">
                    <span></span>
                    @foreach ($calendarDays as $day)
                        <strong class="{{ $day['is_today'] ? 'is-today' : '' }}">
                            <span>{{ $day['label'] }}</span>
                            <em>{{ $day['day'] }}</em>
                        </strong>
                    @endforeach
                </div>

                <div class="sessions-calendar-body">
                    <div class="sessions-time-column">
                        @foreach ($calendarHours as $hour)
                            <span>{{ $hour }}</span>
                        @endforeach
                    </div>
                    <div class="sessions-day-columns">
                        @foreach ($calendarDays as $day)
                            <div class="sessions-day-column"></div>
                        @endforeach
                        @foreach ($calendarSessions as $calendarSession)
                            <button
                                class="sessions-calendar-event {{ $calendarSession['type'] === 'online' ? 'is-online' : '' }}"
                                type="button"
                                data-session-details
                                data-session-title="{{ $calendarSession['title'] }}"
                                data-session-group="{{ $calendarSession['group'] }}"
                                data-session-date="{{ $calendarDays[$calendarSession['day_index']]['label'] ?? '' }}"
                                data-session-time="{{ $calendarSession['time'] }}"
                                data-session-location="{{ $calendarSession['type'] === 'online' ? 'Online meeting' : 'Study location' }}"
                                data-session-type="{{ ucfirst($calendarSession['type']) }}"
                                data-session-attendees="Open details"
                                data-session-host="StudyHub Member"
                                data-session-notes="Open the full session card for more details."
                                style="--event-day: {{ $calendarSession['day_index'] + 1 }}; --event-top: {{ $calendarSession['top'] }}px; --event-height: {{ $calendarSession['height'] }}px;"
                            >
                                <strong>{{ Str::limit($calendarSession['title'], 24) }}</strong>
                                <span>{{ $calendarSession['time'] }}</span>
                                <small>{{ Str::limit($calendarSession['group'], 26) }}</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <aside class="sessions-side-rail">
            <section class="content-card sessions-side-card">
                <div class="sessions-side-card-header">
                    <h3>Today's Schedule</h3>
                    <span>{{ now()->format('M j') }}</span>
                </div>
                <div class="sessions-side-list">
                    @forelse ($todaySchedule as $session)
                        <button
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
                        >
                            <span>{{ $session['time'] }}</span>
                            <strong>{{ $session['title'] }}</strong>
                            <em>{{ $session['group'] }}</em>
                        </button>
                    @empty
                        <p>No sessions scheduled today.</p>
                    @endforelse
                </div>
            </section>

            <section class="content-card sessions-side-card">
                <div class="sessions-side-card-header">
                    <h3>Upcoming Reminders</h3>
                    <span>{{ count($upcomingReminders) }}</span>
                </div>
                <div class="sessions-reminder-list">
                    @forelse ($upcomingReminders as $session)
                        <div>
                            <span class="icon-box">{!! $icons[$session['type'] === 'online' ? 'video' : 'calendar'] !!}</span>
                            <p>
                                <strong>{{ $session['title'] }}</strong>
                                <small>{{ $session['date'] }} | {{ $session['group'] }}</small>
                            </p>
                        </div>
                    @empty
                        <p>No upcoming reminders.</p>
                    @endforelse
                </div>
            </section>

            <section class="content-card sessions-side-card">
                <div class="sessions-side-card-header">
                    <h3>Most Active Groups</h3>
                    <span>Sessions</span>
                </div>
                <div class="sessions-active-groups">
                    @forelse ($activeSessionGroups as $group)
                        <div>
                            <span>{{ Str::of($group['name'])->substr(0, 2)->upper() }}</span>
                            <strong>{{ $group['name'] }}</strong>
                            <small>{{ $group['count'] }} sessions</small>
                        </div>
                    @empty
                        <p>No session activity yet.</p>
                    @endforelse
                </div>
                <a class="sessions-side-link" href="{{ route('studyhub.student.groups') }}">View all groups</a>
            </section>
        </aside>
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

    <div class="sessions-create-modal {{ $showScheduleModal ? 'is-open' : '' }}" aria-hidden="{{ $showScheduleModal ? 'false' : 'true' }}" data-studyhub-modal data-sessions-modal>
        <button class="sessions-create-backdrop" type="button" aria-label="Close create session" data-sessions-close></button>
        <div class="sessions-create-panel">
            <div class="sessions-create-heading">
                <span class="sessions-create-heading-icon icon-box">{!! $icons['calendar'] !!}</span>
                <div>
                    <h3>Create Session</h3>
                    <p>Plan a new study block for one of your joined groups.</p>
                </div>
                <button class="sessions-create-close" type="button" aria-label="Close create session" data-sessions-close>&times;</button>
            </div>

            @if ($showScheduleModal)
                <div class="sessions-create-errors" role="alert" aria-live="polite">
                    <strong>Session was not scheduled</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="sessions-create-form" method="POST" action="{{ route('studyhub.student.sessions.store') }}">
                @csrf
                <label class="sessions-create-field full">
                    <span>Title <strong>*</strong></span>
                    <div class="sessions-create-control">
                        <span class="icon-box">{!! $icons['settings'] !!}</span>
                        <input type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="e.g. Algorithms Review Session" required>
                    </div>
                </label>

                <label class="sessions-create-field">
                    <span>Study Group <strong>*</strong></span>
                    <div class="sessions-create-control select">
                        <span class="icon-box">{!! $icons['users'] !!}</span>
                        <select name="group_id" required>
                            <option value="">Select study group</option>
                            @foreach ($sessionGroups as $group)
                                <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </label>

                <div class="sessions-create-field">
                    <span>Session Type <strong>*</strong></span>
                    <div class="sessions-create-control select">
                        <span class="icon-box">{!! $icons['video'] !!}</span>
                        <select name="type" required data-session-type-input>
                            <option value="">Select session type</option>
                            <option value="in-person" @selected(old('type') === 'in-person')>In person</option>
                            <option value="online" @selected(old('type') === 'online')>Online</option>
                        </select>
                    </div>
                </div>

                <label class="sessions-create-field">
                    <span>Date <strong>*</strong></span>
                    <div class="sessions-create-control">
                        <span class="icon-box">{!! $icons['calendar'] !!}</span>
                        <input type="date" name="date" value="{{ old('date') }}" required>
                    </div>
                </label>

                <label class="sessions-create-field">
                    <span data-session-location-label>Location / Meeting Link <strong>*</strong></span>
                    <div class="sessions-create-control">
                        <span class="icon-box">{!! $icons['map-pin'] !!}</span>
                        <input type="text" name="location" maxlength="255" value="{{ old('location') }}" placeholder="e.g. Library Room 204 or paste link" required data-session-location-input>
                    </div>
                    <small>Add a room for in-person sessions or a link for online sessions.</small>
                </label>

                <label class="sessions-create-field compact">
                    <span>Start Time <strong>*</strong></span>
                    <div class="sessions-create-control select">
                        <span class="icon-box">{!! $icons['clock'] !!}</span>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" required data-session-start-time>
                    </div>
                </label>

                <label class="sessions-create-field compact">
                    <span>End Time <strong>*</strong></span>
                    <div class="sessions-create-control select">
                        <span class="icon-box">{!! $icons['clock'] !!}</span>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" required data-session-end-time>
                    </div>
                    <small>End time must be later than start time.</small>
                </label>

                <div class="sessions-create-duration">
                    <strong>Duration</strong>
                    <div><span class="icon-box">{!! $icons['clock'] !!}</span><span data-session-duration>--</span></div>
                    <small>Automatically calculated</small>
                </div>

                <label class="sessions-create-field full">
                    <span>Max Attendees <strong>*</strong></span>
                    <div class="sessions-create-control">
                        <span class="icon-box">{!! $icons['users'] !!}</span>
                        <input type="number" name="max_attendees" min="2" max="100" value="{{ old('max_attendees', 12) }}" placeholder="Enter maximum number of participants" required>
                    </div>
                    <small>Set the maximum number of students who can join this session.</small>
                </label>

                <label class="sessions-create-field full">
                    <span>Description <em>(Optional)</em></span>
                    <div class="sessions-create-control textarea">
                        <span class="icon-box">{!! $icons['file'] !!}</span>
                        <textarea name="notes" maxlength="500" rows="3" placeholder="Add a brief description, agenda, or notes for this session...">{{ old('notes') }}</textarea>
                    </div>
                    <small>This will help participants understand what to expect.</small>
                </label>

                <div class="sessions-create-options full">
                    <span><span class="icon-box">{!! $icons['bell'] !!}</span>Add Reminder <i class="is-on"></i></span>
                    <span><span class="icon-box">{!! $icons['users'] !!}</span>Allow RSVP <i class="is-on"></i></span>
                </div>

                <div class="sessions-create-actions full">
                    <button class="sessions-create-cancel" type="button" data-sessions-close>Cancel</button>
                    <button class="sessions-create-submit" type="submit" data-loading-label="Creating session...">
                        <span class="icon-box">{!! $icons['calendar'] !!}</span>
                        <span>Create Session</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
            const detailsModal = document.querySelector('[data-session-details-modal]');
            const sessionTypeInput = document.querySelector('[data-session-type-input]');
            const sessionLocationLabel = document.querySelector('[data-session-location-label]');
            const sessionLocationInput = document.querySelector('[data-session-location-input]');
            const sessionStartTime = document.querySelector('[data-session-start-time]');
            const sessionEndTime = document.querySelector('[data-session-end-time]');
            const sessionDuration = document.querySelector('[data-session-duration]');
            const sessionFilterForm = document.querySelector('[data-session-filter-form]');

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

            const syncSessionDuration = function () {
                if (! sessionStartTime || ! sessionEndTime || ! sessionDuration) {
                    return;
                }

                if (! sessionStartTime.value || ! sessionEndTime.value) {
                    sessionDuration.textContent = '--';
                    return;
                }

                const [startHour, startMinute] = sessionStartTime.value.split(':').map(Number);
                const [endHour, endMinute] = sessionEndTime.value.split(':').map(Number);
                const startTotal = (startHour * 60) + startMinute;
                const endTotal = (endHour * 60) + endMinute;
                const duration = endTotal - startTotal;

                if (duration <= 0) {
                    sessionDuration.textContent = 'Invalid time';
                    return;
                }

                const hours = Math.floor(duration / 60);
                const minutes = duration % 60;
                sessionDuration.textContent = [
                    hours ? hours + 'h' : '',
                    minutes ? minutes + 'm' : '',
                ].filter(Boolean).join(' ');
            };

            if (scheduleModal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: scheduleModal,
                    open: '[data-session-open]',
                    close: '[data-sessions-close]',
                    beforeOpen: syncSessionLocationField,
                });
            }

            if (detailsModal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: detailsModal,
                    open: '[data-session-details]',
                    close: '[data-session-details-close]',
                    beforeOpen: function (button) {
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
                    },
                });
            }

            sessionTypeInput?.addEventListener('change', syncSessionLocationField);
            sessionStartTime?.addEventListener('change', syncSessionDuration);
            sessionEndTime?.addEventListener('change', syncSessionDuration);
            sessionFilterForm?.querySelectorAll('[data-session-filter-auto]').forEach(function (control) {
                control.addEventListener('change', function () {
                    if (sessionFilterForm.requestSubmit) {
                        sessionFilterForm.requestSubmit();
                        return;
                    }

                    sessionFilterForm.submit();
                });
            });
            syncSessionLocationField();
            syncSessionDuration();
            window.StudyHubUI.syncBodyOverflow();
        });
    </script>
@endsection
