<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionHelpfulVote;
use App\Models\DiscussionNotificationPreference;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDiscussionController extends StudyHubController
{
    public function index(Request $request): View
    {
        $filters = [
            'tab' => $request->query('tab', 'all'),
            'q' => trim((string) $request->query('q', '')),
        ];
        $discussions = $this->getPaginatedStudentDiscussions(filters: $filters);
        $discussionItems = collect($discussions->items());
        $profile = $this->getStudentProfile();
        $stats = [
            ['label' => 'Total Discussions', 'value' => (string) $discussions->total(), 'icon' => 'discussion', 'color' => '#0F4C75'],
            ['label' => 'Your Posts', 'value' => (string) $discussionItems->where('author', $profile['display_name'])->count(), 'icon' => 'user', 'color' => '#3282B8'],
            ['label' => 'Trending Topics', 'value' => (string) $discussionItems->where('trending', true)->count(), 'icon' => 'trend', 'color' => '#63bb7a'],
        ];

        return $this->renderStudent('studyhub.student.discussions', [
            'stats' => $stats,
            'discussions' => $discussions,
            'discussionGroups' => $this->getJoinedGroups(),
            'discussionFilters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $joinedGroupIds = collect($this->getJoinedGroups())
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'group_id' => ['required', 'in:'.implode(',', $joinedGroupIds)],
            'body' => ['required', 'string', 'max:500'],
            'discussion_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $group = StudyGroup::find((int) $validated['group_id']);

        if (! $group || Gate::denies('createContent', $group)) {
            return back()->with('status', 'You can only post discussions to groups you joined.');
        }

        $imagePath = null;
        $imageOriginalName = null;
        $imageMimeType = null;

        if ($request->hasFile('discussion_image')) {
            $image = $request->file('discussion_image');
            $imagePath = $image->store('studyhub-discussion-images', 'local');
            $imageOriginalName = $image->getClientOriginalName();
            $imageMimeType = $image->getMimeType();
        }

        $discussion = Discussion::create([
            'group_id' => (int) $validated['group_id'],
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'image_path' => $imagePath,
            'image_original_name' => $imageOriginalName,
            'image_mime_type' => $imageMimeType,
            'views' => 1,
            'trending' => false,
            'last_active_at' => now(),
        ]);
        $this->logActivity(
            'discussion_posted',
            'Discussion posted',
            ($request->user()->display_name ?: $request->user()->name).' posted "'.$discussion->title.'" in '.$group->name.'.',
            $group,
            $discussion,
        );
        DiscussionNotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id, 'discussion_id' => $discussion->id],
            ['notify_replies' => true, 'last_read_at' => now()],
        );

        return redirect()
            ->route('studyhub.student.discussions')
            ->with('status', 'Discussion posted successfully.');
    }

    public function image(Discussion $discussion): RedirectResponse|StreamedResponse
    {
        $discussion->load('group');

        if (Gate::denies('view', $discussion)) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'You need to join that private group before viewing its discussion.');
        }

        if (! $discussion->image_path || ! Storage::disk('local')->exists($discussion->image_path)) {
            return redirect()
                ->route('studyhub.student.discussions.show', $discussion)
                ->with('status', 'That discussion image could not be found.');
        }

        return Storage::disk('local')->response(
            $discussion->image_path,
            $discussion->image_original_name ?: basename($discussion->image_path),
            ['Content-Type' => $discussion->image_mime_type ?: 'image/jpeg'],
        );
    }

    public function show(Discussion $discussion): View|RedirectResponse
    {
        $discussion->load(['author', 'group', 'replies.author']);

        if (Gate::denies('view', $discussion)) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'You need to join that private group before viewing its discussion.');
        }

        $discussion->increment('views');
        $discussion->refresh();

        return $this->renderStudent('studyhub.student.discussion-thread', [
            'discussion' => $this->formatDiscussion($discussion->loadMissing(['author', 'group', 'replies'])),
            'isWatchingDiscussion' => DiscussionNotificationPreference::query()
                ->where('user_id', auth()->id())
                ->where('discussion_id', $discussion->id)
                ->where('notify_replies', true)
                ->exists(),
        ]);
    }

    public function destroy(Request $request, Discussion $discussion): RedirectResponse
    {
        $discussion->load(['author', 'group']);

        if (Gate::denies('view', $discussion)) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'You need to join that private group before managing its discussion.');
        }

        if (Gate::denies('delete', $discussion)) {
            return back()->with('status', 'You can only delete discussions you created.');
        }

        if ($discussion->image_path) {
            Storage::disk('local')->delete($discussion->image_path);
        }

        $discussion->delete();

        return redirect()
            ->route('studyhub.student.discussions')
            ->with('status', 'Discussion deleted successfully.');
    }

    public function reply(Request $request, Discussion $discussion): RedirectResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:500'],
            'parent_reply_id' => ['nullable', 'integer'],
        ]);

        $discussion->load('group');

        if (Gate::denies('reply', $discussion)) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'You need to join that private group before replying.');
        }

        $parentReply = null;

        if ($validated['parent_reply_id'] ?? null) {
            $parentReply = DiscussionReply::query()
                ->where('discussion_id', $discussion->id)
                ->find($validated['parent_reply_id']);

            if (! $parentReply) {
                return back()->with('status', 'The reply you selected could not be found.');
            }
        }

        $discussion->replies()->create([
            'parent_reply_id' => $parentReply?->id,
            'author_id' => $request->user()->id,
            'body' => $request->string('reply')->trim()->value(),
        ]);
        $discussion->update(['last_active_at' => now()]);

        return redirect()
            ->route('studyhub.student.discussions.show', $discussion)
            ->with('status', 'Reply posted successfully.');
    }

    public function toggleNotifications(Request $request, Discussion $discussion): RedirectResponse
    {
        $discussion->load('group');

        if (Gate::denies('view', $discussion)) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'You need to join that private group before watching its discussion.');
        }

        $preference = DiscussionNotificationPreference::firstOrNew([
            'user_id' => $request->user()->id,
            'discussion_id' => $discussion->id,
        ]);
        $isEnabled = ! (bool) $preference->notify_replies;

        $preference->fill([
            'notify_replies' => $isEnabled,
            'last_read_at' => now(),
        ])->save();

        return back()->with('status', $isEnabled ? 'Notifications enabled for this discussion.' : 'Notifications muted for this discussion.');
    }

    public function toggleHelpful(Request $request, Discussion $discussion): RedirectResponse|JsonResponse
    {
        $discussion->load('group');

        if (Gate::denies('view', $discussion)) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'You need to join that private group before voting on its discussion.');
        }

        $vote = DiscussionHelpfulVote::query()
            ->where('discussion_id', $discussion->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($vote) {
            $vote->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'helpful_votes' => $discussion->helpfulVotes()->count(),
                    'viewer_voted_helpful' => false,
                    'message' => 'Helpful vote removed.',
                ]);
            }

            return back()->with('status', 'Helpful vote removed.');
        }

        DiscussionHelpfulVote::create([
            'discussion_id' => $discussion->id,
            'user_id' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'helpful_votes' => $discussion->helpfulVotes()->count(),
                'viewer_voted_helpful' => true,
                'message' => 'Marked as helpful.',
            ]);
        }

        return back()->with('status', 'Marked as helpful.');
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        DiscussionNotificationPreference::query()
            ->where('user_id', $request->user()->id)
            ->where('notify_replies', true)
            ->update(['last_read_at' => now()]);
        $request->session()->put('student_discussion_posts_read_at', now()->toDateTimeString());

        return back()->with('status', 'Notifications marked as read.');
    }
}
