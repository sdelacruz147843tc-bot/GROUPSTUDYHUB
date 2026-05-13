@extends('studyhub.student.layout')

@section('title', $group['name'])

@section('page')
    @php
        $showUploadModal = $errors->has('group_id') || $errors->has('category') || $errors->has('resource_file');
        $showSessionModal = $errors->has('title') || $errors->has('date') || $errors->has('start_time') || $errors->has('end_time') || $errors->has('location') || $errors->has('type') || $errors->has('max_attendees');
        $groupImages = [
            'artificial intelligence' => 'Artificial intelegence.png',
            'business' => 'business.png',
            'calculus' => 'Mathematics.png',
            'computer science' => 'Computer Science.png',
            'database' => 'Information system.png',
            'design' => 'design.png',
            'english' => 'General.png',
            'general' => 'General.png',
            'information system' => 'Information system.png',
            'information systems' => 'Information system.png',
            'language' => 'language.png',
            'math' => 'Mathematics.png',
            'mathematics' => 'Mathematics.png',
            'other' => 'General.png',
            'programming' => 'Programming.png',
            'research' => 'General.png',
            'science' => 'science.png',
            'web' => 'Programming.png',
        ];
        $groupThemes = [
            'artificial intelligence' => ['rgb' => '40, 28, 88', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
            'business' => ['rgb' => '37, 18, 82', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
            'calculus' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
            'computer science' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
            'database' => ['rgb' => '60, 39, 13', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
            'design' => ['rgb' => '92, 43, 4', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
            'english' => ['rgb' => '16, 54, 37', 'accent' => '74, 222, 128', 'accentHex' => '#86efac'],
            'general' => ['rgb' => '16, 54, 37', 'accent' => '74, 222, 128', 'accentHex' => '#86efac'],
            'information system' => ['rgb' => '60, 39, 13', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
            'information systems' => ['rgb' => '60, 39, 13', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
            'language' => ['rgb' => '8, 46, 95', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
            'math' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
            'mathematics' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
            'other' => ['rgb' => '16, 54, 37', 'accent' => '74, 222, 128', 'accentHex' => '#86efac'],
            'programming' => ['rgb' => '40, 28, 88', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
            'research' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
            'science' => ['rgb' => '6, 52, 27', 'accent' => '34, 197, 94', 'accentHex' => '#86efac'],
            'web' => ['rgb' => '40, 28, 88', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
        ];
        $groupImage = asset('images/General.png');
        $groupTheme = $groupThemes['general'];
        $groupImageHaystack = strtolower(($group['category'] ?? '').' '.($group['name'] ?? '').' '.($group['description'] ?? ''));

        foreach ($groupImages as $keyword => $image) {
            if (str_contains($groupImageHaystack, $keyword)) {
                $groupImage = asset('images/'.$image);
                $groupTheme = $groupThemes[$keyword] ?? $groupThemes['general'];
                break;
            }
        }
    @endphp

    <a class="back-link" href="{{ route('studyhub.student.groups') }}">
        <span class="icon-box">{!! $icons['arrow-left'] !!}</span>
        <span>Back to Groups</span>
    </a>

    <section class="content-card detail-hero" style="--detail-group-image: url('{{ $groupImage }}'); --detail-group-rgb: {{ $groupTheme['rgb'] }}; --detail-group-accent-rgb: {{ $groupTheme['accent'] }}; --detail-group-accent: {{ $groupTheme['accentHex'] }};">
        <div class="detail-hero-banner">
            <div class="detail-hero-copy">
                <span class="detail-hero-kicker">{{ $group['category'] ?? 'Study Group' }}</span>
                <h2>{{ $group['name'] }}</h2>
                <p>{{ $group['description'] }}</p>
                <div class="detail-hero-tags">
                    <span class="detail-hero-members">
                        <span class="icon-box">{!! $icons['users'] !!}</span>
                        <span>{{ $group['members'] }} members</span>
                    </span>
                    <span class="detail-hero-tag">{{ ucfirst(str_replace('-', ' ', $group['meeting_style'] ?? 'in-person')) }}</span>
                    @if (($group['visibility'] ?? 'public') === 'private')
                        <span class="detail-hero-tag private">
                            <span class="icon-box">{!! $icons['lock'] !!}</span>
                            <span>Private</span>
                        </span>
                    @endif
                </div>
            </div>
            <div class="detail-hero-art" aria-hidden="true"></div>
            <div class="detail-hero-meta">
                @if ($isJoined)
                    <form method="POST" action="{{ route('studyhub.student.groups.leave', $group['id']) }}">
                        @csrf
                        <button class="detail-hero-button joined leave" type="submit" data-loading-label="Leaving...">Leave Group</button>
                    </form>
                @else
                    <form class="detail-hero-join" method="POST" action="{{ route('studyhub.student.groups.join', $group['id']) }}">
                        @csrf
                        @if (($group['visibility'] ?? 'public') === 'private')
                            <input class="detail-hero-code" type="text" name="join_code" placeholder="Enter join code" value="{{ old('join_code') }}">
                        @endif
                        <button class="detail-hero-button" type="submit" data-loading-label="Joining...">Join Group</button>
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
                                    @if ((int) ($resource['rating_count'] ?? 0) > 0)
                                        <span>{{ number_format((float) $resource['rating_average'], 1) }} rating</span>
                                    @endif
                                    @if (! empty($resource['size']))
                                        <span>{{ $resource['size'] }}</span>
                                    @endif
                                    <span>{{ $resource['date'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="resource-actions">
                            <a class="resource-download" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.view', $resource['id']) : '#' }}" target="_blank" rel="noopener" @if (empty($resource['path'])) aria-disabled="true" @endif>View</a>
                            <a class="resource-download" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.download', $resource['id']) : '#' }}" @if (empty($resource['path'])) aria-disabled="true" @endif>Download</a>
                            <form class="resource-save-form compact" method="POST" action="{{ ! empty($resource['is_saved']) ? route('studyhub.student.resources.unsave', $resource['id']) : route('studyhub.student.resources.save', $resource['id']) }}">
                                @csrf
                                @if (! empty($resource['is_saved']))
                                    @method('DELETE')
                                @endif
                                <button class="resource-library-toggle {{ ! empty($resource['is_saved']) ? 'is-saved' : '' }}" type="submit">{{ ! empty($resource['is_saved']) ? 'Saved' : 'Save' }}</button>
                            </form>
                            @if (! empty($resource['can_delete']))
                                <form class="resource-delete-form" method="POST" action="{{ route('studyhub.student.resources.delete', $resource['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">
                                    <button class="resource-delete-button compact" type="button" data-resource-delete-open data-resource-delete-filename="{{ $resource['name'] }}">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="resource-empty app-empty-state compact">
                        <span class="app-empty-icon">{!! $icons['file'] !!}</span>
                        <strong>No resources yet</strong>
                        <span>Upload the first file for this group.</span>
                        @if ($isJoined)
                            <button class="app-empty-action" type="button" data-detail-upload-open>Upload file</button>
                        @endif
                    </div>
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
                                    <button class="session-rsvp" type="submit" data-loading-label="{{ $session['type'] === 'online' ? 'Joining...' : 'Saving RSVP...' }}" @if ($isSessionFull) disabled @endif>{{ $isSessionFull ? 'Full' : ($session['type'] === 'online' ? 'Join' : 'RSVP') }}</button>
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
                                data-session-meeting-url="{{ $session['meeting_url'] ?? '' }}"
                                data-session-type="{{ ucfirst($session['type']) }}"
                                data-session-attendees="{{ $session['attendees'] }} / {{ $session['max_attendees'] }}"
                                data-session-host="{{ $session['created_by'] ?? 'StudyHub Member' }}"
                                data-session-notes="{{ $session['notes'] ?? 'No extra notes yet.' }}"
                            >Details</button>
                        </div>
                    </div>
                @empty
                    <div class="session-empty app-empty-state compact">
                        <span class="app-empty-icon">{!! $icons['calendar'] !!}</span>
                        <strong>No sessions yet</strong>
                        <span>Schedule the first group study session.</span>
                        @if ($isJoined)
                            <button class="app-empty-action" type="button" data-detail-session-open>Schedule session</button>
                        @endif
                    </div>
                @endforelse

                @if ($isJoined)
                    <button class="schedule-button" type="button" data-detail-session-open>Schedule New Session</button>
                @endif
            </div>
        </article>
    </section>

    <x-studyhub.modal
        title="Delete resource?"
        subtitle="This removes the file from this group."
        close-data="data-resource-delete-close"
        size="sm"
        data-resource-delete-modal
    >
                <div class="resource-delete-dialog">
                    <div class="resource-delete-dialog-icon">
                        <span class="icon-box">{!! $icons['trash'] !!}</span>
                    </div>
                    <div class="resource-delete-dialog-copy">
                        <strong data-resource-delete-target>this resource</strong>
                        <span>Deleting this resource also removes its stored file. This action cannot be undone.</span>
                    </div>
                </div>

                <div class="resource-delete-dialog-actions">
                    <button class="resource-delete-cancel" type="button" data-resource-delete-close>Cancel</button>
                    <button class="resource-delete-confirm" type="button" data-resource-delete-confirm>
                        <span class="icon-box">{!! $icons['trash'] !!}</span>
                        <span>Delete Resource</span>
                    </button>
                </div>
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.querySelector('[data-resource-delete-modal]');
            const deleteName = document.querySelector('[data-resource-delete-target]');
            const deleteConfirm = document.querySelector('[data-resource-delete-confirm]');
            let pendingDeleteForm = null;

            if (! deleteModal || ! window.StudyHubUI) {
                return;
            }

            window.StudyHubUI.bindModalTriggers({
                modal: deleteModal,
                open: '[data-resource-delete-open]',
                close: '[data-resource-delete-close]',
                beforeOpen: function (button) {
                    pendingDeleteForm = button.closest('form');

                    if (deleteName) {
                        deleteName.textContent = button.dataset.resourceDeleteFilename || 'this resource';
                    }
                },
                afterClose: function () {
                    pendingDeleteForm = null;
                },
            });

            deleteConfirm?.addEventListener('click', function () {
                if (! pendingDeleteForm) {
                    window.StudyHubUI.setModalState(deleteModal, false);
                    return;
                }

                deleteConfirm.disabled = true;
                deleteConfirm.innerHTML = '<span class="student-button-spinner" aria-hidden="true"></span><span>Deleting...</span>';
                pendingDeleteForm.submit();
            });
        });
    </script>

    @if ($isJoined)
        <x-studyhub.modal
            title="Schedule session"
            :subtitle="$group['name']"
            close-data="data-detail-session-close"
            :open="$showSessionModal"
            size="lg"
            data-detail-session-modal
        >
                    @if ($showSessionModal)
                        <div class="detail-upload-errors" role="alert" aria-live="polite">
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
                        <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2">
                            <span class="text-sm font-extrabold text-[#244231]">Title</span>
                            <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Algorithms Review Session" required>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">Type</span>
                            <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="type" required data-detail-session-type-input>
                                <option value="">Choose type</option>
                                <option value="in-person" @selected(old('type') === 'in-person')>In person</option>
                                <option value="online" @selected(old('type') === 'online')>Online</option>
                            </select>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]" data-detail-session-location-label>Location</span>
                            <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="location" maxlength="255" value="{{ old('location') }}" placeholder="Library Room 204" required data-detail-session-location-input>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">Date</span>
                            <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="date" name="date" value="{{ old('date') }}" required>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">Max attendees</span>
                            <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="number" name="max_attendees" min="2" max="100" value="{{ old('max_attendees', 12) }}" required>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">Start time</span>
                            <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="time" name="start_time" value="{{ old('start_time') }}" required>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">End time</span>
                            <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="time" name="end_time" value="{{ old('end_time') }}" required>
                        </label>

                        <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6 md:col-span-2">
                            <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Scheduling...">Create Session</button>
                        </div>
                    </form>
        </x-studyhub.modal>

        <x-studyhub.modal
            title="Session Details"
            close-data="data-detail-session-details-close"
            data-detail-session-details-modal
        >
                    <span class="sr-only" data-detail-session-title>Session Details</span>
                    <p class="mb-4 text-sm font-semibold text-[#5f776b]" data-detail-session-group></p>
                    <div class="detail-session-details-grid">
                        <div class="detail-session-details-card">
                            <strong>Date & Time</strong>
                            <span><span data-detail-session-date></span> | <span data-detail-session-time></span></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong data-detail-session-location-heading>Location</strong>
                            <span data-detail-session-location></span>
                            <a class="meeting-link-button" href="#" target="_blank" rel="noopener noreferrer" data-detail-session-meeting-link hidden>Open meeting</a>
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
        </x-studyhub.modal>

        <x-studyhub.modal
            title="Upload file"
            :subtitle="$group['name']"
            close-data="data-detail-upload-close"
            :open="$showUploadModal"
            data-detail-upload-modal
        >
                    @if ($showUploadModal)
                        <div class="detail-upload-errors" role="alert" aria-live="polite">
                            <strong>Upload was not saved</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="grid gap-3" method="POST" action="{{ route('studyhub.student.resources.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">Category</span>
                            <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="category" required>
                                <option value="">Choose category</option>
                                @foreach ($resourceCategories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                            <span class="text-sm font-extrabold text-[#244231]">File</span>
                            <span class="upload-dropzone" data-upload-dropzone>
                                <input class="upload-dropzone-input" type="file" name="resource_file" required data-upload-file-input>
                                <span class="upload-dropzone-icon">{!! $icons['file'] !!}</span>
                                <span class="upload-dropzone-title">Drag file here</span>
                                <span class="upload-dropzone-subtitle">or click to choose</span>
                                <span class="upload-dropzone-name" data-upload-file-name>No file selected</span>
                            </span>
                        </label>

                        <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6">
                            <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Uploading...">Upload</button>
                        </div>
                    </form>
        </x-studyhub.modal>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const uploadModal = document.querySelector('[data-detail-upload-modal]');
                const sessionModal = document.querySelector('[data-detail-session-modal]');
                const detailsModal = document.querySelector('[data-detail-session-details-modal]');
                const uploadDropzones = document.querySelectorAll('[data-upload-dropzone]');
                const detailSessionTypeInput = document.querySelector('[data-detail-session-type-input]');
                const detailSessionLocationLabel = document.querySelector('[data-detail-session-location-label]');
                const detailSessionLocationInput = document.querySelector('[data-detail-session-location-input]');

                const syncDetailSessionLocationField = function () {
                    const isOnline = detailSessionTypeInput?.value === 'online';

                    if (detailSessionLocationLabel) {
                        detailSessionLocationLabel.textContent = isOnline ? 'Meeting link' : 'Location';
                    }

                    if (detailSessionLocationInput) {
                        detailSessionLocationInput.type = isOnline ? 'url' : 'text';
                        detailSessionLocationInput.placeholder = isOnline ? 'https://meet.google.com/abc-defg-hij' : 'Library Room 204';
                    }
                };

                if (uploadModal) {
                    window.StudyHubUI.bindModalTriggers({
                        modal: uploadModal,
                        open: '[data-detail-upload-open]',
                        close: '[data-detail-upload-close]',
                    });
                }

                uploadDropzones.forEach(function (dropzone) {
                    const input = dropzone.querySelector('[data-upload-file-input]');
                    const fileName = dropzone.querySelector('[data-upload-file-name]');

                    if (! input || ! fileName) {
                        return;
                    }

                    const setFileName = function () {
                        fileName.textContent = input.files?.[0]?.name || 'No file selected';
                        dropzone.classList.toggle('has-file', Boolean(input.files?.length));
                    };

                    input.addEventListener('change', setFileName);

                    ['dragenter', 'dragover'].forEach(function (eventName) {
                        dropzone.addEventListener(eventName, function (event) {
                            event.preventDefault();
                            dropzone.classList.add('is-dragging');
                        });
                    });

                    ['dragleave', 'drop'].forEach(function (eventName) {
                        dropzone.addEventListener(eventName, function (event) {
                            event.preventDefault();
                            dropzone.classList.remove('is-dragging');
                        });
                    });

                    dropzone.addEventListener('drop', function (event) {
                        if (event.dataTransfer?.files?.length) {
                            input.files = event.dataTransfer.files;
                            setFileName();
                        }
                    });
                });

                if (sessionModal) {
                    window.StudyHubUI.bindModalTriggers({
                        modal: sessionModal,
                        open: '[data-detail-session-open]',
                        close: '[data-detail-session-close]',
                        beforeOpen: syncDetailSessionLocationField,
                    });
                }

                if (detailsModal) {
                    window.StudyHubUI.bindModalTriggers({
                        modal: detailsModal,
                        open: '[data-detail-session-details]',
                        close: '[data-detail-session-details-close]',
                        beforeOpen: function (button) {
                            detailsModal.querySelector('[data-detail-session-title]').textContent = button.dataset.sessionTitle || 'Session Details';
                            detailsModal.querySelector('[data-detail-session-group]').textContent = button.dataset.sessionGroup || '';
                            detailsModal.querySelector('[data-detail-session-date]').textContent = button.dataset.sessionDate || '';
                            detailsModal.querySelector('[data-detail-session-time]').textContent = button.dataset.sessionTime || '';
                            const meetingUrl = button.dataset.sessionMeetingUrl || '';
                            const isOnline = (button.dataset.sessionType || '').toLowerCase() === 'online';
                            const meetingLink = detailsModal.querySelector('[data-detail-session-meeting-link]');
                            detailsModal.querySelector('[data-detail-session-location-heading]').textContent = isOnline ? 'Meeting link' : 'Location';
                            detailsModal.querySelector('[data-detail-session-location]').textContent = button.dataset.sessionLocation || '';
                            if (meetingLink) {
                                meetingLink.hidden = ! isOnline || ! meetingUrl;
                                meetingLink.href = meetingUrl || '#';
                            }
                            detailsModal.querySelector('[data-detail-session-type]').textContent = button.dataset.sessionType || '';
                            detailsModal.querySelector('[data-detail-session-attendees]').textContent = button.dataset.sessionAttendees || '';
                            detailsModal.querySelector('[data-detail-session-host]').textContent = button.dataset.sessionHost || '';
                            detailsModal.querySelector('[data-detail-session-notes]').textContent = button.dataset.sessionNotes || '';
                        },
                    });
                }

                detailSessionTypeInput?.addEventListener('change', syncDetailSessionLocationField);
                syncDetailSessionLocationField();
                window.StudyHubUI.syncBodyOverflow();
            });
        </script>
    @endif
@endsection
