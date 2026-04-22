@extends('studyhub.layout')

@section('title', 'StudyHub Login')

@push('styles')
    <style>
        @keyframes loginFloat {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes loginFadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-shell {
            display: grid;
            grid-template-columns: minmax(360px, 1.05fr) minmax(360px, 0.95fr);
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(227, 179, 123, 0.18), transparent 28%),
                linear-gradient(180deg, #f7f1ea 0%, #eef3ed 100%);
        }

        .login-brand {
            position: relative;
            overflow: hidden;
            padding: 56px 42px 48px;
            color: white;
            background:
                radial-gradient(circle at top, rgba(255,255,255,0.15), transparent 36%),
                linear-gradient(135deg, #22553b 0%, #2f7c56 42%, #5fb981 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .login-brand::before,
        .login-brand::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(50px);
            opacity: 0.22;
        }

        .login-brand::before {
            width: 260px;
            height: 260px;
            background: rgba(255,255,255,0.8);
            top: 34px;
            right: -40px;
        }

        .login-brand::after {
            width: 320px;
            height: 320px;
            background: #9fe0b9;
            left: -60px;
            bottom: -40px;
        }

        .login-brand-content,
        .login-feature-list,
        .login-brand-stats,
        .login-brand-topbar {
            position: relative;
            z-index: 1;
        }

        .login-brand-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 48px;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .brand-status {
            color: rgba(247,255,250,0.84);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .login-brand h1 {
            margin: 0;
            font-size: 4rem;
            line-height: 0.95;
            font-weight: 800;
            letter-spacing: -0.06em;
            max-width: 520px;
        }

        .login-brand p {
            max-width: 420px;
            margin: 20px 0 0;
            font-size: 1.12rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.92);
        }

        .login-brand-copy {
            animation: loginFadeUp 520ms ease both;
        }

        .login-brand-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 26px;
            max-width: 540px;
        }

        .brand-stat {
            padding: 14px 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(6px);
        }

        .brand-stat-value {
            margin: 0 0 4px;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .brand-stat-label {
            color: rgba(245,255,248,0.8);
            font-size: 0.88rem;
        }

        .login-feature-list {
            display: grid;
            gap: 18px;
            max-width: 500px;
            animation: loginFadeUp 620ms ease both;
            animation-delay: 120ms;
        }

        .login-feature {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 16px;
            border-radius: 22px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(8px);
        }

        .login-feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
            flex-shrink: 0;
        }

        .login-feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .login-feature-copy {
            margin: 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.95rem;
        }

        .login-panel {
            background:
                radial-gradient(circle at top right, rgba(123, 190, 149, 0.12), transparent 22%),
                linear-gradient(180deg, #f7f3ed 0%, #f1f5f1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
        }

        .login-card {
            width: min(100%, 470px);
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(198, 214, 203, 0.9);
            border-radius: 30px;
            box-shadow: 0 28px 56px rgba(41, 67, 88, 0.16);
            padding: 34px 30px 30px;
            animation: loginFadeUp 520ms ease both;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #65c889 0%, #82c6de 100%);
        }

        .login-card-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .login-card-topline span {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: #eef6f0;
            border: 1px solid #d5e4da;
            color: #476553;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .login-card-copy {
            margin-bottom: 26px;
        }

        .login-card h2 {
            margin: 0 0 8px;
            color: #111;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .login-card-copy p {
            margin: 0;
            color: #6b7673;
            line-height: 1.55;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.92rem;
            font-weight: 700;
            color: #3f454b;
        }

        .form-input {
            width: 100%;
            height: 52px;
            border-radius: 16px;
            border: 1px solid #cfddd5;
            background: rgba(255,255,255,0.96);
            padding: 0 16px;
            font: inherit;
            color: #1a2332;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
        }

        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .role-pill {
            height: 52px;
            border-radius: 16px;
            border: 1px solid #d2ddd7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a535a;
            font-weight: 700;
            background: rgba(255,255,255,0.92);
            cursor: pointer;
            transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .role-pill.is-active {
            background: linear-gradient(135deg, #65c889 0%, #4ab872 100%);
            border-color: transparent;
            color: white;
            box-shadow: 0 14px 26px rgba(99, 187, 122, 0.26);
        }

        .login-submit {
            width: 100%;
            height: 54px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #65c889 0%, #4ab872 100%);
            color: white;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 16px 28px rgba(99, 187, 122, 0.24);
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            color: #7f8585;
            font-size: 0.95rem;
        }

        .login-footer a {
            color: #3d9c5d;
            font-weight: 700;
        }

        .login-status,
        .login-errors {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 0.94rem;
            line-height: 1.5;
        }

        .login-status {
            background: #eef8f1;
            border: 1px solid #cfe7d5;
            color: #2f6a41;
        }

        .login-errors {
            background: #fff1f0;
            border: 1px solid #f0c8c3;
            color: #9a3b33;
        }

        .form-error {
            margin-top: 8px;
            color: #b2453d;
            font-size: 0.84rem;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-brand {
                min-height: 460px;
                padding: 40px 24px 28px;
            }

            .login-brand h1 {
                font-size: 3rem;
            }

            .login-brand-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $icons = [
            'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'book' => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>',
        ];
    @endphp

    <div class="studyhub-shell login-shell">
        <section class="login-brand">
            <div class="login-brand-topbar">
                <span class="brand-pill">StudyHub Portal</span>
                <span class="brand-status">Student + Admin access</span>
            </div>

            <div class="login-brand-content">
                <div class="login-brand-copy">
                    <h1>Study smarter, together.</h1>
                    <p>StudyHub brings groups, shared resources, discussions, and sessions into one focused learning workspace.</p>
                </div>

                <div class="login-brand-stats">
                    <div class="brand-stat">
                        <div class="brand-stat-value">24/7</div>
                        <div class="brand-stat-label">Access to shared materials</div>
                    </div>
                    <div class="brand-stat">
                        <div class="brand-stat-value">+89</div>
                        <div class="brand-stat-label">Active study groups</div>
                    </div>
                    <div class="brand-stat">
                        <div class="brand-stat-value">Live</div>
                        <div class="brand-stat-label">Discussions and sessions</div>
                    </div>
                </div>
            </div>

            <div class="login-feature-list">
                @foreach ($features as $feature)
                    <div class="login-feature">
                        <div class="icon-box login-feature-icon">{!! $icons[$feature['icon']] !!}</div>
                        <div>
                            <h2 class="login-feature-title">{{ $feature['title'] }}</h2>
                            <p class="login-feature-copy">{{ $feature['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <div class="login-card-topline">
                    <span>Secure Sign In</span>
                    <span>Campus Workspace</span>
                </div>

                <div class="login-card-copy">
                    <h2>Welcome Back</h2>
                    <p>Choose your role, then sign in to continue into your StudyHub workspace.</p>
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

                <form id="studyhub-login-form" action="{{ route('studyhub.authenticate') }}" method="post">
                    @csrf
                    <input id="studyhub-login-role" name="role" type="hidden" value="{{ old('role', 'student') }}">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="student@studyhub.test">
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input" id="password" name="password" type="password" placeholder="password">
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Login as</label>
                        <div class="role-grid">
                            <button class="role-pill {{ old('role', 'student') === 'student' ? 'is-active' : '' }}" type="button" data-role="student">
                                Student
                            </button>
                            <button class="role-pill {{ old('role') === 'admin' ? 'is-active' : '' }}" type="button" data-role="admin">
                                Admin
                            </button>
                        </div>
                        @error('role')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="login-submit" type="submit">Sign In</button>
                </form>

                <div class="login-footer">
                    Don't have an account?
                    <a href="{{ url('/register') }}">Sign up</a>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('studyhub-login-form');
            const roleInput = document.getElementById('studyhub-login-role');
            const roleButtons = document.querySelectorAll('.role-pill[data-role]');

            if (!form || !roleInput || !roleButtons.length) {
                return;
            }

            roleButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        roleButtons.forEach(function (item) {
                            item.classList.remove('is-active');
                    });

                    button.classList.add('is-active');
                    roleInput.value = button.dataset.role || 'student';
                });
            });
        });
    </script>
@endsection
