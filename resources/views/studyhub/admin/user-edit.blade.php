@extends('studyhub.admin.layout')

@section('title', 'Edit User')

@section('page')
    @php
        $isCurrentUser = (int) $managedUser->id === (int) auth()->id();
        $displayName = $managedUser->display_name ?: $managedUser->name;
    @endphp

    <div class="toolbar">
        <div>
            <a class="back-link" href="{{ route('studyhub.admin.users') }}">
                <span class="icon-box">{!! $icons['arrow-left'] !!}</span>
                <span>Back to users</span>
            </a>
            <h2 class="page-title">Edit User</h2>
            <p class="page-subtitle">Update account access, contact details, and credentials for {{ $displayName }}.</p>
        </div>
    </div>

    <section class="admin-detail-grid">
        <article class="content-card panel" id="manage-user">
            <h3>Account Details</h3>

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

            <form class="users-form" method="POST" action="{{ route('studyhub.admin.users.update', $managedUser) }}">
                @csrf
                @method('PUT')

                <div class="users-field full">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $displayName) }}">
                    @error('name')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field full">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}">
                    @error('email')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field">
                    <label for="role">Role</label>
                    @if ($isCurrentUser)
                        <input type="hidden" name="role" value="{{ $managedUser->role }}">
                        <select id="role" disabled>
                            <option>{{ ucfirst($managedUser->role) }}</option>
                        </select>
                    @else
                        <select id="role" name="role">
                            <option value="student" {{ old('role', $managedUser->role) === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="admin" {{ old('role', $managedUser->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    @endif
                    @error('role')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field">
                    <label for="password">New Password</label>
                    <input id="password" name="password" type="password" placeholder="Leave blank to keep current password">
                    @error('password')
                        <div class="users-field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="users-field full">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat new password">
                </div>

                <div class="users-form-actions">
                    <a class="secondary-button" href="{{ route('studyhub.admin.users') }}">Cancel</a>
                    <button class="action-button" type="submit">Save Changes</button>
                </div>
            </form>
        </article>

        <aside class="content-card panel admin-side-panel">
            <h3>Account Status</h3>
            <div class="detail-metric-list">
                <div class="detail-metric-row">
                    <span>Role</span>
                    <strong>{{ ucfirst($managedUser->role) }}</strong>
                </div>
                <div class="detail-metric-row">
                    <span>Email</span>
                    <strong>{{ $managedUser->email_verified_at ? 'Verified' : 'Pending' }}</strong>
                </div>
                <div class="detail-metric-row">
                    <span>Joined</span>
                    <strong>{{ $managedUser->created_at?->format('M j, Y') ?? 'Unknown' }}</strong>
                </div>
                <div class="detail-metric-row">
                    <span>Groups</span>
                    <strong>{{ $managedUser->joinedGroups()->count() }}</strong>
                </div>
            </div>

            <div class="danger-zone">
                <h4>Delete Account</h4>
                <p>Deleting an account also removes owned groups and related StudyHub records through the database safeguards.</p>

                @if ($canDelete)
                    <form method="POST" action="{{ route('studyhub.admin.users.delete', $managedUser) }}" onsubmit="return confirm('Delete {{ addslashes($displayName) }} from StudyHub?')">
                        @csrf
                        @method('DELETE')
                        <button class="users-delete-submit" type="submit">
                            <span class="icon-box">{!! $icons['trash'] !!}</span>
                            <span>Delete User</span>
                        </button>
                    </form>
                @else
                    <div class="users-delete-note">
                        <span class="icon-box">{!! $icons['shield'] !!}</span>
                        <span>{{ $deleteBlocker }}</span>
                    </div>
                @endif
            </div>
        </aside>
    </section>
@endsection
