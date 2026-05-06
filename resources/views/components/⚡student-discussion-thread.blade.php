<?php

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Services\StudyHub\StudyHubFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public int $discussionId;

    public array $icons = [];

    public array $studentProfile = [];

    public string $reply = '';

    public ?int $parentReplyId = null;

    public string $parentAuthor = '';

    public function mount(int $discussionId, array $icons = [], array $studentProfile = []): void
    {
        $this->discussionId = $discussionId;
        $this->icons = $icons;
        $this->studentProfile = $studentProfile;
    }

    public function setReplyTarget(int $replyId, string $authorName): void
    {
        $this->parentReplyId = $replyId;
        $this->parentAuthor = $authorName;
    }

    public function clearReplyTarget(): void
    {
        $this->parentReplyId = null;
        $this->parentAuthor = '';
    }

    public function postReply(): void
    {
        $validated = $this->validate([
            'reply' => ['required', 'string', 'max:500'],
            'parentReplyId' => ['nullable', 'integer'],
        ], [
            'reply.required' => 'Write a reply before posting.',
            'reply.max' => 'Replies must be 500 characters or less.',
        ]);

        $discussion = Discussion::query()
            ->with('group')
            ->findOrFail($this->discussionId);

        if (Gate::denies('reply', $discussion)) {
            $this->addError('reply', 'You need to join that private group before replying.');

            return;
        }

        $parentReply = null;

        if ($this->parentReplyId) {
            $parentReply = DiscussionReply::query()
                ->where('discussion_id', $discussion->id)
                ->find($this->parentReplyId);

            if (! $parentReply) {
                $this->addError('reply', 'The reply you selected could not be found.');

                return;
            }
        }

        $discussion->replies()->create([
            'parent_reply_id' => $parentReply?->id,
            'author_id' => Auth::id(),
            'body' => trim($validated['reply']),
        ]);
        $discussion->update(['last_active_at' => now()]);

        $this->reset(['reply', 'parentReplyId', 'parentAuthor']);
        session()->flash('thread_status', 'Reply posted successfully.');
    }

    public function replies(): array
    {
        $discussion = Discussion::query()
            ->with(['group', 'replies.author', 'replies.childReplies.author'])
            ->find($this->discussionId);

        if (! $discussion || Gate::denies('view', $discussion)) {
            return [];
        }

        return $discussion->replies
            ->whereNull('parent_reply_id')
            ->sortBy('created_at')
            ->values()
            ->map(fn (DiscussionReply $reply) => [
                'id' => $reply->id,
                'author' => $this->authorName($reply),
                'author_avatar_url' => $reply->author?->avatar_url ?: '',
                'author_initials' => $this->initials($this->authorName($reply)),
                'body' => $reply->body,
                'time' => app(StudyHubFormatter::class)->humanizeTime($reply->created_at),
                'children' => $reply->childReplies
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn (DiscussionReply $childReply) => [
                        'id' => $childReply->id,
                        'author' => $this->authorName($childReply),
                        'author_avatar_url' => $childReply->author?->avatar_url ?: '',
                        'author_initials' => $this->initials($this->authorName($childReply)),
                        'body' => $childReply->body,
                        'time' => app(StudyHubFormatter::class)->humanizeTime($childReply->created_at),
                        'parent_author' => $this->authorName($reply),
                    ])
                    ->all(),
            ])
            ->all();
    }

    public function replyCount(): int
    {
        return DiscussionReply::query()
            ->where('discussion_id', $this->discussionId)
            ->count();
    }

    private function authorName(DiscussionReply $reply): string
    {
        return $reply->author?->display_name ?: $reply->author?->name ?: 'Unknown';
    }

    public function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = strtoupper(substr($parts[0] ?? $name, 0, 1));
        $last = strtoupper(substr($parts[count($parts) - 1] ?? $name, 0, 1));

        return $first.$last;
    }
};
?>

@php($replies = $this->replies())

