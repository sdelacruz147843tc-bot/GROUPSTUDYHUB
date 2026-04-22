@extends('studyhub.layout')

@section('title', 'StudyHub Login')

@section('content')
    @php
        $activeAuthMode = old('auth_mode', 'login');
        if ($errors->has('name') || $errors->has('password_confirmation')) {
            $activeAuthMode = 'register';
        }
    @endphp

    <div class="studyhub-shell login-shell">
        <div class="login-frame">
            <section class="login-panel">
                <div class="login-brand">
                    <div class="brand-lockup">
                        <span class="brand-logo-mark" aria-hidden="true">
                            <svg viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M48 8 16 22l32 14 32-14L48 8Z" fill="#122D5D"/>
                                <path d="M31 27c0-3 8-8 17-8s17 5 17 8v11H31V27Z" fill="#122D5D"/>
                                <path d="M48 31c2 0 4 2 5 5l4 18H39l4-18c1-3 3-5 5-5Z" fill="#122D5D"/>
                                <path d="M21 44 15 71c10-4 22-6 33-6V50c-9 0-18-2-27-6Z" fill="#1F8BFF"/>
                                <path d="M75 44c-9 4-18 6-27 6v15c11 0 23 2 33 6l-6-27Z" fill="#4CCB68"/>
                                <path d="M48 50v15c-12 0-25 2-37 7l3-13c10-4 22-6 34-6v-3Z" fill="#122D5D"/>
                                <path d="M48 50v15c12 0 25 2 37 7l-3-13c-10-4-22-6-34-6v-3Z" fill="#122D5D"/>
                                <path d="M80 30a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z" fill="#122D5D"/>
                                <path d="M80 35v14" stroke="#122D5D" stroke-width="3" stroke-linecap="round"/>
                                <path d="M80 49c-3 0-4 4-4 7h8c0-3-1-7-4-7Z" fill="#4CCB68"/>
                            </svg>
                        </span>
                        <span class="brand-copy">
                            <span class="brand-wordmark">Study<span>Hub</span></span>
                        </span>
                    </div>
                </div>

                <div class="login-copy">
                    <h1>{{ $activeAuthMode === 'register' ? 'Create account' : 'Welcome back' }}</h1>
                    <p>{{ $activeAuthMode === 'register' ? 'Start your student workspace.' : 'Sign in to continue.' }}</p>
                </div>

                <div class="auth-switch" role="tablist" aria-label="Authentication forms">
                    <button class="auth-switch-button {{ $activeAuthMode === 'login' ? 'is-active' : '' }}" type="button" data-auth-target="login">
                        Login
                    </button>
                    <button class="auth-switch-button {{ $activeAuthMode === 'register' ? 'is-active' : '' }}" type="button" data-auth-target="register">
                        Sign Up
                    </button>
                </div>

                @if (session('status'))
                    <div class="login-status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="login-errors">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div id="login-panel" class="auth-panel {{ $activeAuthMode === 'login' ? 'is-active' : '' }}">
                    <form id="studyhub-login-form" class="login-form" action="{{ route('studyhub.authenticate') }}" method="post">
                        @csrf
                        <input name="auth_mode" type="hidden" value="login">
                        <input id="studyhub-login-role" name="role" type="hidden" value="{{ old('auth_mode') === 'register' ? 'student' : old('role', 'student') }}">

                        <div class="form-group">
                            <label class="form-label" for="login-email">Email</label>
                            <input class="form-input" id="login-email" name="email" type="email" value="{{ old('auth_mode') === 'register' ? '' : old('email') }}" placeholder="you@example.com">
                            @error('email')
                                @if (old('auth_mode', 'login') === 'login' || ! old('auth_mode'))
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="login-password">Password</label>
                            <input class="form-input" id="login-password" name="password" type="password" placeholder="Password">
                            @error('password')
                                @if (old('auth_mode', 'login') === 'login' || ! old('auth_mode'))
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Login as</label>
                            <div class="role-grid">
                                <button class="role-pill {{ (old('auth_mode') === 'register' ? 'student' : old('role', 'student')) === 'student' ? 'is-active' : '' }}" type="button" data-role-target="studyhub-login-role" data-role="student">
                                    Student
                                </button>
                                <button class="role-pill {{ old('auth_mode') !== 'register' && old('role') === 'admin' ? 'is-active' : '' }}" type="button" data-role-target="studyhub-login-role" data-role="admin">
                                    Admin
                                </button>
                            </div>
                            @error('role')
                                @if (old('auth_mode', 'login') === 'login' || ! old('auth_mode'))
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="password-row">
                            <label class="show-password" for="show-login-password">
                                <input id="show-login-password" type="checkbox" data-password-target="login-password">
                                <span>Show password</span>
                            </label>
                        </div>

                        <button class="login-submit" type="submit">Login</button>
                    </form>

                    <div class="login-footer">
                        No account?
                        <a href="#" data-auth-target="register">Create one.</a>
                    </div>
                </div>

                <div id="register-panel" class="auth-panel {{ $activeAuthMode === 'register' ? 'is-active' : '' }}">
                    <form id="studyhub-register-form" class="login-form" action="{{ route('studyhub.register') }}" method="post">
                        @csrf
                        <input name="auth_mode" type="hidden" value="register">
                        <input id="studyhub-register-role" name="role" type="hidden" value="student">

                        <div class="form-group">
                            <label class="form-label" for="register-name">Full name</label>
                            <input class="form-input" id="register-name" name="name" type="text" value="{{ old('name') }}" placeholder="Your name">
                            @error('name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="register-email">Email</label>
                            <input class="form-input" id="register-email" name="email" type="email" value="{{ old('auth_mode') === 'register' ? old('email') : '' }}" placeholder="you@example.com">
                            @error('email')
                                @if (old('auth_mode') === 'register')
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="form-grid-two">
                            <div class="form-group">
                                <label class="form-label" for="register-password">Password</label>
                                <input class="form-input" id="register-password" name="password" type="password" placeholder="Min. 8 characters">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="register-password-confirmation">Confirm password</label>
                                <input class="form-input" id="register-password-confirmation" name="password_confirmation" type="password" placeholder="Repeat password">
                            </div>
                        </div>
                        @error('password')
                            @if (old('auth_mode') === 'register')
                                <div class="form-error">{{ $message }}</div>
                            @endif
                        @enderror
                        @error('password_confirmation')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        <div class="password-row">
                            <label class="show-password" for="show-register-password">
                                <input id="show-register-password" type="checkbox" data-password-target="register-password">
                                <span>Show password</span>
                            </label>
                            <label class="show-password" for="show-register-confirmation">
                                <input id="show-register-confirmation" type="checkbox" data-password-target="register-password-confirmation">
                                <span>Show confirm</span>
                            </label>
                        </div>

                        <button class="login-submit" type="submit">Sign Up</button>
                    </form>

                    <div class="login-footer">
                        Have an account?
                        <a href="#" data-auth-target="login">Back to login.</a>
                    </div>
                </div>
            </section>

            <section class="login-visual" aria-hidden="true">
                <div class="visual-card">
                    <div class="visual-window">
                        <img class="visual-image" src="{{ asset('studyhub-login-illustration.png') }}" alt="StudyHub learning illustration">
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const authButtons = document.querySelectorAll('[data-auth-target]');
            const authPanels = {
                login: document.getElementById('login-panel'),
                register: document.getElementById('register-panel'),
            };
            const loginTitle = document.querySelector('.login-copy h1');
            const loginSubtitle = document.querySelector('.login-copy p');
            const roleButtons = document.querySelectorAll('.role-pill[data-role]');
            const passwordToggles = document.querySelectorAll('[data-password-target]');

            function setAuthMode(mode) {
                Object.entries(authPanels).forEach(function (entry) {
                    const panelMode = entry[0];
                    const panel = entry[1];

                    if (!panel) {
                        return;
                    }

                    panel.classList.toggle('is-active', panelMode === mode);
                });

                authButtons.forEach(function (button) {
                    button.classList.toggle('is-active', button.dataset.authTarget === mode);
                });

                if (loginTitle) {
                    loginTitle.textContent = mode === 'register' ? 'Create Account' : 'StudyHub Login';
                }

                if (loginSubtitle) {
                    loginSubtitle.textContent = mode === 'register' ? 'Set up your workspace.' : 'Sign in to continue.';
                }
            }

            authButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    setAuthMode(button.dataset.authTarget || 'login');
                });
            });

            roleButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId = button.dataset.roleTarget;
                    const targetInput = targetId ? document.getElementById(targetId) : null;

                    if (!targetInput) {
                        return;
                    }

                    document.querySelectorAll('.role-pill[data-role-target="' + targetId + '"]').forEach(function (item) {
                        item.classList.remove('is-active');
                    });

                    button.classList.add('is-active');
                    targetInput.value = button.dataset.role || 'student';
                });
            });

            passwordToggles.forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const target = document.getElementById(toggle.dataset.passwordTarget || '');

                    if (!target) {
                        return;
                    }

                    target.type = toggle.checked ? 'text' : 'password';
                });
            });

            setAuthMode('{{ $activeAuthMode }}');
        });
    </script>
@endsection

