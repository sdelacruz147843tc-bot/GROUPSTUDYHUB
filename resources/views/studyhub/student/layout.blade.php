@extends('studyhub.layout')

@php
    $icons = [
        'home' => '<svg viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/></svg>',
        'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'resources' => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'library' => '<svg viewBox="0 0 24 24"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H9l2 2h7.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5v-9Z"/><path d="M3 10h18"/></svg>',
        'discussion' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>',
        'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
        'message' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'bell' => '<svg viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>',
        'lock' => '<svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>',
        'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
        'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
        'upload-cloud' => '<svg viewBox="0 0 24 24"><path d="M16 16l-4-4-4 4"/><path d="M12 12v9"/><path d="M20.4 18.5A5 5 0 0 0 18 9h-1.3A8 8 0 1 0 4 16.3"/></svg>',
        'download' => '<svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>',
        'trash' => '<svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>',
        'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>',
        'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24"><path d="M12 3l1.7 2.7 3.1.5.5 3.1L20 11l-2.7 1.7-.5 3.1-3.1.5L12 19l-1.7-2.7-3.1-.5-.5-3.1L4 11l2.7-1.7.5-3.1 3.1-.5L12 3z"/><circle cx="12" cy="11" r="3"/></svg>',
        'trend' => '<svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7"/><path d="M14 8h6v6"/></svg>',
        'map-pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s-6-4.5-6-10a6 6 0 1 1 12 0c0 5.5-6 10-6 10z"/><circle cx="12" cy="11" r="2.5"/></svg>',
        'video' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3"/></svg>',
        'arrow-left' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>',
    ];

    $studentNav = [
        ['label' => 'Dashboard', 'mobile_label' => 'Dashboard', 'route' => 'studyhub.student.dashboard', 'icon' => 'home'],
        ['label' => 'Study Groups', 'mobile_label' => 'Groups', 'route' => 'studyhub.student.groups', 'icon' => 'users'],
        ['label' => 'Resources', 'mobile_label' => 'Resources', 'route' => 'studyhub.student.resources', 'icon' => 'resources', 'active_routes' => ['studyhub.student.resources', 'studyhub.student.library']],
        ['label' => 'Discussions', 'mobile_label' => 'Discussions', 'route' => 'studyhub.student.discussions', 'icon' => 'discussion'],
        ['label' => 'Sessions', 'mobile_label' => 'Sessions', 'route' => 'studyhub.student.sessions', 'icon' => 'calendar'],
    ];

    $currentRoute = request()->route()?->getName();
    $studentAppearance = ($studentProfile['theme'] ?? 'forest') === 'dark' ? 'dark' : 'light';
    $studentThemeClass = 'student-theme-forest';
    $studentSurfaceClass = 'student-surface-'.($studentProfile['surface_style'] ?? 'soft');
    $studentDensityClass = 'student-density-'.($studentProfile['interface_density'] ?? 'comfortable');
    $isStudentDashboardHome = in_array($currentRoute, ['studyhub.student.dashboard', 'studyhub.student.index'], true);
    $isStudentGroupsHome = $currentRoute === 'studyhub.student.groups';
@endphp