<section class="thread-layout">
    <article class="thread-card">
        <div class="thread-card-header">
            <div class="thread-card-title-lockup">
                <h3>Conversation</h3>
                <small>{{ $this->replyCount() }} {{ str('reply')->plural($this->replyCount()) }}</small>
            </div>
            <span>{{ $this->replyCount() }}</span>
        </div>
        <div class="thread-livewire-skeleton" wire:loading.flex wire:target="postReply,setReplyTarget,clearReplyTarget">
            <span></span>
            <span></span>
        </div>

        <div class="thread-replies" wire:loading.class="is-livewire-updating" wire:target="postReply,setReplyTarget,clearReplyTarget">
            @forelse ($replies as $replyItem)
                <article class="thread-reply {{ ($replyItem['author'] ?? '') === ($studentProfile['display_name'] ?? '') ? 'is-own' : '' }}" wire:key="reply-{{ $replyItem['id'] }}">
                    <div class="thread-reply-header">
                        <span class="thread-reply-avatar">
                            @if (! empty($replyItem['author_avatar_url']))
                                <img src="{{ $replyItem['author_avatar_url'] }}" alt="{{ $replyItem['author'] }}">
                            @else
                                {{ $replyItem['author_initials'] ?? $this->initials($replyItem['author']) }}
                            @endif
                        </span>
                        <span class="thread-reply-profile">
                            <span class="thread-reply-author">
                                {{ $replyItem['author'] }}
                                @if (($replyItem['author'] ?? '') === ($studentProfile['display_name'] ?? ''))
                                    <span class="thread-you-badge">You</span>
                                @endif
                            </span>
                            <span class="thread-reply-time">{{ $replyItem['time'] }}</span>
                        </span>
                    </div>
                    <p class="thread-reply-body">{{ $replyItem['body'] }}</p>
                    <div class="thread-reply-actions">
                        <button
                            class="thread-inline-reply"
                            type="button"
                            wire:click="setReplyTarget({{ $replyItem['id'] }}, @js($replyItem['author']))"
                            onclick="document.getElementById('thread-reply-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                        >
                            Reply
                        </button>
                    </div>

                    @if (! empty($replyItem['children']))
                        <div class="thread-children">
                            @foreach ($replyItem['children'] as $childReply)
                                <article class="thread-child-reply {{ ($childReply['author'] ?? '') === ($studentProfile['display_name'] ?? '') ? 'is-own' : '' }}" wire:key="reply-{{ $replyItem['id'] }}-child-{{ $childReply['id'] }}">
                                    <div class="thread-child-header">
                                        <span class="thread-child-avatar">
                                            @if (! empty($childReply['author_avatar_url']))
                                                <img src="{{ $childReply['author_avatar_url'] }}" alt="{{ $childReply['author'] }}">
                                            @else
                                                {{ $childReply['author_initials'] ?? $this->initials($childReply['author']) }}
                                            @endif
                                        </span>
                                        <span class="thread-child-meta">
                                            <span class="thread-child-author">
                                                {{ $childReply['author'] }}
                                                @if (($childReply['author'] ?? '') === ($studentProfile['display_name'] ?? ''))
                                                    <span class="thread-you-badge">You</span>
                                                @endif
                                            </span>
                                            <span class="thread-child-time">{{ $childReply['time'] }}</span>
                                        </span>
                                    </div>
                                    <p class="thread-child-target">Replying to {{ $childReply['parent_author'] }}</p>
                                    <p class="thread-reply-body">{{ $childReply['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="thread-empty app-empty-state compact">
                    <span class="app-empty-icon">{!! $icons['message'] ?? '' !!}</span>
                    <strong>No replies yet</strong>
                    <span>Be the first to share an idea or answer.</span>
                </div>
            @endforelse
        </div>
    </article>

    <article class="thread-card thread-compose-card" id="thread-reply-form">
        <div class="thread-card-header">
            <div class="thread-card-title-lockup">
                <h3>{{ $parentReplyId ? 'Reply to '.$parentAuthor : 'Join the discussion' }}</h3>
                <small>{{ $parentReplyId ? 'Nested reply' : 'Add your thought' }}</small>
            </div>
        </div>

        @if (session('thread_status'))
            <div class="thread-status">{{ session('thread_status') }}</div>
        @endif

        @if ($errors->any())
            <div class="thread-errors" role="alert" aria-live="polite">
                <strong>Reply was not posted</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="thread-form" wire:submit.prevent="postReply" wire:loading.class="is-saving" wire:target="postReply">
            <div class="thread-replying-to {{ $parentReplyId ? 'is-active' : '' }}">
                <span>
                    @if ($parentReplyId)
                        Replying directly to {{ $parentAuthor ?: 'this comment' }}.
                    @else
                        Replying to this discussion.
                    @endif
                </span>
                <button class="thread-replying-clear" type="button" wire:click="clearReplyTarget">Clear</button>
            </div>
            <textarea
                class="thread-textarea @error('reply') is-invalid @enderror"
                maxlength="500"
                placeholder="Write your reply..."
                wire:model.live.debounce.300ms="reply"
                required
            ></textarea>
            @error('reply')
                <span class="student-field-error">{{ $message }}</span>
            @enderror
            <div class="thread-submit-row">
                <button class="thread-submit" type="submit" wire:loading.attr="disabled" wire:target="postReply" @disabled(trim($reply) === '')>
                    <span wire:loading.remove wire:target="postReply">Post Reply</span>
                    <span wire:loading wire:target="postReply"><span class="student-button-spinner" aria-hidden="true"></span>Posting reply...</span>
                </button>
            </div>
        </form>
    </article>
</section>
