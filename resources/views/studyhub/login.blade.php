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
        <main class="login-frame" aria-label="StudyHub authentication">
            <section class="login-feature-panel" aria-label="StudyHub features">
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
                        <span class="brand-wordmark">Study<span>Hub</span></span>
                    </div>
                </div>

                <div class="feature-copy">
                    <h1>Everything you need in one place.</h1>
                    <p>Upload, share, download, and collaborate with ease.</p>
                </div>

                <div class="quick-access-list" aria-label="Quick access features">
                    <article class="quick-access-card">
                        <span class="quick-access-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 5v10"/><path d="m8 9 4-4 4 4"/><path d="M5 19h14"/></svg>
                        </span>
                        <span>
                            <strong>Upload your notes</strong>
                            <small>Share learning materials</small>
                        </span>
                    </article>
                    <article class="quick-access-card">
                        <span class="quick-access-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 4v10"/><path d="m8 10 4 4 4-4"/><path d="M5 20h14"/></svg>
                        </span>
                        <span>
                            <strong>Download resources</strong>
                            <small>Access quality reviewers</small>
                        </span>
                    </article>
                    <article class="quick-access-card">
                        <span class="quick-access-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M16 11a4 4 0 1 0-8 0"/><path d="M3 20a7 7 0 0 1 14 0"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>
                        </span>
                        <span>
                            <strong>Join study groups</strong>
                            <small>Collaborate with peers</small>
                        </span>
                    </article>
                    <article class="quick-access-card">
                        <span class="quick-access-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                        </span>
                        <span>
                            <strong>Get notifications</strong>
                            <small>Stay updated</small>
                        </span>
                    </article>
                </div>
            </section>

            <section class="login-panel">
                <div class="login-copy">
                    <h2>{{ $activeAuthMode === 'register' ? 'Create account' : 'Welcome back!' }}</h2>
                    <p>{{ $activeAuthMode === 'register' ? 'Start your student workspace.' : 'Sign in to continue.' }}</p>
                </div>

                <div class="auth-switch" role="tablist" aria-label="Authentication forms">
                    <button class="auth-switch-button {{ $activeAuthMode === 'login' ? 'is-active' : '' }}" type="button" data-auth-target="login">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 10 8-5 8 5-8 5-8-5Z"/><path d="M6 12v5c2 1 4 2 6 2s4-1 6-2v-5"/></svg>
                        Login
                    </button>
                    <button class="auth-switch-button {{ $activeAuthMode === 'register' ? 'is-active' : '' }}" type="button" data-auth-target="register">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3 8 8 9 5-1 8-4 8-9V6l-8-3Z"/><path d="M9 12h6"/></svg>
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
                        <input name="login_theme" type="hidden" value="light" data-login-theme-input>
                        <input id="studyhub-login-role" name="role" type="hidden" value="{{ old('auth_mode') === 'register' ? 'student' : old('role', 'student') }}">

                        <div class="form-group">
                            <label class="form-label" for="login-email">Email address</label>
                            <span class="input-shell">
                                <span class="input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                                </span>
                                <input class="form-input" id="login-email" name="email" type="email" value="{{ old('auth_mode') === 'register' ? '' : old('email') }}" placeholder="you@example.com">
                            </span>
                            @error('email')
                                @if (old('auth_mode', 'login') === 'login' || ! old('auth_mode'))
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="login-password">Password</label>
                            <span class="input-shell">
                                <span class="input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                </span>
                                <input class="form-input" id="login-password" name="password" type="password" placeholder="Enter your password">
                                <label class="password-eye" for="show-login-password" title="Show password">
                                    <input id="show-login-password" type="checkbox" data-password-target="login-password">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </label>
                            </span>
                            @error('password')
                                @if (old('auth_mode', 'login') === 'login' || ! old('auth_mode'))
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="login-options-row">
                            <label class="remember-check" for="remember-login">
                                <input id="remember-login" name="remember" type="checkbox" value="1">
                                <span>Remember me</span>
                            </label>
                            <a href="{{ route('password.request') }}">Forgot password?</a>
                        </div>

                        <div class="form-group role-group">
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

                        <button class="login-submit" type="submit">
                            Login
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </button>
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
                        <input name="login_theme" type="hidden" value="light" data-login-theme-input>
                        <input id="studyhub-register-role" name="role" type="hidden" value="student">

                        <div class="form-group">
                            <label class="form-label" for="register-name">Full name</label>
                            <span class="input-shell">
                                <span class="input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                </span>
                                <input class="form-input" id="register-name" name="name" type="text" value="{{ old('name') }}" placeholder="Your name">
                            </span>
                            @error('name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="register-email">Email address</label>
                            <span class="input-shell">
                                <span class="input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                                </span>
                                <input class="form-input" id="register-email" name="email" type="email" value="{{ old('auth_mode') === 'register' ? old('email') : '' }}" placeholder="you@example.com">
                            </span>
                            @error('email')
                                @if (old('auth_mode') === 'register')
                                    <div class="form-error">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>

                        <div class="form-grid-two">
                            <div class="form-group">
                                <label class="form-label" for="register-password">Password</label>
                                <span class="input-shell">
                                    <span class="input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                    </span>
                                    <input class="form-input" id="register-password" name="password" type="password" placeholder="Min. 8 characters">
                                </span>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="register-password-confirmation">Confirm password</label>
                                <span class="input-shell">
                                    <span class="input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24"><path d="M20 7 9 18l-5-5"/></svg>
                                    </span>
                                    <input class="form-input" id="register-password-confirmation" name="password_confirmation" type="password" placeholder="Repeat password">
                                </span>
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

                        <button class="login-submit" type="submit">
                            Sign Up
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </button>
                    </form>

                    <div class="login-footer">
                        Have an account?
                        <a href="#" data-auth-target="login">Back to login.</a>
                    </div>
                </div>
            </section>

            <aside class="login-illustration" aria-hidden="true">
                <button class="theme-toggle-pill" type="button" data-login-theme-toggle aria-pressed="false">
                    <span data-login-theme-label>Light</span>
                    <i></i>
                </button>
                <img class="login-hero-image" src="{{ asset('images/login.png') }}" alt="">
            </aside>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const authButtons = document.querySelectorAll('[data-auth-target]');
            const authPanels = {
                login: document.getElementById('login-panel'),
                register: document.getElementById('register-panel'),
            };
            const loginTitle = document.querySelector('.login-copy h2');
            const loginSubtitle = document.querySelector('.login-copy p');
            const roleButtons = document.querySelectorAll('.role-pill[data-role]');
            const passwordToggles = document.querySelectorAll('[data-password-target]');
            const loginShell = document.querySelector('.login-shell');
            const themeToggle = document.querySelector('[data-login-theme-toggle]');
            const themeLabel = document.querySelector('[data-login-theme-label]');
            const themeInputs = document.querySelectorAll('[data-login-theme-input]');
            const themeStorageKey = 'studyhub-login-theme';

            function setLoginTheme(theme) {
                const isDark = theme === 'dark';

                if (loginShell) {
                    loginShell.classList.toggle('login-theme-dark', isDark);
                }

                if (themeToggle) {
                    themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                    themeToggle.title = isDark ? 'Switch to light mode' : 'Switch to dark mode';
                }

                if (themeLabel) {
                    themeLabel.textContent = isDark ? 'Dark' : 'Light';
                }

                themeInputs.forEach(function (input) {
                    input.value = isDark ? 'dark' : 'light';
                });

                try {
                    localStorage.setItem(themeStorageKey, isDark ? 'dark' : 'light');
                } catch (error) {
                    // Some browsers block localStorage in private contexts.
                }
            }

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
                    loginTitle.textContent = mode === 'register' ? 'Create account' : 'Welcome back!';
                }

                if (loginSubtitle) {
                    loginSubtitle.textContent = mode === 'register' ? 'Start your student workspace.' : 'Sign in to continue.';
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

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    const nextTheme = loginShell && loginShell.classList.contains('login-theme-dark') ? 'light' : 'dark';
                    setLoginTheme(nextTheme);
                });
            }

            let storedTheme = 'light';
            try {
                storedTheme = localStorage.getItem(themeStorageKey) || 'light';
            } catch (error) {
                storedTheme = 'light';
            }

            setLoginTheme(storedTheme === 'dark' ? 'dark' : 'light');
            setAuthMode('{{ $activeAuthMode }}');
        });
    </script>
@endsection
