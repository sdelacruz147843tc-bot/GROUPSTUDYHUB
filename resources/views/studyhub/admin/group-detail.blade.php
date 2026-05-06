@extends('studyhub.admin.layout')

@section('title', 'Manage Group')

@section('page')
    @php
        $groupCategories = [
            'General',
            'Mathematics',
            'Programming',
            'Science',
            'Language',
            'Business',
            'Design',
            'Computer Science',
            'Information Systems',
            'Artificial Intelligence',
            'Other',
        ];
        $currentCategory = old('category', $group->category);
        $ownerName = $group->owner?->display_name ?: $group->owner?->name ?: 'No owner';
    @endphp

    <div class="toolbar">
        <div>
            <a class="back-link" href="{{ route('studyhub.admin.groups') }}">
                <span class="icon-box">{!! $icons['arrow-left'] !!}</span>
                <span>Back to groups</span>
            </a>
            <h2 class="page-title">{{ $group->name }}</h2>
            <p class="page-subtitle">{{ $group->description }}</p>
        </div>
        <div class="toolbar-actions">
            <form method="POST" action="{{ route('studyhub.admin.groups.delete', $group) }}" onsubmit="return confirm('Delete {{ addslashes($group->name) }} and all of its StudyHub records?')">
                @csrf
                @method('DELETE')
                <button class="users-delete-submit" type="submit">
                    <span class="icon-box">{!! $icons['trash'] !!}</span>
                    <span>Delete Group</span>
                </button>
            </form>
        </div>
    </div>

    <section class="stats-grid">
        <article class="stat-card">
            <div class="mb-2 text-[var(--text-muted)]">Members</div>
            <div class="text-[2rem] font-extrabold text-[#0F4C75]">{{ $group->members_count }}</div>
        </article>
        <article class="stat-card">
            <div class="mb-2 text-[var(--text-muted)]">Resources</div>
            <div class="text-[2rem] font-extrabold text-[#3282B8]">{{ $group->resources_count }}</div>
        </article>
        <article class="stat-card">
            <div class="mb-2 text-[var(--text-muted)]">Discussions</div>
            <div class="text-[2rem] font-extrabold text-[#06D6A0]">{{ $group->discussions_count }}</div>
        </article>
        <article class="stat-card">
            <div class="mb-2 text-[var(--text-muted)]">Sessions</div>
            <div class="text-[2rem] font-extrabold text-[#FF6B35]">{{ $group->sessions_count }}</div>
        </article>
    </section>

    <section class="admin-detail-grid">
        <article class="content-card panel" id="manage-group">
            <h3>Manage Group</h3>

            @if ($errors->any())
                <div class="users-errors">
                    Please fix the highlighted fields and try again.
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="users-form" method="POST" action="{{ route('studyhub.admin.groups.update', $group) }}">
                @csrf
                @method('PUT')

                <div class="users-field full">
                    <label for="name">Group Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $group->name) }}">
                    @error('name')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4">{{ old('description', $group->description) }}</textarea>
                    @error('description')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        @if (! in_array($currentCategory, $groupCategories, true))
                            <option value="{{ $currentCategory }}" selected>{{ $currentCategory }}</option>
                        @endif
                        @foreach ($groupCategories as $category)
                            <option value="{{ $category }}" {{ $currentCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field">
                    <label for="meeting_style">Meeting Style</label>
                    <select id="meeting_style" name="meeting_style">
                        @foreach ($meetingStyles as $value => $label)
                            <option value="{{ $value }}" {{ old('meeting_style', $group->meeting_style) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('meeting_style')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field">
                    <label for="visibility">Visibility</label>
                    <select id="visibility" name="visibility" data-group-visibility>
                        @foreach ($visibilities as $value => $label)
                            <option value="{{ $value }}" {{ old('visibility', $group->visibility) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('visibility')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field">
                    <label for="join_code">Private Join Code</label>
                    <input id="join_code" name="join_code" type="text" value="{{ old('join_code', $group->join_code) }}" placeholder="Required for private groups">
                    @error('join_code')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-form-actions">
                    <a class="secondary-button" href="{{ route('studyhub.admin.groups') }}">Cancel</a>
                    <button class="action-button" type="submit">Save Group</button>
                </div>
            </form>
        </article>

        <aside class="content-card panel admin-side-panel">
            <h3>Group Snapshot</h3>
            <div class="detail-metric-list">
                <div class="detail-metric-row">
                    <span>Owner</span>
                    <strong>{{ $ownerName }}</strong>
                </div>
                <div class="detail-metric-row">
                    <span>Visibility</span>
                    <strong>{{ ucfirst($group->visibility) }}</strong>
                </div>
                <div class="detail-metric-row">
                    <span>Category</span>
                    <strong>{{ $group->category }}</strong>
                </div>
                <div class="detail-metric-row">
                    <span>Created</span>
                    <strong>{{ $group->created_at?->format('M j, Y') ?? 'Unknown' }}</strong>
                </div>
            </div>
        </aside>
    </section>

    <section class="report-grid mt-6">
        <article class="content-card report-panel">
            <h3>Recent Resources</h3>
            <div class="detail-list">
                @forelse ($recentResources as $resource)
                    <div class="detail-list-row">
                        <div>
                            <strong>{{ $resource->name }}</strong>
                            <span>{{ $resource->category }} by {{ $resource->uploader?->display_name ?: $resource->uploader?->name ?: 'Unknown' }}</span>
                        </div>
                        <form class="action-form" method="POST" action="{{ route('studyhub.admin.resources.delete', $resource) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="{{ route('studyhub.admin.groups.show', $group) }}">
                            <button class="action-chip delete" type="button" data-resource-delete-open data-resource-delete-filename="{{ $resource->name }}">
                                <span class="icon-box">{!! $icons['trash'] !!}</span>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="empty-panel">No resources have been shared in this group.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card report-panel">
            <h3>Recent Discussions</h3>
            <div class="detail-list">
                @forelse ($recentDiscussions as $discussion)
                    <div class="detail-list-row">
                        <strong>{{ $discussion->title }}</strong>
                        <span>{{ \Illuminate\Support\Str::limit($discussion->body, 90) }}</span>
                    </div>
                @empty
                    <div class="empty-panel">No discussions have been started in this group.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="content-card report-panel mt-6">
        <h3>Study Sessions</h3>
        <div class="detail-list">
            @forelse ($upcomingSessions as $session)
                <div class="detail-list-row inline">
                    <div>
                        <strong>{{ $session->title }}</strong>
                        <span>{{ $session->session_date?->format('M j, Y') }} at {{ substr((string) $session->start_time, 0, 5) }} - {{ $session->location }}</span>
                    </div>
                    <form class="action-form" method="POST" action="{{ route('studyhub.admin.sessions.delete', $session) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.admin.groups.show', $group) }}">
                        <button class="action-chip delete" type="button" data-session-delete-open data-session-delete-title="{{ $session->title }}">
                            <span class="icon-box">{!! $icons['trash'] !!}</span>
                            <span>Delete</span>
                        </button>
                    </form>
                </div>
            @empty
                <div class="empty-panel">No study sessions are attached to this group.</div>
            @endforelse
        </div>
    </section>

    <div class="users-delete-modal" data-resource-delete-modal>
        <button class="users-delete-backdrop" type="button" data-resource-delete-close aria-label="Close delete confirmation"></button>

        <div class="users-delete-panel" role="dialog" aria-modal="true" aria-labelledby="resource-delete-title">
            <div class="users-delete-header">
                <span class="users-delete-badge">
                    <span class="icon-box users-delete-icon">{!! $icons['trash'] !!}</span>
                    <span>Confirm Delete</span>
                </span>
                <button class="users-delete-close" type="button" data-resource-delete-close aria-label="Close">&times;</button>
            </div>

            <div class="users-delete-body">
                <h3 class="users-delete-title" id="resource-delete-title">Delete this resource?</h3>
                <p class="users-delete-copy">
                    You are about to remove <span class="users-delete-name" data-resource-delete-target>this resource</span> from StudyHub.
                    The stored file will be deleted too.
                </p>

                <div class="users-delete-note">
                    <span class="icon-box">{!! $icons['shield'] !!}</span>
                    <span>Use this only for outdated, duplicate, or inappropriate resources. This action cannot be undone.</span>
                </div>

                <div class="users-delete-actions">
                    <button class="secondary-button" type="button" data-resource-delete-close>Keep Resource</button>
                    <button class="users-delete-submit" type="button" data-resource-delete-confirm>
                        <span class="icon-box">{!! $icons['trash'] !!}</span>
                        <span>Delete Resource</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="users-delete-modal" data-session-delete-modal>
        <button class="users-delete-backdrop" type="button" data-session-delete-close aria-label="Close delete confirmation"></button>

        <div class="users-delete-panel" role="dialog" aria-modal="true" aria-labelledby="session-delete-title">
            <div class="users-delete-header">
                <span class="users-delete-badge">
                    <span class="icon-box users-delete-icon">{!! $icons['trash'] !!}</span>
                    <span>Confirm Delete</span>
                </span>
                <button class="users-delete-close" type="button" data-session-delete-close aria-label="Close">&times;</button>
            </div>

            <div class="users-delete-body">
                <h3 class="users-delete-title" id="session-delete-title">Delete this study session?</h3>
                <p class="users-delete-copy">
                    You are about to remove <span class="users-delete-name" data-session-delete-target>this session</span> from StudyHub.
                    Existing RSVPs for this session will be removed too.
                </p>

                <div class="users-delete-note">
                    <span class="icon-box">{!! $icons['shield'] !!}</span>
                    <span>Use this for cancelled, duplicate, or inappropriate sessions. This action cannot be undone.</span>
                </div>

                <div class="users-delete-actions">
                    <button class="secondary-button" type="button" data-session-delete-close>Keep Session</button>
                    <button class="users-delete-submit" type="button" data-session-delete-confirm>
                        <span class="icon-box">{!! $icons['trash'] !!}</span>
                        <span>Delete Session</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.querySelector('[data-resource-delete-modal]');
            const deleteButtons = Array.from(document.querySelectorAll('[data-resource-delete-open]'));
            const deleteCloseButtons = document.querySelectorAll('[data-resource-delete-close]');
            const deleteName = document.querySelector('[data-resource-delete-target]');
            const deleteConfirm = document.querySelector('[data-resource-delete-confirm]');
            let pendingDeleteForm = null;

            if (! deleteModal || ! deleteConfirm) {
                return;
            }

            const setDeleteModalOpen = function (isOpen) {
                deleteModal.classList.toggle('is-open', isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);

                if (! isOpen) {
                    pendingDeleteForm = null;
                }
            };

            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    pendingDeleteForm = button.closest('form');

                    if (! pendingDeleteForm) {
                        return;
                    }

                    if (deleteName) {
                        deleteName.textContent = button.dataset.resourceDeleteFilename || 'this resource';
                    }

                    setDeleteModalOpen(true);
                });
            });

            deleteCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setDeleteModalOpen(false);
                });
            });

            deleteConfirm.addEventListener('click', function () {
                if (! pendingDeleteForm) {
                    setDeleteModalOpen(false);
                    return;
                }

                deleteConfirm.disabled = true;
                deleteConfirm.innerHTML = '<span class="icon-box">{!! $icons['trash'] !!}</span><span>Deleting...</span>';
                pendingDeleteForm.submit();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setDeleteModalOpen(false);
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.querySelector('[data-session-delete-modal]');
            const deleteButtons = Array.from(document.querySelectorAll('[data-session-delete-open]'));
            const deleteCloseButtons = document.querySelectorAll('[data-session-delete-close]');
            const deleteName = document.querySelector('[data-session-delete-target]');
            const deleteConfirm = document.querySelector('[data-session-delete-confirm]');
            let pendingDeleteForm = null;

            if (! deleteModal || ! deleteConfirm) {
                return;
            }

            const setDeleteModalOpen = function (isOpen) {
                deleteModal.classList.toggle('is-open', isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);

                if (! isOpen) {
                    pendingDeleteForm = null;
                }
            };

            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    pendingDeleteForm = button.closest('form');

                    if (! pendingDeleteForm) {
                        return;
                    }

                    if (deleteName) {
                        deleteName.textContent = button.dataset.sessionDeleteTitle || 'this session';
                    }

                    setDeleteModalOpen(true);
                });
            });

            deleteCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setDeleteModalOpen(false);
                });
            });

            deleteConfirm.addEventListener('click', function () {
                if (! pendingDeleteForm) {
                    setDeleteModalOpen(false);
                    return;
                }

                deleteConfirm.disabled = true;
                deleteConfirm.innerHTML = '<span class="icon-box">{!! $icons['trash'] !!}</span><span>Deleting...</span>';
                pendingDeleteForm.submit();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setDeleteModalOpen(false);
                }
            });
        });
    </script>
@endsection
