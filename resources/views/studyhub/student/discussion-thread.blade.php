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
        <span>Back</span>
    </a>

    <section class="thread-hero">
        <h2 class="thread-title">{{ $discussion['title'] }}</h2>
        <div class="thread-profile {{ ($discussion['author'] ?? '') === $studentProfile['display_name'] ? 'is-own' : '' }}">
            <span class="thread-profile-avatar">
                @if (! empty($discussion['author_avatar_url']))
                    <img src="{{ $discussion['author_avatar_url'] }}" alt="{{ $discussion['author'] }}">
                @else
                    {{ $discussion['author_initials'] ?? $threadInitials($discussion['author'] ?? 'Unknown') }}
                @endif
            </span>
            <span class="thread-profile-copy">
                <span class="thread-profile-name">
                    {{ $discussion['author'] }}
                    @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
                        <span class="thread-you-badge">You</span>
                    @endif
                </span>
                <span class="thread-profile-role">{{ $discussion['group'] }}</span>
            </span>
        </div>
        <div class="thread-meta">
            <span>{{ $discussion['group'] }}</span>
            <span>{{ $discussion['views'] }} views</span>
            <span>{{ $discussion['replies'] }} replies</span>
            <span>{{ $discussion['last_active'] }}</span>
        </div>
        <p class="thread-body">{{ $discussion['body'] }}</p>
        @if (! empty($discussion['has_image']))
            <div class="thread-image-gallery {{ ($discussion['image_count'] ?? 0) > 1 ? 'is-gallery' : '' }}">
                @foreach ($discussion['images'] ?? [] as $image)
                    <figure class="thread-image-attachment">
                        <a href="{{ $image['url'] }}" target="_blank" rel="noopener" aria-label="Open {{ $image['name'] ?: $discussion['title'] }} image">
                            <img src="{{ $image['url'] }}" alt="{{ $image['name'] ?: $discussion['title'] }}">
                        </a>
                    </figure>
                @endforeach
            </div>
        @endif
       
        <div class="thread-hero-actions">
            <form class="thread-notify-form" method="POST" action="{{ route('studyhub.student.discussions.notifications', $discussion['id']) }}">
                @csrf
                <button class="thread-notify-button {{ ! empty($isWatchingDiscussion) ? 'is-active' : '' }}" type="submit" data-loading-label="{{ ! empty($isWatchingDiscussion) ? 'Muting...' : 'Enabling...' }}">
                    <span class="icon-box">{!! $icons['bell'] ?? $icons['message'] !!}</span>
                    <span class="thread-notify-copy">
                        <strong>{{ ! empty($isWatchingDiscussion) ? 'Notifications on' : 'Notify me' }}</strong>
                        <small>{{ ! empty($isWatchingDiscussion) ? 'Replies will alert you' : 'Watch replies' }}</small>
                    </span>
                </button>
            </form>
            @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
                <form class="thread-delete-form" method="POST" action="{{ route('studyhub.student.discussions.delete', $discussion['id']) }}" onsubmit="return confirm('Delete this discussion and its replies?')">
                    @csrf
                    @method('DELETE')
                    <button class="thread-delete-button" type="submit" data-loading-label="Deleting...">Delete Discussion</button>
                </form>
            @endif
        </div>
    </section>

    <livewire:student-discussion-thread
        :discussion-id="$discussion['id']"
        :student-profile="$studentProfile"
        :icons="$icons"
    />
@endsection
