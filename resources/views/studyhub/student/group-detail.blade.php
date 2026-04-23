@extends('studyhub.student.layout')

@section('title', $group['name'])

@section('page')
    @php
        $showUploadModal = $errors->has('group_id') || $errors->has('category') || $errors->has('resource_file');
        $showSessionModal = $errors->has('title') || $errors->has('date') || $errors->has('start_time') || $errors->has('end_time') || $errors->has('location') || $errors->has('type') || $errors->has('max_attendees');
    @endphp

    <a class="back-link" href="{{ route('studyhub.student.groups') }}">
        <span class="icon-box">{!! $icons['arrow-left'] !!}</span>
        <span>Back to Groups</span>
    </a>

    <section class="content-card detail-hero">
        <div class="detail-hero-banner">
            <h2>{{ $group['name'] }}</h2>
            <p>{{ $group['description'] }}</p>
            <div class="detail-hero-meta">
                <div class="detail-hero-tags">
                    <div class="detail-hero-members">
                        <span class="icon-box">{!! $icons['users'] !!}</span>
                        <span>{{ $group['members'] }} members</span>
                    </div>
                    @if (($group['visibility'] ?? 'public') === 'private')
                        <span class="detail-hero-tag">
                            <span class="icon-box">{!! $icons['lock'] !!}</span>
                            <span>Private</span>
                        </span>
                    @endif
                </div>
                @if ($isJoined)
                    <form method="POST" action="{{ route('studyhub.student.groups.leave', $group['id']) }}">
                        @csrf
                        <button class="detail-hero-button joined leave" type="submit">Leave Group</button>
                    </form>
                @else
                    <form class="detail-hero-join" method="POST" action="{{ route('studyhub.student.groups.join', $group['id']) }}">
                        @csrf
                        @if (($group['visibility'] ?? 'public') === 'private')
                            <input class="detail-hero-code" type="text" name="join_code" placeholder="Enter join code" value="{{ old('join_code') }}">
                        @endif
                        <button class="detail-hero-button" type="submit">Join Group</button>
                        @if (! empty($joinCodeError))
                            <div class="detail-hero-code-error">{{ $joinCodeError }}</div>
                        @endif
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="detail-grid">
        <article class="content-card detail-panel">
            <div class="detail-panel-header">
                <div class="detail-panel-title">
                    <span class="icon-box">{!! $icons['file'] !!}</span>
                    <span>Shared Resources</span>
                </div>
                @if ($isJoined)
                    <button class="detail-panel-action" type="button" data-detail-upload-open>
                        <span class="icon-box">{!! $icons['plus'] !!}</span>
                        <span>Upload</span>
                    </button>
                @else
                    <span class="detail-panel-action cursor-default opacity-70">
                        <span>Join to upload</span>
                    </span>
                @endif
            </div>

            <div class="detail-panel-body">
                @forelse ($resources as $resource)
                    <div class="resource-row">
                        <div class="resource-main">
                            <div class="icon-box resource-icon">{!! $icons['file'] !!}</div>
                            <div class="resource-copy">
                                <strong>{{ $resource['name'] }}</strong>
                                <div class="resource-meta">
                                    <span>Uploaded by {{ $resource['uploaded_by'] ?? 'StudyHub Member' }}</span>
                                    @if (! empty($resource['size']))
                                        <span>{{ $resource['size'] }}</span>
                                    @endif
                                    <span>{{ $resource['date'] }}</span>
                                </div>
                            </div>
                        </div>
                        <a class="resource-download" href="{{ ! empty($resource['path']) ? asset('storage/'.$resource['path']) : '#' }}" @if (! empty($resource['path'])) download @endif>Download</a>
                    </div>
                @empty
                    <div class="resource-empty">No resources yet. Upload the first file for this group.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card detail-panel">
            <div class="detail-panel-header">
                <div class="detail-panel-title">
                    <span class="icon-box">{!! $icons['calendar'] !!}</span>
                    <span>Upcoming Sessions</span>
                </div>
                @if ($isJoined)
                    <button class="detail-panel-action" type="button" data-detail-session-open>
                        <span class="icon-box">{!! $icons['plus'] !!}</span>
                        <span>Schedule</span>
                    </button>
                @endif
            </div>

            <div class="detail-panel-body">
                @forelse ($sessions as $session)
                    @php
                        $isSessionJoined = in_array($studentProfile['display_name'], $session['attendee_names'] ?? [], true);
                        $isSessionFull = (int) $session['attendees'] >= (int) $session['max_attendees'];
                    @endphp
                    <div class="session-card">
                        <h3>{{ $session['title'] }}</h3>
                        <p>{{ $session['date'] }} at {{ $session['time'] }}</p>
                        <p>{{ $session['location'] }}</p>
                        <p>{{ $session['attendees'] }} / {{ $session['max_attendees'] }} attendees</p>
                        @if (! empty($session['notes']))
                            <p class="session-note">{{ $session['notes'] }}</p>
                        @endif
                        <div class="session-actions">
                            @if ($isSessionJoined)
                                <span class="session-rsvp joined">{{ $session['type'] === 'online' ? 'Joined' : 'RSVPd' }}</span>
                            @else
                                <form method="POST" action="{{ route('studyhub.student.sessions.rsvp', $session['id']) }}">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">
                                    <button class="session-rsvp" type="submit" @if ($isSessionFull) disabled @endif>{{ $isSessionFull ? 'Full' : ($session['type'] === 'online' ? 'Join' : 'RSVP') }}</button>
                                </form>
                            @endif
                            <button
                                class="session-detail-button"
                                type="button"
                                data-detail-session-details
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
                @empty
                    <div class="session-empty">No sessions yet. Schedule the first group session.</div>
                @endforelse

                @if ($isJoined)
                    <button class="schedule-button" type="button" data-detail-session-open>Schedule New Session</button>
                @endif
            </div>
        </article>
    </section>

    @if ($isJoined)
        <div class="detail-session-modal @if ($showSessionModal) is-open @endif" data-detail-session-modal>
            <button class="detail-session-backdrop" type="button" aria-label="Close session form" data-detail-session-close></button>
            <div class="detail-session-panel">
                <div class="detail-session-header">
                    <div>
                        <h3 class="detail-session-title">Schedule session</h3>
                        <p class="detail-session-copy">{{ $group['name'] }}</p>
                    </div>
                    <button class="detail-session-close" type="button" aria-label="Close session form" data-detail-session-close>&times;</button>
                </div>

                <div class="detail-session-body">
                    @if ($showSessionModal)
                        <div class="detail-upload-errors">
                            Please fix the following:
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="detail-session-form" method="POST" action="{{ route('studyhub.student.sessions.store') }}">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">

                        <label class="detail-session-field full">
                            <span class="detail-session-label">Title</span>
                            <input class="detail-session-input" type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Algorithms Review Session" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Type</span>
                            <select class="detail-session-select" name="type" required>
                                <option value="">Choose type</option>
                                <option value="in-person" @selected(old('type') === 'in-person')>In person</option>
                                <option value="online" @selected(old('type') === 'online')>Online</option>
                            </select>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Location</span>
                            <input class="detail-session-input" type="text" name="location" maxlength="120" value="{{ old('location') }}" placeholder="Library Room 204 or Zoom" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Date</span>
                            <input class="detail-session-input" type="date" name="date" value="{{ old('date') }}" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Max attendees</span>
                            <input class="detail-session-input" type="number" name="max_attendees" min="2" max="100" value="{{ old('max_attendees', 12) }}" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Start time</span>
                            <input class="detail-session-input" type="time" name="start_time" value="{{ old('start_time') }}" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">End time</span>
                            <input class="detail-session-input" type="time" name="end_time" value="{{ old('end_time') }}" required>
                        </label>

                        <div class="detail-session-submit-row">
                            <button class="detail-session-submit" type="submit">Create Session</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="detail-session-modal" data-detail-session-details-modal>
            <button class="detail-session-backdrop" type="button" aria-label="Close session details" data-detail-session-details-close></button>
            <div class="detail-session-details-panel">
                <div class="detail-session-header">
                    <div>
                        <h3 class="detail-session-title" data-detail-session-title>Session Details</h3>
                        <p class="detail-session-copy" data-detail-session-group></p>
                    </div>
                    <button class="detail-session-close" type="button" aria-label="Close session details" data-detail-session-details-close>&times;</button>
                </div>

                <div class="detail-session-body">
                    <div class="detail-session-details-grid">
                        <div class="detail-session-details-card">
                            <strong>Date & Time</strong>
                            <span><span data-detail-session-date></span> | <span data-detail-session-time></span></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Location</strong>
                            <span data-detail-session-location></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Type</strong>
                            <span data-detail-session-type></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Attendees</strong>
                            <span data-detail-session-attendees></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Host</strong>
                            <span data-detail-session-host></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Notes</strong>
                            <span data-detail-session-notes></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-upload-modal @if ($showUploadModal) is-open @endif" data-detail-upload-modal>
            <button class="detail-upload-backdrop" type="button" aria-label="Close upload form" data-detail-upload-close></button>
            <div class="detail-upload-panel">
                <div class="detail-upload-header">
                    <div>
                        <h3 class="detail-upload-title">Upload file</h3>
                        <p class="detail-upload-copy">{{ $group['name'] }}</p>
                    </div>
                    <button class="detail-upload-close" type="button" aria-label="Close upload form" data-detail-upload-close>&times;</button>
                </div>

                <div class="detail-upload-body">
                    @if ($showUploadModal)
                        <div class="detail-upload-errors">
                            Please fix the following:
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="detail-upload-form" method="POST" action="{{ route('studyhub.student.resources.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">

                        <label class="detail-upload-field">
                            <span class="detail-upload-label">Category</span>
                            <select class="detail-upload-select" name="category" required>
                                <option value="">Choose category</option>
                                @foreach ($resourceCategories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="detail-upload-field">
                            <span class="detail-upload-label">File</span>
                            <input class="detail-upload-file" type="file" name="resource_file" required>
                        </label>

                        <div class="detail-upload-actions">
                            <button class="detail-upload-submit" type="submit">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const uploadModal = document.querySelector('[data-detail-upload-modal]');
                const uploadOpenButton = document.querySelector('[data-detail-upload-open]');
                const uploadCloseButtons = document.querySelectorAll('[data-detail-upload-close]');
                const sessionModal = document.querySelector('[data-detail-session-modal]');
                const sessionOpenButtons = document.querySelectorAll('[data-detail-session-open]');
                const sessionCloseButtons = document.querySelectorAll('[data-detail-session-close]');
                const detailsModal = document.querySelector('[data-detail-session-details-modal]');
                const detailsOpenButtons = document.querySelectorAll('[data-detail-session-details]');
                const detailsCloseButtons = document.querySelectorAll('[data-detail-session-details-close]');

                const setBodyOverflow = function () {
                    const hasOpenModal = uploadModal?.classList.contains('is-open')
                        || sessionModal?.classList.contains('is-open')
                        || detailsModal?.classList.contains('is-open');
                    document.body.classList.toggle('overflow-hidden', hasOpenModal);
                };

                const setModalState = function (modal, isOpen) {
                    if (! modal) {
                        return;
                    }

                    modal.classList.toggle('is-open', isOpen);
                    setBodyOverflow();
                };

                uploadOpenButton?.addEventListener('click', function () {
                    setModalState(uploadModal, true);
                });

                uploadCloseButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(uploadModal, false);
                    });
                });

                sessionOpenButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(sessionModal, true);
                    });
                });

                sessionCloseButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(sessionModal, false);
                    });
                });

                detailsOpenButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        detailsModal.querySelector('[data-detail-session-title]').textContent = button.dataset.sessionTitle || 'Session Details';
                        detailsModal.querySelector('[data-detail-session-group]').textContent = button.dataset.sessionGroup || '';
                        detailsModal.querySelector('[data-detail-session-date]').textContent = button.dataset.sessionDate || '';
                        detailsModal.querySelector('[data-detail-session-time]').textContent = button.dataset.sessionTime || '';
                        detailsModal.querySelector('[data-detail-session-location]').textContent = button.dataset.sessionLocation || '';
                        detailsModal.querySelector('[data-detail-session-type]').textContent = button.dataset.sessionType || '';
                        detailsModal.querySelector('[data-detail-session-attendees]').textContent = button.dataset.sessionAttendees || '';
                        detailsModal.querySelector('[data-detail-session-host]').textContent = button.dataset.sessionHost || '';
                        detailsModal.querySelector('[data-detail-session-notes]').textContent = button.dataset.sessionNotes || '';
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
                        setModalState(uploadModal, false);
                        setModalState(sessionModal, false);
                        setModalState(detailsModal, false);
                    }
                });

                setBodyOverflow();
            });
        </script>
    @endif
@endsection
