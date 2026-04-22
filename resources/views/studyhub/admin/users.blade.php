@extends('studyhub.admin.layout')

@section('title', 'User Management')

@section('page')
    @php
        $showCreateModal = $errors->has('name') || $errors->has('email') || $errors->has('role') || $errors->has('password');
    @endphp

    <div class="toolbar">
        <div>
            <h2 class="page-title">User Management</h2>
            <p class="page-subtitle">Manage and monitor user accounts.</p>
        </div>
        <div class="toolbar-actions">
            <button class="action-button" type="button" data-open-users-modal>
                <span class="icon-box">{!! $icons['users'] !!}</span>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <div class="toolbar" style="margin-top:-8px;">
        <div class="toolbar-filters" style="width:100%;">
            <div class="search-box">
                <span class="icon-box">{!! $icons['search'] !!}</span>
                <input type="text" placeholder="Search users..." data-user-search>
            </div>
            <button class="secondary-button" type="button" data-user-filter-toggle>Filter</button>
        </div>
    </div>

    <div class="users-filter-panel" data-user-filter-panel>
        <select class="users-filter-select" data-user-role>
            <option value="">All Roles</option>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>

        <select class="users-filter-select" data-user-status>
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <section class="stats-grid">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <div style="color:var(--text-muted);margin-bottom:8px;">{{ $stat['label'] }}</div>
                <div style="font-size:2rem;font-weight:800;color:{{ $stat['color'] }};">{{ $stat['value'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="content-card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Groups</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr
                        class="user-row"
                        data-user-row
                        data-name="{{ strtolower($user['name']) }}"
                        data-email="{{ strtolower($user['email']) }}"
                        data-role="{{ strtolower($user['role']) }}"
                        data-status="{{ strtolower($user['status']) }}"
                    >
                        <td><strong>{{ $user['name'] }}</strong></td>
                        <td>{{ $user['email'] }}</td>
                        <td><span class="user-pill">{{ $user['role'] }}</span></td>
                        <td>{{ $user['groups'] }}</td>
                        <td><span class="status-pill {{ strtolower($user['status']) }}">{{ $user['status'] }}</span></td>
                        <td>{{ $user['join_date'] }}</td>
                        <td>
                            <div class="actions-row">
                                <a class="action-link action-chip" href="mailto:{{ $user['email'] }}" title="Email {{ $user['name'] }}">
                                    <span class="icon-box">{!! $icons['mail'] !!}</span>
                                    <span>Email</span>
                                </a>
                                <span class="action-chip role" title="Current role">
                                    <span class="icon-box">{!! $icons['shield'] !!}</span>
                                    <span>{{ $user['role'] }}</span>
                                </span>
                                @if ($user['can_delete'])
                                    <form class="action-form" method="POST" action="{{ route('studyhub.admin.users.delete', $user['id']) }}">
                                        @csrf
                                        <button
                                            class="action-chip delete"
                                            type="button"
                                            data-open-delete-modal
                                            data-user-name="{{ $user['name'] }}"
                                        >
                                            <span class="icon-box">{!! $icons['ban'] !!}</span>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="action-chip delete disabled" title="You cannot delete your own account here">
                                        <span class="icon-box">{!! $icons['ban'] !!}</span>
                                        <span>Delete</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="users-empty-state" data-users-empty>
            No users match your current search or filters.
        </div>
    </section>

    <div class="users-create-modal {{ $showCreateModal ? 'is-open' : '' }}" data-users-modal>
        <button class="users-create-backdrop" type="button" data-close-users-modal aria-label="Close add user form"></button>

        <div class="users-create-panel">
            <div class="users-create-header">
                <div>
                    <h3>Create User Account</h3>
                    <p>Add a new student or admin account directly from user management.</p>
                </div>
                <button class="users-create-close" type="button" data-close-users-modal aria-label="Close">&times;</button>
            </div>

            <div class="users-create-body">
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

                <form class="users-form" method="POST" action="{{ route('studyhub.admin.users.store') }}">
                    @csrf

                    <div class="users-field full">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Jordan Reyes">
                        @error('name')
                            <div class="users-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="users-field full">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="jordan@studyhub.test">
                        @error('email')
                            <div class="users-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="users-field">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <div class="users-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="users-field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="At least 8 characters">
                        @error('password')
                            <div class="users-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="users-field full">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat password">
                    </div>

                    <div class="users-form-actions">
                        <button class="secondary-button" type="button" data-close-users-modal>Cancel</button>
                        <button class="action-button" type="submit">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="users-delete-modal" data-users-delete-modal>
        <button class="users-delete-backdrop" type="button" data-close-delete-modal aria-label="Close delete confirmation"></button>

        <div class="users-delete-panel" role="dialog" aria-modal="true" aria-labelledby="users-delete-title">
            <div class="users-delete-header">
                <span class="users-delete-badge">
                    <span class="icon-box users-delete-icon">{!! $icons['ban'] !!}</span>
                    <span>Confirm Delete</span>
                </span>
                <button class="users-delete-close" type="button" data-close-delete-modal aria-label="Close">&times;</button>
            </div>

            <div class="users-delete-body">
                <h3 class="users-delete-title" id="users-delete-title">Delete this user account?</h3>
                <p class="users-delete-copy">
                    You are about to remove <span class="users-delete-name" data-delete-user-name>this user</span> from StudyHub.
                    This action permanently deletes their access and cannot be undone.
                </p>

                <div class="users-delete-note">
                    <span class="icon-box">{!! $icons['shield'] !!}</span>
                    <span>Use delete only for accounts you are sure should be removed. Existing safeguards for your own account and the last admin still apply.</span>
                </div>

                <div class="users-delete-actions">
                    <button class="secondary-button" type="button" data-close-delete-modal>Keep User</button>
                    <button class="users-delete-submit" type="button" data-confirm-delete>
                        <span class="icon-box">{!! $icons['ban'] !!}</span>
                        <span>Delete User</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-users-modal]');
            const openButton = document.querySelector('[data-open-users-modal]');
            const closeButtons = document.querySelectorAll('[data-close-users-modal]');
            const deleteModal = document.querySelector('[data-users-delete-modal]');
            const deleteButtons = Array.from(document.querySelectorAll('[data-open-delete-modal]'));
            const deleteCloseButtons = document.querySelectorAll('[data-close-delete-modal]');
            const deleteName = document.querySelector('[data-delete-user-name]');
            const deleteConfirm = document.querySelector('[data-confirm-delete]');
            const searchInput = document.querySelector('[data-user-search]');
            const filterToggle = document.querySelector('[data-user-filter-toggle]');
            const filterPanel = document.querySelector('[data-user-filter-panel]');
            const roleSelect = document.querySelector('[data-user-role]');
            const statusSelect = document.querySelector('[data-user-status]');
            const rows = Array.from(document.querySelectorAll('[data-user-row]'));
            const emptyState = document.querySelector('[data-users-empty]');
            let pendingDeleteForm = null;

            if (!modal || !openButton || !deleteModal || !deleteConfirm) {
                return;
            }

            openButton.addEventListener('click', function () {
                modal.classList.add('is-open');
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    modal.classList.remove('is-open');
                });
            });

            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    pendingDeleteForm = button.closest('form');

                    if (!pendingDeleteForm) {
                        return;
                    }

                    if (deleteName) {
                        deleteName.textContent = button.dataset.userName || 'this user';
                    }

                    deleteModal.classList.add('is-open');
                });
            });

            deleteCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    deleteModal.classList.remove('is-open');
                    pendingDeleteForm = null;
                });
            });

            deleteConfirm.addEventListener('click', function () {
                if (!pendingDeleteForm) {
                    deleteModal.classList.remove('is-open');
                    return;
                }

                pendingDeleteForm.submit();
            });

            filterToggle?.addEventListener('click', function () {
                filterPanel?.classList.toggle('is-open');
            });

            const applyUserFilters = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const role = (roleSelect?.value || '').trim().toLowerCase();
                const status = (statusSelect?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchesSearch = searchTerm === ''
                        || row.dataset.name.includes(searchTerm)
                        || row.dataset.email.includes(searchTerm)
                        || row.dataset.role.includes(searchTerm);
                    const matchesRole = role === '' || row.dataset.role === role;
                    const matchesStatus = status === '' || row.dataset.status === status;
                    const isVisible = matchesSearch && matchesRole && matchesStatus;

                    row.classList.toggle('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            };

            searchInput?.addEventListener('input', applyUserFilters);
            roleSelect?.addEventListener('change', applyUserFilters);
            statusSelect?.addEventListener('change', applyUserFilters);
            applyUserFilters();
        });
    </script>
@endsection