@section('content')
    <div class="studyhub-shell app-shell student-shell student-dashboard-shell {{ $isStudentDashboardHome ? 'student-dashboard-home' : '' }} {{ $isStudentGroupsHome ? 'student-groups-home' : '' }} {{ $studentThemeClass }} {{ $studentSurfaceClass }} {{ $studentDensityClass }}">
        <script>
            (function () {
                try {
                    var theme = '{{ $studentAppearance }}';
                    var shell = document.currentScript.parentElement;

                    if (shell) {
                        shell.classList.toggle('student-theme-dark', theme === 'dark');
                    }

                    localStorage.setItem('studyhub-login-theme', theme);
                } catch (error) {
                    // Keep the default light dashboard when browser storage is unavailable.
                }
            })();
        </script>
        <aside class="app-sidebar">
            <div class="sidebar-brand">
                <span class="sidebar-kicker">Student Workspace</span>
                <div class="sidebar-brand-lockup">
                    <span class="sidebar-brand-mark" aria-hidden="true">
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
                    <span class="sidebar-brand-copy">
                        <h1>Study<span>Hub</span></h1>
                    </span>
                </div>
            </div>

            <div class="sidebar-section-label">Navigation</div>
            <nav class="sidebar-nav">
                @foreach ($studentNav as $item)
                    @php
                        $isActive = in_array($currentRoute, $item['active_routes'] ?? [$item['route']], true);
                    @endphp
                    <a class="sidebar-link {{ $isActive ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <span class="icon-box">{!! $icons[$item['icon']] !!}</span>
                        <span class="nav-copy">
                            <span class="nav-title" data-mobile-label="{{ $item['mobile_label'] ?? $item['label'] }}">{{ $item['label'] }}</span>
                            <span class="nav-meta">{{ $isActive ? 'Current page' : 'Open section' }}</span>
                        </span>
                    </a>
                @endforeach
            </nav>

        </aside>

        <main class="app-main">
            <div class="student-global-topbar">
                <div class="student-mobile-brand" aria-label="StudyHub">
                    <span class="student-mobile-brand-mark" aria-hidden="true">
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
                    <span class="student-mobile-brand-text">Study<span>Hub</span></span>
                </div>

                @php
                    $activeChatThreadId = (int) old('chat_thread_id', session('open_chat_thread_id', collect($studentChatThreads)->first()['id'] ?? 0));
                    $activeChatThreadId = $activeChatThreadId ?: (int) (collect($studentChatThreads)->first()['id'] ?? 0);
                @endphp

                <div
                    class="student-chat-menu {{ session('open_chat') || $errors->groupChat->any() ? 'is-open' : '' }}"
                    data-student-chat-menu
                    data-chat-refresh-url="{{ route('studyhub.student.groups.chats') }}"
                    data-chat-read-url-template="{{ route('studyhub.student.groups.messages.read', ['group' => '__GROUP_ID__']) }}"
                >
                    <button class="student-top-icon-button student-chat-button" type="button" aria-label="Open chats" aria-expanded="{{ session('open_chat') || $errors->groupChat->any() ? 'true' : 'false' }}" data-student-chat-button>
                        <span class="icon-box">{!! $icons['message'] !!}</span>
                        <span class="student-notification-badge" data-student-chat-badge @if ($studentUnreadChatCount <= 0) hidden @endif>{{ $studentUnreadChatCount }}</span>
                    </button>

                    <section class="student-chat-popover" aria-label="Chats" data-student-chat-panel>
                        <div class="student-chat-header">
                            <div>
                                <h2>Chats</h2>
                                <span>{{ count($studentChatThreads) }} group threads</span>
                            </div>
                            <button class="student-chat-close" type="button" aria-label="Close chats" data-student-chat-close>
                                <svg viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        @if ($errors->groupChat->any())
                            <div class="student-chat-error" role="alert">{{ $errors->groupChat->first('body') }}</div>
                        @endif

                        @if (count($studentChatThreads) > 0)
                            <div class="student-chat-shell">
                                <div class="student-chat-thread-list">
                                    @foreach ($studentChatThreads as $thread)
                                        <button
                                            class="student-chat-thread {{ (int) $thread['id'] === $activeChatThreadId ? 'is-active' : '' }} {{ $thread['unread_count'] > 0 ? 'has-unread' : '' }}"
                                            type="button"
                                            data-student-chat-thread="{{ $thread['id'] }}"
                                        >
                                            <span class="student-chat-thread-avatar">{{ $thread['initials'] }}</span>
                                            <span class="student-chat-thread-copy">
                                                <strong>{{ $thread['name'] }}</strong>
                                                <span data-student-chat-latest>
                                                    @if ($thread['latest_author'])
                                                        {{ $thread['latest_author'] }}:
                                                    @endif
                                                    {{ $thread['latest_body'] }}
                                                </span>
                                            </span>
                                            <span class="student-chat-thread-meta">
                                                <time data-student-chat-time @if (! $thread['latest_time']) hidden @endif>{{ $thread['latest_time'] }}</time>
                                                <span data-student-chat-thread-badge @if ($thread['unread_count'] <= 0) hidden @endif>{{ $thread['unread_count'] }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>

                                <div class="student-chat-conversations">
                                    @foreach ($studentChatThreads as $thread)
                                        <article class="student-chat-conversation {{ (int) $thread['id'] === $activeChatThreadId ? 'is-active' : '' }}" data-student-chat-conversation="{{ $thread['id'] }}">
                                            <div class="student-chat-conversation-header">
                                                <button class="student-chat-back" type="button" aria-label="Back to chats" data-student-chat-back>
                                                    <svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                                                </button>
                                                <div class="student-chat-thread-avatar">{{ $thread['initials'] }}</div>
                                                <div>
                                                    <strong>{{ $thread['name'] }}</strong>
                                                    <span data-student-chat-message-count>{{ count($thread['messages']) }} messages</span>
                                                </div>
                                            </div>

                                            <div class="student-chat-messages" data-student-chat-messages>
                                                @forelse ($thread['messages'] as $message)
                                                    <div class="student-chat-message-row {{ $message['is_mine'] ? 'is-mine' : '' }}">
                                                        <span class="student-chat-message-avatar" title="{{ $message['author'] }}">
                                                            @if (! empty($message['author_avatar_url']))
                                                                <img src="{{ $message['author_avatar_url'] }}" alt="{{ $message['author'] }}">
                                                            @else
                                                                {{ $message['author_initials'] }}
                                                            @endif
                                                        </span>
                                                        <div class="student-chat-message">
                                                            <div class="student-chat-message-meta">
                                                                <strong>{{ $message['author'] }}</strong>
                                                                <time>{{ $message['time'] }}</time>
                                                            </div>
                                                            <p>{{ $message['body'] }}</p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="student-chat-empty">
                                                        <span class="icon-box">{!! $icons['message'] !!}</span>
                                                        <strong>No messages yet</strong>
                                                        <span>Start this group chat with a quick update.</span>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <form class="student-chat-form" method="POST" action="{{ route('studyhub.student.groups.messages.store', $thread['id']) }}" data-student-chat-form="{{ $thread['id'] }}">
                                                @csrf
                                                <input type="hidden" name="chat_thread_id" value="{{ $thread['id'] }}">
                                                <label class="sr-only" for="student-chat-body-{{ $thread['id'] }}">Message {{ $thread['name'] }}</label>
                                                <textarea id="student-chat-body-{{ $thread['id'] }}" name="body" maxlength="1000" rows="2" placeholder="Message {{ $thread['name'] }}" required></textarea>
                                                <button type="submit" data-loading-label="Sending...">Send</button>
                                            </form>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="student-chat-empty is-panel-empty">
                                <span class="icon-box">{!! $icons['message'] !!}</span>
                                <strong>No chats yet</strong>
                                <span>Join a study group to start chatting.</span>
                                <a href="{{ route('studyhub.student.groups') }}">Browse groups</a>
                            </div>
                        @endif
                    </section>
                </div>

                <livewire:student-notifications :icons="$icons" />

                <div class="student-global-profile-menu" data-profile-menu>
                    <button class="student-top-profile profile-card-button" type="button" aria-expanded="false" data-profile-menu-button>
                        <span class="profile-avatar">
                            @if (! empty($studentProfile['avatar_url']))
                                <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $studentProfile['display_name'] }}">
                            @else
                                {{ strtoupper(substr($studentProfile['display_name'], 0, 1)) }}{{ strtoupper(substr(trim(strrchr($studentProfile['display_name'], ' ')) ?: $studentProfile['display_name'], 0, 1)) }}
                            @endif
                        </span>
                        <span class="profile-copy">
                            <span class="profile-name">{{ $studentProfile['display_name'] }}</span>
                        </span>
                        <span class="profile-menu-caret" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                        </span>
                    </button>

                    <div class="profile-dropdown" data-profile-menu-panel>
                        <a class="profile-dropdown-item" href="{{ route('studyhub.student.profile') }}">
                            <span class="icon-box">{!! $icons['user'] !!}</span>
                            <span>Profile</span>
                        </a>
                        <form method="POST" action="{{ route('studyhub.logout') }}">
                            @csrf
                            <button class="profile-dropdown-item profile-dropdown-logout" type="submit" data-loading-label="Logging out...">
                                <span class="icon-box">{!! $icons['logout'] !!}</span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="layout-status">{{ session('status') }}</div>
            @endif
            @yield('page')
        </main>
    </div>

    <script>
        (function () {
            if (window.StudyHubUI) {
                return;
            }

            const getModal = function (modal) {
                return typeof modal === 'string' ? document.querySelector(modal) : modal;
            };

            const getAllModals = function () {
                return Array.from(document.querySelectorAll('[data-studyhub-modal]'));
            };

            const syncBodyOverflow = function () {
                document.body.classList.toggle('overflow-hidden', getAllModals().some(function (modal) {
                    return modal.classList.contains('is-open');
                }));
            };

            const setModalState = function (modal, isOpen) {
                const target = getModal(modal);

                if (! target) {
                    return;
                }

                target.classList.toggle('is-open', isOpen);
                target.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                syncBodyOverflow();
            };

            const bindModalTriggers = function (options) {
                const modal = getModal(options.modal);

                if (! modal) {
                    return null;
                }

                if (options.open) {
                    document.querySelectorAll(options.open).forEach(function (button) {
                        button.addEventListener('click', function () {
                            if (typeof options.beforeOpen === 'function') {
                                options.beforeOpen(button, modal);
                            }

                            setModalState(modal, true);

                            if (typeof options.afterOpen === 'function') {
                                options.afterOpen(button, modal);
                            }
                        });
                    });
                }

                if (options.close) {
                    document.querySelectorAll(options.close).forEach(function (button) {
                        button.addEventListener('click', function () {
                            setModalState(modal, false);

                            if (typeof options.afterClose === 'function') {
                                options.afterClose(button, modal);
                            }
                        });
                    });
                }

                return modal;
            };

            const setEmptyState = function (emptyState, options) {
                if (! emptyState) {
                    return;
                }

                const visible = Number(options.visibleCount) === 0;
                const hasItems = Number(options.totalCount) > 0;
                const title = emptyState.querySelector('[data-empty-title]');
                const copy = emptyState.querySelector('[data-empty-copy]');

                emptyState.classList.toggle('hidden', ! visible);

                if (! visible) {
                    return;
                }

                if (title) {
                    title.textContent = hasItems ? options.filteredTitle : options.emptyTitle;
                }

                if (copy) {
                    copy.textContent = hasItems ? options.filteredCopy : options.emptyCopy;
                }
            };

            const initLoadingButtons = function () {
                document.querySelectorAll('form').forEach(function (form) {
                    if (form.dataset.studyhubLoadingBound === 'true') {
                        return;
                    }

                    form.dataset.studyhubLoadingBound = 'true';

                    form.addEventListener('click', function (event) {
                        const submitter = event.target.closest('button[type="submit"]');

                        if (submitter) {
                            form._studyHubSubmitter = submitter;
                        }
                    });

                    form.addEventListener('submit', function (event) {
                        const submitter = event.submitter || form._studyHubSubmitter || form.querySelector('button[type="submit"][data-loading-label]');

                        if (! submitter || submitter.disabled || ! submitter.dataset.loadingLabel) {
                            return;
                        }

                        submitter.dataset.originalLabel = submitter.innerHTML;
                        submitter.innerHTML = '<span class="student-button-spinner" aria-hidden="true"></span><span>' + submitter.dataset.loadingLabel + '</span>';
                        submitter.classList.add('is-loading');

                        form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                            button.disabled = true;
                            button.setAttribute('aria-disabled', 'true');
                        });
                    });
                });
            };

            window.StudyHubUI = {
                bindModalTriggers: bindModalTriggers,
                closeAllModals: function () {
                    getAllModals().forEach(function (modal) {
                        setModalState(modal, false);
                    });
                },
                initLoadingButtons: initLoadingButtons,
                setEmptyState: setEmptyState,
                setModalState: setModalState,
                syncBodyOverflow: syncBodyOverflow,
            };
        })();

        document.addEventListener('DOMContentLoaded', function () {
            const menu = document.querySelector('[data-profile-menu]');
            const button = document.querySelector('[data-profile-menu-button]');
            const chatMenu = document.querySelector('[data-student-chat-menu]');
            const chatButton = document.querySelector('[data-student-chat-button]');
            const chatClose = document.querySelector('[data-student-chat-close]');
            const chatMobileQuery = window.matchMedia('(max-width: 720px)');
            const csrfToken = @json(csrf_token());
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let chatRefreshTimer = null;
            let chatIsSyncing = false;

            function isMobileChat() {
                return chatMobileQuery.matches;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function pluralize(count, singular, plural) {
                return count === 1 ? singular : plural;
            }

            function activeChatThreadId() {
                return chatMenu?.querySelector('[data-student-chat-thread].is-active')?.dataset.studentChatThread || '';
            }

            function chatReadUrl(threadId) {
                return (chatMenu?.dataset.chatReadUrlTemplate || '').replace('__GROUP_ID__', encodeURIComponent(threadId));
            }

            function setBadgeValue(badge, value) {
                if (! badge) {
                    return;
                }

                const count = Number(value) || 0;
                badge.textContent = count;
                badge.hidden = count <= 0;
            }

            function scrollMessagesToBottom(messageList, force) {
                if (! messageList) {
                    return;
                }

                const isNearBottom = messageList.scrollTop + messageList.clientHeight >= messageList.scrollHeight - 80;

                if (! force && ! isNearBottom) {
                    return;
                }

                messageList.scrollTo({
                    top: messageList.scrollHeight,
                    behavior: prefersReducedMotion ? 'auto' : 'smooth',
                });
            }

            function messageHtml(message) {
                const avatar = message.author_avatar_url
                    ? '<img src="' + escapeHtml(message.author_avatar_url) + '" alt="' + escapeHtml(message.author) + '">'
                    : escapeHtml(message.author_initials);

                return [
                    '<div class="student-chat-message-row ' + (message.is_mine ? 'is-mine' : '') + '" data-student-chat-message="' + escapeHtml(String(message.id)) + '">',
                        '<span class="student-chat-message-avatar" title="' + escapeHtml(message.author) + '">' + avatar + '</span>',
                        '<div class="student-chat-message">',
                            '<div class="student-chat-message-meta">',
                                '<strong>' + escapeHtml(message.author) + '</strong>',
                                '<time>' + escapeHtml(message.time) + '</time>',
                            '</div>',
                            '<p>' + escapeHtml(message.body) + '</p>',
                        '</div>',
                    '</div>',
                ].join('');
            }

            function emptyChatHtml() {
                return [
                    '<div class="student-chat-empty">',
                        '<span class="icon-box">{!! $icons['message'] !!}</span>',
                        '<strong>No messages yet</strong>',
                        '<span>Start this group chat with a quick update.</span>',
                    '</div>',
                ].join('');
            }

            function applyChatPayload(payload) {
                if (! chatMenu || ! payload || ! Array.isArray(payload.threads)) {
                    return;
                }

                setBadgeValue(chatMenu.querySelector('[data-student-chat-badge]'), payload.unread_total);

                payload.threads.forEach(function (thread) {
                    const threadButton = chatMenu.querySelector('[data-student-chat-thread="' + thread.id + '"]');
                    const conversation = chatMenu.querySelector('[data-student-chat-conversation="' + thread.id + '"]');

                    if (threadButton) {
                        const latest = threadButton.querySelector('[data-student-chat-latest]');
                        const time = threadButton.querySelector('[data-student-chat-time]');

                        threadButton.classList.toggle('has-unread', Number(thread.unread_count) > 0);
                        setBadgeValue(threadButton.querySelector('[data-student-chat-thread-badge]'), thread.unread_count);

                        if (latest) {
                            latest.textContent = (thread.latest_author ? thread.latest_author + ': ' : '') + thread.latest_body;
                        }

                        if (time) {
                            time.textContent = thread.latest_time || '';
                            time.hidden = ! thread.latest_time;
                        }
                    }

                    if (conversation) {
                        const count = Number(thread.messages?.length || 0);
                        const countLabel = conversation.querySelector('[data-student-chat-message-count]');
                        const messageList = conversation.querySelector('[data-student-chat-messages]');

                        if (countLabel) {
                            countLabel.textContent = count + ' ' + pluralize(count, 'message', 'messages');
                        }

                        if (messageList) {
                            const wasNearBottom = messageList.scrollTop + messageList.clientHeight >= messageList.scrollHeight - 80;
                            messageList.innerHTML = count > 0 ? thread.messages.map(messageHtml).join('') : emptyChatHtml();

                            if (wasNearBottom || conversation.classList.contains('is-active')) {
                                scrollMessagesToBottom(messageList, true);
                            }
                        }
                    }
                });
            }

            function refreshChats() {
                if (! chatMenu?.dataset.chatRefreshUrl || chatIsSyncing || document.hidden) {
                    return Promise.resolve();
                }

                chatIsSyncing = true;
                const url = new URL(chatMenu.dataset.chatRefreshUrl, window.location.origin);
                const threadId = activeChatThreadId();

                if (threadId) {
                    url.searchParams.set('active_thread_id', threadId);
                }

                return fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (response) {
                        return response.ok ? response.json() : null;
                    })
                    .then(applyChatPayload)
                    .catch(function () {
                        return null;
                    })
                    .finally(function () {
                        chatIsSyncing = false;
                    });
            }

            function markChatRead(threadId) {
                const url = chatReadUrl(threadId);

                if (! url) {
                    return Promise.resolve();
                }

                const threadButton = chatMenu?.querySelector('[data-student-chat-thread="' + threadId + '"]');
                const threadBadge = threadButton?.querySelector('[data-student-chat-thread-badge]');
                const globalBadge = chatMenu?.querySelector('[data-student-chat-badge]');
                const previousUnread = Number(threadBadge?.textContent || 0);
                const previousTotal = Number(globalBadge?.textContent || 0);

                threadButton?.classList.remove('has-unread');
                setBadgeValue(threadBadge, 0);
                setBadgeValue(globalBadge, Math.max(0, previousTotal - previousUnread));

                return fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                })
                    .then(function (response) {
                        return response.ok ? response.json() : null;
                    })
                    .then(applyChatPayload)
                    .catch(function () {
                        setBadgeValue(threadBadge, previousUnread);
                        setBadgeValue(globalBadge, previousTotal);
                        threadButton?.classList.toggle('has-unread', previousUnread > 0);
                    });
            }

            function startChatPolling() {
                if (chatRefreshTimer || ! chatMenu) {
                    return;
                }

                refreshChats();
                chatRefreshTimer = window.setInterval(refreshChats, 15000);
            }

            function stopChatPolling() {
                if (! chatRefreshTimer) {
                    return;
                }

                window.clearInterval(chatRefreshTimer);
                chatRefreshTimer = null;
            }

            function setOpen(isOpen) {
                if (! menu || ! button) {
                    return;
                }

                menu.classList.toggle('is-open', isOpen);
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

            function setChatOpen(isOpen) {
                if (! chatMenu || ! chatButton) {
                    return;
                }

                chatMenu.classList.toggle('is-open', isOpen);
                chatButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                if (! isOpen) {
                    chatMenu.classList.remove('is-viewing-chat');
                }

                document.body.classList.toggle('student-chat-is-open', isOpen && isMobileChat());

                if (isOpen) {
                    startChatPolling();

                    if (! isMobileChat() && activeChatThreadId()) {
                        markChatRead(activeChatThreadId());
                    }
                }
            }

            function setChatMobileView(isViewingChat) {
                if (! chatMenu) {
                    return;
                }

                chatMenu.classList.toggle('is-viewing-chat', isViewingChat && isMobileChat());
            }

            if (menu && button) {
                button.addEventListener('click', function (event) {
                    event.stopPropagation();
                    setOpen(! menu.classList.contains('is-open'));
                    setChatOpen(false);
                });

                document.addEventListener('click', function (event) {
                    if (! menu.contains(event.target)) {
                        setOpen(false);
                    }
                });
            }

            if (chatMenu && chatButton) {
                chatButton.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const shouldOpen = ! chatMenu.classList.contains('is-open');

                    setChatOpen(shouldOpen);
                    if (shouldOpen) {
                        setChatMobileView(false);
                    }

                    setOpen(false);
                });

                chatClose?.addEventListener('click', function () {
                    setChatOpen(false);
                });

                chatMenu.querySelectorAll('[data-student-chat-back]').forEach(function (backButton) {
                    backButton.addEventListener('click', function () {
                        setChatMobileView(false);
                    });
                });

                chatMenu.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                document.addEventListener('click', function (event) {
                    if (! chatMenu.contains(event.target)) {
                        setChatOpen(false);
                    }
                });

                chatMenu.querySelectorAll('[data-student-chat-thread]').forEach(function (threadButton) {
                    threadButton.addEventListener('click', function () {
                        const threadId = threadButton.dataset.studentChatThread;

                        chatMenu.querySelectorAll('[data-student-chat-thread]').forEach(function (button) {
                            button.classList.toggle('is-active', button.dataset.studentChatThread === threadId);
                        });

                        chatMenu.querySelectorAll('[data-student-chat-conversation]').forEach(function (conversation) {
                            conversation.classList.toggle('is-active', conversation.dataset.studentChatConversation === threadId);
                        });

                        setChatMobileView(true);
                        markChatRead(threadId);
                    });
                });

                chatMenu.querySelectorAll('[data-student-chat-form]').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        event.stopImmediatePropagation();

                        const submitButton = form.querySelector('button[type="submit"]');
                        const textarea = form.querySelector('textarea[name="body"]');
                        const body = textarea?.value.trim() || '';

                        if (! body) {
                            return;
                        }

                        submitButton.disabled = true;
                        submitButton.setAttribute('aria-busy', 'true');
                        form.classList.add('is-sending');

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(form),
                        })
                            .then(function (response) {
                                return response.ok ? response.json() : Promise.reject(response);
                            })
                            .then(function (payload) {
                                textarea.value = '';
                                applyChatPayload(payload);
                                scrollMessagesToBottom(form.closest('[data-student-chat-conversation]')?.querySelector('[data-student-chat-messages]'), true);
                                markChatRead(form.dataset.studentChatForm);
                            })
                            .catch(function () {
                                textarea.focus();
                            })
                            .finally(function () {
                                submitButton.disabled = false;
                                submitButton.removeAttribute('aria-busy');
                                form.classList.remove('is-sending');
                            });
                    });
                });

                if (chatMenu.classList.contains('is-open')) {
                    setChatMobileView(isMobileChat());
                    document.body.classList.toggle('student-chat-is-open', isMobileChat());

                    if (! isMobileChat() && activeChatThreadId()) {
                        markChatRead(activeChatThreadId());
                    }
                }

                chatMobileQuery.addEventListener('change', function () {
                    document.body.classList.toggle('student-chat-is-open', chatMenu.classList.contains('is-open') && isMobileChat());

                    if (! isMobileChat()) {
                        chatMenu.classList.remove('is-viewing-chat');
                    }
                });

                document.addEventListener('visibilitychange', function () {
                    if (document.hidden) {
                        stopChatPolling();
                    } else {
                        startChatPolling();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setOpen(false);
                    setChatOpen(false);
                    window.StudyHubUI.closeAllModals();
                }
            });

            window.StudyHubUI.initLoadingButtons();
            window.StudyHubUI.syncBodyOverflow();
        });
    </script>
@endsection
