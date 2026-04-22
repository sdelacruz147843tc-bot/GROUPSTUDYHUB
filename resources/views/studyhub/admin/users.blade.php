@extends('studyhub.admin.layout')

@section('title', 'User Management')

@push('page-styles')
    <style>
        .users-create-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 28px;
            z-index: 50;
        }

        .users-create-modal.is-open {
            display: flex;
        }

        .users-create-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 20, 26, 0.5);
            backdrop-filter: blur(6px);
            cursor: pointer;
        }

        .users-create-panel {
            position: relative;
            z-index: 1;
            width: min(620px, 100%);
            border-radius: 24px;
            background: white;
            border: 1px solid #dbe5ec;
            box-shadow: 0 28px 60px rgba(33, 46, 62, 0.2);
            overflow: hidden;
        }

        .users-create-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px 18px;
            background: linear-gradient(135deg, #edf4ef 0%, #f6f8fb 100%);
            border-bottom: 1px solid #e2eaf0;
        }

        .users-create-header h3 {
            margin: 0 0 6px;
            font-size: 1.5rem;
            font-weight: 800;
            color: #1d2d3a;
        }

        .users-create-header p {
            margin: 0;
            color: #60707d;
        }

        .users-create-close {
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 12px;
            background: #edf1f5;
            color: #33404a;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .users-create-body {
            padding: 22px 24px 24px;
        }

        .users-status,
        .users-errors {
            margin-bottom: 18px;
            padding: 13px 15px;
            border-radius: 16px;
            font-size: 0.94rem;
        }

        .users-status {
            border: 1px solid #d3e5d7;
            background: #edf8f0;
            color: #2f6a41;
        }

        .users-errors {
            border: 1px solid #efd0ca;
            background: #fff3f1;
            color: #9a3b33;
        }

        .users-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .users-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .users-field {
            display: grid;
            gap: 7px;
        }

        .users-field.full {
            grid-column: 1 / -1;
        }

        .users-field label {
            font-size: 0.92rem;
            font-weight: 700;
            color: #40505d;
        }

        .users-field input,
        .users-field select {
            width: 100%;
            height: 50px;
            border-radius: 14px;
            border: 1px solid #d6e0e7;
            background: #fbfcfd;
            padding: 0 14px;
            font: inherit;
            color: #1f2c37;
        }

        .users-form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 4px;
        }

        .users-field-error {
            color: #b2453d;
            font-size: 0.83rem;
            font-weight: 600;
        }

        .users-filter-panel {
            display: none;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin: 0 0 18px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid #dbe5ec;
            background: #fff;
            box-shadow: 0 14px 26px rgba(80, 111, 95, 0.08);
        }

        .users-filter-panel.is-open {
            display: flex;
        }

        .users-filter-select {
            min-width: 180px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid #d6e0e7;
            background: #fbfcfd;
            padding: 0 14px;
            font: inherit;
            color: #1f2c37;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 16px 18px;
            text-align: left;
            border-bottom: 1px solid #e7edf3;
        }

        th {
            font-size: 0.92rem;
            color: var(--text-muted);
        }

        .user-pill,
        .status-pill {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
        }

        .user-pill {
            background: #eef6ff;
            color: #4d6e93;
        }

        .status-pill.active {
            background: #eaf7ee;
            color: var(--green-main);
        }

        .status-pill.inactive {
            background: #f6ece8;
            color: #c45d40;
        }

        .actions-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-link,
        .action-form {
            margin: 0;
        }

        .action-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            border: 1px solid #dbe5ec;
            background: #edf1f5;
            color: #33404a;
            font-size: 0.82rem;
            font-weight: 700;
            gap: 8px;
        }

        .action-chip.role {
            background: #f4f7fb;
            color: #4d6e93;
        }

        .action-chip.delete {
            border-color: #efc9c2;
            background: #fff3f1;
            color: #9a3b33;
            cursor: pointer;
        }

        .action-chip.disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .action-chip .icon-box {
            width: 14px;
            height: 14px;
        }

        .user-row.is-hidden {
            display: none;
        }

        .users-empty-state {
            display: none;
            padding: 24px;
            color: #60707d;
            text-align: center;
            font-weight: 600;
        }

        .users-delete-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 28px;
            z-index: 70;
        }

        .users-delete-modal.is-open {
            display: flex;
        }

        .users-delete-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background:
                radial-gradient(circle at top, rgba(58, 138, 255, 0.12), transparent 40%),
                rgba(9, 14, 24, 0.62);
            backdrop-filter: blur(10px);
            cursor: pointer;
        }

        .users-delete-panel {
            position: relative;
            z-index: 1;
            width: min(520px, 100%);
            border-radius: 28px;
            border: 1px solid rgba(219, 229, 236, 0.9);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 252, 0.98) 100%);
            box-shadow: 0 36px 90px rgba(12, 22, 35, 0.32);
            overflow: hidden;
        }

        .users-delete-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 24px 16px;
        }

        .users-delete-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #fff1ef;
            color: #a63f34;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .users-delete-icon {
            width: 18px;
            height: 18px;
        }

        .users-delete-close {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 14px;
            background: #edf1f5;
            color: #33404a;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .users-delete-body {
            padding: 0 24px 24px;
        }

        .users-delete-title {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            color: #16222d;
        }

        .users-delete-copy {
            margin: 12px 0 0;
            color: #5a6a78;
            line-height: 1.6;
            font-size: 0.98rem;
        }

        .users-delete-name {
            color: #1d2d3a;
            font-weight: 800;
        }

        .users-delete-note {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-top: 20px;
            padding: 16px 18px;
            border-radius: 18px;
            background: #f5f8fb;
            border: 1px solid #dbe5ec;
            color: #51616d;
            font-size: 0.92rem;
        }

        .users-delete-note .icon-box {
            flex: 0 0 auto;
            color: #6d7d88;
        }

        .users-delete-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .users-delete-submit {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 18px;
            border: 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #d95c4f 0%, #bb3d35 100%);
            box-shadow: 0 18px 32px rgba(185, 61, 53, 0.24);
            color: #fff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        @media (max-width: 640px) {
            .users-delete-header,
            .users-delete-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .users-delete-title {
                font-size: 1.45rem;
            }

            .users-delete-actions {
                flex-direction: column-reverse;
            }

            .users-delete-actions .secondary-button,
            .users-delete-submit {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

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
