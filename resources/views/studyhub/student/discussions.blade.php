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

@push('page-styles')
    <style>
        .discussion-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .discussion-header .page-title {
            font-size: 2.9rem;
            margin-bottom: 6px;
            letter-spacing: -0.04em;
        }

        .discussion-header .page-subtitle {
            margin: 0;
            max-width: 700px;
            font-size: 1.05rem;
        }

        .discussion-cta {
            min-height: 56px;
            padding: 0 28px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            font-size: 1.02rem;
            font-weight: 800;
            white-space: nowrap;
            margin-top: 18px;
        }

        .discussion-status,
        .discussion-errors {
            margin: 0 0 18px;
            padding: 13px 15px;
            border-radius: 16px;
            font-size: 0.94rem;
        }

        .discussion-status {
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
        }

        .discussion-errors {
            border: 1px solid rgba(219, 137, 120, 0.22);
            background: rgba(255, 244, 240, 0.92);
            color: #8a3f32;
        }

        .discussion-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .discussion-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .discussion-stat-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 20px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            border: 1px solid rgba(195, 215, 203, 0.92);
            box-shadow: 0 22px 40px rgba(66, 95, 76, 0.1);
        }

        .discussion-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            flex: 0 0 auto;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.45), 0 12px 18px rgba(80, 126, 101, 0.16);
        }

        .discussion-stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .discussion-stat-label {
            color: #46604f;
            font-size: 0.96rem;
            margin-bottom: 4px;
        }

        .discussion-stat-value {
            color: #173223;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .discussion-list {
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
        }

        .discussion-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 20px 22px;
            background: linear-gradient(90deg, rgba(245,252,246,0.98) 0%, rgba(237,248,241,0.98) 100%);
            border-bottom: 1px solid rgba(208, 226, 214, 0.95);
        }

        .discussion-row:last-child {
            border-bottom: 0;
        }

        .discussion-main {
            min-width: 0;
            flex: 1;
        }

        .discussion-side {
            display: grid;
            gap: 10px;
            justify-items: end;
            flex: 0 0 auto;
        }

        .discussion-author-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }

        .discussion-author-avatar {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 76%, white 24%) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.92rem;
            font-weight: 800;
            box-shadow: 0 12px 20px color-mix(in srgb, var(--student-accent) 18%, transparent 82%);
            overflow: hidden;
            flex: 0 0 auto;
        }

        .discussion-author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .discussion-author-copy {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        .discussion-author-name {
            color: #173223;
            font-size: 0.96rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .discussion-author-role {
            color: #738479;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .discussion-main h3 {
            margin: 0 0 8px;
            font-size: 1.18rem;
            line-height: 1.4;
            letter-spacing: -0.02em;
            color: #173223;
        }

        .discussion-meta,
        .discussion-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            color: #6b7d72;
            font-size: 0.82rem;
        }

        .discussion-meta {
            margin-bottom: 8px;
        }

        .discussion-meta span:first-child,
        .discussion-meta span:nth-child(2) {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .discussion-meta .icon-box,
        .discussion-stats .icon-box {
            color: #73877a;
        }

        .discussion-meta svg,
        .discussion-stats svg {
            width: 12px;
            height: 12px;
            stroke-width: 2;
        }

        .thread-button {
            min-width: 108px;
            height: 40px;
            padding: 0 18px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .discussion-delete-form {
            margin: 0;
        }

        .discussion-delete-button {
            min-width: 108px;
            height: 38px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid rgba(216, 133, 118, 0.26);
            background: rgba(255, 244, 240, 0.96);
            color: #9b4637;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .discussion-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 28px;
            z-index: 40;
        }

        .discussion-modal.is-open {
            display: flex;
        }

        .discussion-modal-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 22, 17, 0.48);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .discussion-modal-panel {
            position: relative;
            z-index: 1;
            width: min(680px, 100%);
            max-height: calc(100vh - 44px);
            overflow: auto;
            border-radius: 28px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 34%),
                linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 40%, white 60%) 0%, rgba(255,255,255,0.98) 100%);
            box-shadow: 0 28px 56px color-mix(in srgb, var(--student-accent-text) 18%, transparent 82%);
        }

        .discussion-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 24px 16px;
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 72%, white 28%) 0%, color-mix(in srgb, var(--student-accent-pale) 68%, white 32%) 100%);
        }

        .discussion-modal-title {
            margin: 0 0 6px;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #183425;
        }

        .discussion-modal-copy {
            margin: 0;
            color: color-mix(in srgb, var(--student-accent-text) 72%, white 28%);
            font-size: 0.95rem;
        }

        .discussion-modal-close {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: color-mix(in srgb, var(--student-accent-pale) 54%, white 46%);
            color: var(--student-accent-text);
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }

        .discussion-modal-body {
            padding: 20px 24px 24px;
        }

        .discussion-form {
            display: grid;
            gap: 14px;
        }

        .discussion-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 46%, white 54%) 0%, color-mix(in srgb, var(--student-accent-pale) 20%, white 80%) 100%);
        }

        .discussion-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: #244231;
        }

        .discussion-input,
        .discussion-select,
        .discussion-textarea {
            width: 100%;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.92);
            color: #1f3528;
            font: inherit;
        }

        .discussion-input,
        .discussion-select {
            height: 54px;
            padding: 0 16px;
        }

        .discussion-textarea {
            min-height: 140px;
            padding: 16px;
            resize: vertical;
        }

        .discussion-form-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }

        .discussion-submit {
            min-height: 54px;
            min-width: 190px;
            padding: 0 24px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.98rem;
            font-weight: 800;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            cursor: pointer;
        }

        @media (max-width: 980px) {
            .discussion-header {
                flex-direction: column;
                align-items: stretch;
            }

            .discussion-header .page-title {
                font-size: 2.3rem;
            }

            .discussion-cta {
                margin-top: 0;
            }

            .discussion-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .discussion-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .discussion-side,
            .thread-button,
            .discussion-delete-button {
                width: 100%;
            }

            .discussion-side {
                justify-items: stretch;
            }
        }
    </style>
@endpush

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
