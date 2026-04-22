@extends('studyhub.student.layout')

@section('title', 'Discussions')

@php
    $discussionInitials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = strtoupper(substr($parts[0] ?? $name, 0, 1));
        $last = strtoupper(substr($parts[count($parts) - 1] ?? $name, 0, 1));

        return $first.$last;
    };
@endphp

@section('page')
    <div class="discussion-header">
        <div>
            <h2 class="page-title">Discussions</h2>
            <p class="page-subtitle">Engage with your study groups</p>
        </div>
        <div class="toolbar-actions">
            <button class="action-button discussion-cta" type="button" data-discussion-open>
                <span class="icon-box">{!! $icons['plus'] !!}</span>
                <span>New Discussion</span>
            </button>
        </div>
    </div>

    <section class="discussion-stats-grid">
        @foreach ($stats as $stat)
            <article class="discussion-stat-card">
                <div
                    class="icon-box discussion-stat-icon"
                    style="
                        @if ($loop->first)
                            background:linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%); color:#ffffff;
                        @elseif ($loop->iteration === 2)
                            background:linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 44%, white 56%) 0%, color-mix(in srgb, var(--student-accent) 68%, white 32%) 100%); color:#ffffff;
                        @else
                            background:linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 24%, white 76%) 0%, color-mix(in srgb, var(--student-accent-soft) 82%, white 18%) 100%); color:#ffffff;
                        @endif
                    "
                >
                    {!! $icons[$stat['icon']] !!}
                </div>
                <div>
                    <div class="discussion-stat-label">{{ $stat['label'] }}</div>
                    <div class="discussion-stat-value">{{ $stat['value'] }}</div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="content-card discussion-list">
        @foreach ($discussions as $discussion)
            <article class="discussion-row">
                <div class="discussion-main">
                    <div class="discussion-author-row">
                        <span class="discussion-author-avatar">
                            @if ($discussion['author'] === $studentProfile['display_name'] && ! empty($studentProfile['avatar_url']))
                                <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $discussion['author'] }}">
                            @else
                                {{ $discussionInitials($discussion['author']) }}
                            @endif
                        </span>
                        <span class="discussion-author-copy">
                            <span class="discussion-author-name">{{ $discussion['author'] }}</span>
                            <span class="discussion-author-role">{{ $discussion['group'] }}</span>
                        </span>
                    </div>
                    <h3>{{ $discussion['title'] }}</h3>
                    <div class="discussion-meta">
                        <span>
                            <span class="icon-box">{!! $icons['user'] !!}</span>
                            <span>{{ $discussion['author'] }}</span>
                        </span>
                        <span>
                            <span>{{ $discussion['group'] }}</span>
                        </span>
                    </div>
                    <div class="discussion-stats">
                        <span>{{ $discussion['replies'] }} Replies</span>
                        <span>{{ $discussion['views'] }} Views</span>
                        <span>
                            <span class="icon-box">{!! $icons['clock'] !!}</span>
                            <span>{{ $discussion['last_active'] }}</span>
                        </span>
                    </div>
                </div>
                <div class="discussion-side">
                    <a class="thread-button" href="{{ route('studyhub.student.discussions.show', $discussion['id']) }}">View Thread</a>
                    @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
                        <form class="discussion-delete-form" method="POST" action="{{ route('studyhub.student.discussions.delete', $discussion['id']) }}">
                            @csrf
                            <button class="discussion-delete-button" type="submit">Delete</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <div class="discussion-modal @if ($errors->any()) is-open @endif" data-discussion-modal>
        <button class="discussion-modal-backdrop" type="button" aria-label="Close discussion form" data-discussion-close></button>
        <div class="discussion-modal-panel">
            <div class="discussion-modal-header">
                <div>
                    <h3 class="discussion-modal-title">Start discussion</h3>
                    <p class="discussion-modal-copy">Post a new topic to one of your joined groups.</p>
                </div>
                <button class="discussion-modal-close" type="button" aria-label="Close discussion form" data-discussion-close>&times;</button>
            </div>

            <div class="discussion-modal-body">
                @if ($errors->any())
                    <div class="discussion-errors">
                        Please fix the following:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="discussion-form" method="POST" action="{{ route('studyhub.student.discussions.store') }}">
                    @csrf
                    <label class="discussion-field">
                        <span class="discussion-label">Topic title</span>
                        <input class="discussion-input" type="text" name="title" maxlength="120" placeholder="Ask a question or start a topic" value="{{ old('title') }}" required>
                    </label>

                    <label class="discussion-field">
                        <span class="discussion-label">Study group</span>
                        <select class="discussion-select" name="group_id" required>
                            <option value="">Choose group</option>
                            @foreach ($discussionGroups as $group)
                                <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="discussion-field">
                        <span class="discussion-label">Message</span>
                        <textarea class="discussion-textarea" name="body" maxlength="500" placeholder="Share your question, notes, or discussion topic." required>{{ old('body') }}</textarea>
                    </label>

                    <div class="discussion-form-actions">
                        <button class="discussion-submit" type="submit">Post discussion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-discussion-modal]');
            const openButton = document.querySelector('[data-discussion-open]');
            const closeButtons = document.querySelectorAll('[data-discussion-close]');

            if (! modal || ! openButton) {
                return;
            }

            const setModalState = function (isOpen) {
                modal.classList.toggle('is-open', isOpen);
                document.body.style.overflow = isOpen ? 'hidden' : '';
            };

            openButton.addEventListener('click', function () {
                setModalState(true);
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setModalState(false);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    setModalState(false);
                }
            });

            if (modal.classList.contains('is-open')) {
                document.body.style.overflow = 'hidden';
            }
        });
    </script>
@endsection

