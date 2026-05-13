<?php

use App\Models\Discussion;
use App\Models\DiscussionNotificationPreference;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public array $icons = [];

    public bool $open = false;

    public function mount(array $icons = []): void
    {
        $this->icons = $icons;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markRead(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        DiscussionNotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('notify_replies', true)
            ->update(['last_read_at' => now()]);

        session()->put('student_discussion_posts_read_at', now()->toDateTimeString());
    }

    public function notifications(): array
    {
        $user = Auth::user();

        if (! $user) {
            return ['items' => [], 'unread_count' => 0];
        }

        $discussionPostsReadAt = session('student_discussion_posts_read_at')
            ? Carbon::parse(session('student_discussion_posts_read_at'))
            : now()->subDay();

        $visibleGroupIds = StudyGroup::query()
            ->where(function ($query) use ($user) {
                if ($user->isAdmin()) {
                    return;
                }

                $query->where('visibility', 'public')
                    ->orWhere('owner_id', $user->id)
                    ->orWhereHas('members', fn ($query) => $query->where('users.id', $user->id));
            })
            ->pluck('id');

        $discussionItems = Discussion::query()
            ->with(['author', 'group'])
            ->whereIn('group_id', $visibleGroupIds)
            ->where('author_id', '!=', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Discussion $discussion) => [
                'title' => ($discussion->author?->display_name ?: $discussion->author?->name ?: 'Someone').' posted in '.$discussion->group?->name,
                'body' => $discussion->title,
                'time' => $this->humanizeTime($discussion->created_at),
                'url' => route('studyhub.student.discussions.show', $discussion),
                'is_unread' => $discussion->created_at->greaterThan($discussionPostsReadAt),
                'sort_at' => $discussion->created_at,
            ]);

        $watchedReplies = DiscussionReply::query()
            ->with(['author', 'discussion'])
            ->join('discussion_notification_preferences as preferences', function ($join) use ($user) {
                $join->on('preferences.discussion_id', '=', 'discussion_replies.discussion_id')
                    ->where('preferences.user_id', '=', $user->id)
                    ->where('preferences.notify_replies', '=', true);
            })
            ->where('discussion_replies.author_id', '!=', $user->id)
            ->whereColumn('discussion_replies.created_at', '>', 'preferences.created_at')
            ->select('discussion_replies.*', 'preferences.last_read_at as preference_last_read_at')
            ->orderByDesc('discussion_replies.created_at')
            ->limit(8)
            ->get()
            ->map(function (DiscussionReply $reply) {
                $lastReadAt = $reply->preference_last_read_at ? Carbon::parse($reply->preference_last_read_at) : null;

                return [
                    'title' => ($reply->author?->display_name ?: $reply->author?->name ?: 'Someone').' replied to a watched discussion',
                    'body' => $reply->discussion?->title ?: 'Discussion reply',
                    'time' => $this->humanizeTime($reply->created_at),
                    'url' => route('studyhub.student.discussions.show', $reply->discussion_id),
                    'is_unread' => ! $lastReadAt || $reply->created_at->greaterThan($lastReadAt),
                    'sort_at' => $reply->created_at,
                ];
            });

        $items = $discussionItems
            ->merge($watchedReplies)
            ->sortByDesc('sort_at')
            ->take(8)
            ->map(fn (array $item) => collect($item)->except('sort_at')->all())
            ->values()
            ->all();

        return [
            'items' => $items,
            'unread_count' => collect($items)->where('is_unread', true)->count(),
        ];
    }

    private function humanizeTime(Carbon|string|null $value): string
    {
        if (! $value) {
            return 'Recently';
        }

        return Carbon::parse($value)->diffForHumans();
    }
};
?>

@php($notifications = $this->notifications())

<div
    class="student-notification-menu {{ $open ? 'is-open' : '' }}"
    wire:poll.30s
    wire:loading.class="is-notification-toggling"
    wire:target="toggle,close"
>
    <button
        class="student-top-icon-button student-notification-button"
        type="button"
        aria-label="Open notifications"
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        wire:click="toggle"
    >
        <span class="icon-box">{!! $icons['bell'] ?? $icons['message'] ?? '' !!}</span>
        @if (($notifications['unread_count'] ?? 0) > 0)
            <span class="student-notification-badge">{{ min((int) $notifications['unread_count'], 9) }}</span>
        @endif
    </button>

    <div class="student-notification-dropdown">
        <div class="student-notification-header">
            <span>Notifications</span>
            <div class="student-notification-header-actions">
                @if (($notifications['unread_count'] ?? 0) > 0)
                    <button class="student-notification-mark-read" type="button" wire:click="markRead">Mark read</button>
                @endif
            </div>
        </div>
        <div class="student-notification-skeleton" wire:loading.grid wire:target="markRead">
            <span></span>
            <span></span>
        </div>

        <div class="student-notification-list" wire:loading.class="is-livewire-updating" wire:target="markRead">
            @forelse (($notifications['items'] ?? []) as $notification)
                <a class="student-notification-item {{ ! empty($notification['is_unread']) ? 'is-unread' : '' }}" href="{{ $notification['url'] }}">
                    <span class="student-notification-dot" aria-hidden="true"></span>
                    <span class="student-notification-copy">
                        <strong>{{ $notification['title'] }}</strong>
                        <span>{{ $notification['body'] }}</span>
                        <small>{{ $notification['time'] }}</small>
                    </span>
                </a>
            @empty
                <div class="student-notification-empty">
                    <span class="icon-box">{!! $icons['message'] ?? '' !!}</span>
                    <strong>No notifications yet</strong>
                    <span>Watch a discussion to get reply alerts here.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
