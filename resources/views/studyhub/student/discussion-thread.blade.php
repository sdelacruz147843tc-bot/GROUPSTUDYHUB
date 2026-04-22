@extends('studyhub.student.layout')

@section('title', $discussion['title'] ?? 'Discussion Thread')

@php
    $threadInitials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = strtoupper(substr($parts[0] ?? $name, 0, 1));
        $last = strtoupper(substr($parts[count($parts) - 1] ?? $name, 0, 1));

        return $first.$last;
    };
@endphp

@push('page-styles')
    <style>
        .thread-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: var(--student-accent);
            font-weight: 700;
        }

        .thread-hero {
            margin-bottom: 20px;
            padding: 24px;
            border-radius: 24px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
        }

        .thread-title {
            margin: 0 0 10px;
            font-size: 2.2rem;
            line-height: 1.18;
            letter-spacing: -0.04em;
            color: #173223;
        }

        .thread-profile {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(90deg, color-mix(in srgb, var(--student-accent-pale) 58%, white 42%) 0%, rgba(255,255,255,0.9) 100%);
            border: 1px solid color-mix(in srgb, var(--student-accent) 16%, white 84%);
        }

        .thread-profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 78%, white 22%) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 1.05rem;
            font-weight: 800;
            overflow: hidden;
            box-shadow: 0 14px 24px color-mix(in srgb, var(--student-accent) 18%, transparent 82%);
            flex: 0 0 auto;
        }

        .thread-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .thread-profile-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .thread-profile-name {
            color: #173223;
            font-size: 1rem;
            font-weight: 800;
        }

        .thread-profile-role {
            color: #6e8074;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .thread-meta {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            color: #6f8175;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .thread-body {
            margin: 0;
            color: #274032;
            font-size: 1rem;
            line-height: 1.65;
        }

        .thread-hero-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .thread-delete-form {
            margin: 0;
        }

        .thread-delete-button {
            min-height: 42px;
            min-width: 132px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid rgba(216, 133, 118, 0.26);
            background: rgba(255, 244, 240, 0.96);
            color: #9b4637;
            font-size: 0.88rem;
            font-weight: 800;
            cursor: pointer;
        }

        .thread-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(300px, 0.7fr);
            gap: 18px;
            align-items: start;
        }

        .thread-card {
            border-radius: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
            overflow: hidden;
        }

        .thread-card-header {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(208, 226, 214, 0.95);
            background: linear-gradient(90deg, rgba(245,252,246,0.98) 0%, rgba(237,248,241,0.98) 100%);
        }

        .thread-card-header h3 {
            margin: 0;
            font-size: 1.4rem;
            color: #173223;
            letter-spacing: -0.03em;
        }

        .thread-replies {
            display: grid;
            gap: 14px;
            padding: 18px;
        }

        .thread-reply {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid rgba(209, 224, 214, 0.95);
            background: white;
        }

        .thread-reply-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .thread-reply-avatar {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 74%, white 26%) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.9rem;
            font-weight: 800;
            overflow: hidden;
            flex: 0 0 auto;
        }

        .thread-reply-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .thread-reply-profile {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .thread-reply-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .thread-reply-author {
            color: #1a3828;
            font-weight: 800;
        }

        .thread-reply-time {
            color: #738479;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .thread-reply-body {
            margin: 0;
            color: #314b3b;
            line-height: 1.6;
        }

        .thread-reply-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .thread-inline-reply {
            border: 0;
            background: transparent;
            color: var(--student-accent);
            font-size: 0.86rem;
            font-weight: 800;
            cursor: pointer;
            padding: 0;
        }

        .thread-children {
            display: grid;
            gap: 12px;
            margin-top: 14px;
            padding-left: 18px;
            border-left: 2px solid color-mix(in srgb, var(--student-accent-pale) 70%, white 30%);
        }

        .thread-child-reply {
            padding: 14px 14px 14px 16px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(248,252,249,0.98) 0%, rgba(255,255,255,0.98) 100%);
            border: 1px solid rgba(215, 228, 220, 0.92);
        }

        .thread-child-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .thread-child-author {
            color: #1a3828;
            font-weight: 800;
            font-size: 0.92rem;
        }

        .thread-child-time {
            color: #738479;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .thread-child-target {
            margin: 0 0 8px;
            color: var(--student-accent);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .thread-replying-to {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: color-mix(in srgb, var(--student-accent-pale) 70%, white 30%);
            color: var(--student-accent-text);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .thread-replying-to.is-active {
            display: flex;
        }

        .thread-replying-clear {
            border: 0;
            background: transparent;
            color: var(--student-accent-text);
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            padding: 0;
        }

        .thread-empty {
            padding: 18px;
            color: #6e8074;
            text-align: center;
        }

        .thread-form {
            display: grid;
            gap: 14px;
            padding: 18px;
        }

        .thread-status,
        .thread-errors {
            margin: 0 18px 0;
            padding: 12px 14px;
            border-radius: 16px;
            font-size: 0.92rem;
        }

        .thread-status {
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
        }

        .thread-errors {
            border: 1px solid rgba(219, 137, 120, 0.22);
            background: rgba(255, 244, 240, 0.92);
            color: #8a3f32;
        }

        .thread-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .thread-textarea {
            min-height: 180px;
            width: 100%;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.94);
            color: #1f3528;
            font: inherit;
            resize: vertical;
        }

        .thread-submit-row {
            display: flex;
            justify-content: flex-end;
        }

        .thread-submit {
            min-height: 50px;
            min-width: 160px;
            padding: 0 22px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
        }

        @media (max-width: 980px) {
            .thread-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('page')
    <a class="thread-back" href="{{ route('studyhub.student.discussions') }}">
        <span class="icon-box">{!! $icons['arrow-left'] !!}</span>
        <span>Back to Discussions</span>
    </a>

    <section class="thread-hero">
        <h2 class="thread-title">{{ $discussion['title'] }}</h2>
        <div class="thread-profile">
            <span class="thread-profile-avatar">
                @if (($discussion['author'] ?? '') === $studentProfile['display_name'] && ! empty($studentProfile['avatar_url']))
                    <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $discussion['author'] }}">
                @else
                    {{ $threadInitials($discussion['author']) }}
                @endif
            </span>
            <span class="thread-profile-copy">
                <span class="thread-profile-name">{{ $discussion['author'] }}</span>
                <span class="thread-profile-role">{{ $discussion['group'] }} discussion starter</span>
            </span>
        </div>
        <div class="thread-meta">
            <span>{{ $discussion['author'] }}</span>
            <span>{{ $discussion['group'] }}</span>
            <span>{{ $discussion['views'] }} views</span>
            <span>{{ $discussion['replies'] }} replies</span>
            <span>{{ $discussion['last_active'] }}</span>
        </div>
        <p class="thread-body">{{ $discussion['body'] }}</p>
        @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
            <div class="thread-hero-actions">
                <form class="thread-delete-form" method="POST" action="{{ route('studyhub.student.discussions.delete', $discussion['id']) }}">
                    @csrf
                    <button class="thread-delete-button" type="submit">Delete Discussion</button>
                </form>
            </div>
        @endif
    </section>

    <section class="thread-layout">
        <article class="thread-card">
            <div class="thread-card-header">
                <h3>Replies</h3>
            </div>
            <div class="thread-replies">
                @forelse ($replies as $reply)
                    <article class="thread-reply">
                        <div class="thread-reply-header">
                            <span class="thread-reply-avatar">
                                @if (($reply['author'] ?? '') === $studentProfile['display_name'] && ! empty($studentProfile['avatar_url']))
                                    <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $reply['author'] }}">
                                @else
                                    {{ $threadInitials($reply['author']) }}
                                @endif
                            </span>
                            <span class="thread-reply-profile">
                                <span class="thread-reply-author">{{ $reply['author'] }}</span>
                                <span class="thread-reply-time">{{ $reply['time'] }}</span>
                            </span>
                        </div>
                        <p class="thread-reply-body">{{ $reply['body'] }}</p>
                        <div class="thread-reply-actions">
                            <button
                                class="thread-inline-reply"
                                type="button"
                                data-thread-reply-target="{{ $reply['id'] }}"
                                data-thread-reply-author="{{ $reply['author'] }}"
                            >
                                Reply directly
                            </button>
                        </div>

                        @if (! empty($reply['children']))
                            <div class="thread-children">
                                @foreach ($reply['children'] as $childReply)
                                    <article class="thread-child-reply">
                                        <div class="thread-child-meta">
                                            <span class="thread-child-author">{{ $childReply['author'] }}</span>
                                            <span class="thread-child-time">{{ $childReply['time'] }}</span>
                                        </div>
                                        <p class="thread-child-target">Replying to {{ $childReply['parent_author'] }}</p>
                                        <p class="thread-reply-body">{{ $childReply['body'] }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="thread-empty">No replies yet. Start the conversation.</div>
                @endforelse
            </div>
        </article>

        <article class="thread-card">
            <div class="thread-card-header">
                <h3>Reply</h3>
            </div>

            @if ($errors->any())
                <div class="thread-errors">
                    Please fix the following:
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="thread-form" method="POST" action="{{ route('studyhub.student.discussions.reply', $discussion['id']) }}">
                @csrf
                <input type="hidden" name="parent_reply_id" value="{{ old('parent_reply_id') }}" data-thread-parent-input>
                <div class="thread-replying-to {{ old('parent_reply_id') ? 'is-active' : '' }}" data-thread-replying-to>
                    <span data-thread-replying-label>
                        @if (old('parent_reply_id'))
                            Replying directly to another comment.
                        @else
                            Replying to this discussion.
                        @endif
                    </span>
                    <button class="thread-replying-clear" type="button" data-thread-clear-reply>Clear</button>
                </div>
                <textarea class="thread-textarea" name="reply" maxlength="500" placeholder="Write your reply..." required>{{ old('reply') }}</textarea>
                <div class="thread-submit-row">
                    <button class="thread-submit" type="submit">Post Reply</button>
                </div>
            </form>
        </article>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const parentInput = document.querySelector('[data-thread-parent-input]');
            const replyBanner = document.querySelector('[data-thread-replying-to]');
            const replyLabel = document.querySelector('[data-thread-replying-label]');
            const clearButton = document.querySelector('[data-thread-clear-reply]');
            const replyButtons = document.querySelectorAll('[data-thread-reply-target]');

            if (!parentInput || !replyBanner || !replyLabel || !clearButton || !replyButtons.length) {
                return;
            }

            const setReplyTarget = function (replyId, authorName) {
                parentInput.value = replyId || '';

                if (replyId) {
                    replyBanner.classList.add('is-active');
                    replyLabel.textContent = 'Replying directly to ' + authorName + '.';
                } else {
                    replyBanner.classList.remove('is-active');
                    replyLabel.textContent = 'Replying to this discussion.';
                }
            };

            replyButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setReplyTarget(button.dataset.threadReplyTarget || '', button.dataset.threadReplyAuthor || 'this comment');
                });
            });

            clearButton.addEventListener('click', function () {
                setReplyTarget('', '');
            });
        });
    </script>
@endsection
