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

