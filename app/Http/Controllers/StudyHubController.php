<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\GroupChatMessage;
use App\Models\GroupChatRead;
use App\Models\ResourceView;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use App\Services\StudyHub\StudyHubFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

abstract class StudyHubController extends Controller
{
    protected function studentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    protected function formatDiscussion(Discussion $discussion): array
    {
        return app(StudyHubFormatter::class)->discussion($discussion);
    }

    protected function formatGroup(StudyGroup $group): array
    {
        return app(StudyHubFormatter::class)->group($group);
    }

    protected function formatResource(StudyResource $resource): array
    {
        return array_merge(app(StudyHubFormatter::class)->resource($resource), [
            'can_delete' => Gate::allows('delete', $resource),
        ]);
    }

    protected function formatSession(StudySession $session): array
    {
        return app(StudyHubFormatter::class)->session($session);
    }

    protected function logActivity(string $type, string $title, string $description, ?StudyGroup $group = null, ?Model $subject = null, ?User $user = null): ActivityLog
    {
        $user ??= auth()->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'group_id' => $group?->id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
        ]);
    }

    protected function upcomingSessionsQuery(?User $user = null)
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        return $this->visibleSessionsQuery($user)
            ->where(function ($query) use ($today, $currentTime) {
                $query->where('session_date', '>', $today)
                    ->orWhere(function ($query) use ($today, $currentTime) {
                        $query->where('session_date', $today)
                            ->where('end_time', '>=', $currentTime);
                    });
            });
    }

    protected function renderStudent(string $view, array $data = []): View
    {
        $studentProfile = $this->getStudentProfile();
        $studentTheme = $this->getStudentTheme($studentProfile['theme'], $studentProfile['surface_style']);

        return view($view, array_merge($data, [
            'studentProfile' => $studentProfile,
            'studentTheme' => $studentTheme,
            'studentChatThreads' => $this->getStudentChatThreads(),
            'studentUnreadChatCount' => $this->getUnreadChatCountForStudent(),
            'icons' => config('studyhub.icons.student', []),
        ]));
    }

    protected function getStudentProfile(): array
    {
        $user = auth()->user();

        if (! $user) {
            return $this->defaultStudentProfile();
        }

        return [
            'display_name' => $user->display_name ?: $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url ?? '',
            'bio' => $user->bio ?: $this->defaultStudentProfile()['bio'],
            'theme' => $user->theme ?: 'forest',
            'surface_style' => $user->surface_style ?: 'soft',
            'interface_density' => $user->interface_density ?: 'comfortable',
        ];
    }

    protected function defaultStudentProfile(): array
    {
        return [
            'display_name' => 'Alex Student',
            'email' => 'alex.student@studyhub.edu',
            'avatar_url' => '',
            'bio' => 'Focused on collaborative learning, cleaner study routines, and building momentum every week.',
            'theme' => 'forest',
            'surface_style' => 'soft',
            'interface_density' => 'comfortable',
        ];
    }

    protected function getStudentGroups(): array
    {
        return StudyGroup::query()
            ->withCount(['members', 'resources'])
            ->orderBy('name')
            ->get()
            ->map(fn (StudyGroup $group) => $this->formatGroup($group))
            ->all();
    }

    protected function getStudentResources(): array
    {
        $viewerId = $this->studentUser()->id;

        return $this->visibleResourcesQuery()
            ->with($this->studentResourceDisplayRelations($viewerId))
            ->orderByDesc('uploaded_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StudyResource $resource) => $this->formatResource($resource))
            ->all();
    }

    protected function getPaginatedStudentResources(int $perPage = 12, array $filters = [])
    {
        $viewerId = $this->studentUser()->id;
        $query = $this->visibleResourcesQuery()
            ->with($this->studentResourceDisplayRelations($viewerId));

        $this->applyStudentResourceFilters($query, $filters);
        $this->applyStudentResourceSort($query, $filters['sort'] ?? 'newest');

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (StudyResource $resource) => $this->formatResource($resource));
    }

    protected function studentResourceDisplayRelations(int $viewerId): array
    {
        return [
            'group',
            'uploader',
            'latestReview.reviewer',
            'reviews' => fn ($query) => $query->where('user_id', $viewerId)->with('reviewer'),
            'savedResources' => fn ($query) => $query->where('user_id', $viewerId)->with('folder'),
        ];
    }

    protected function getStudentResourceFilterGroups(): array
    {
        $query = StudyGroup::query()
            ->whereHas('resources');

        $this->applyVisibleGroupContentConstraint($query, $this->studentUser());

        return $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (StudyGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])
            ->all();
    }

    protected function applyStudentResourceFilters($query, array $filters): void
    {
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function ($query) use ($like) {
                $query
                    ->where('name', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhereHas('group', function ($query) use ($like) {
                        $query
                            ->where('name', 'like', $like)
                            ->orWhere('category', 'like', $like);
                    })
                    ->orWhereHas('uploader', function ($query) use ($like) {
                        $query
                            ->where('name', 'like', $like)
                            ->orWhere('display_name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        $category = trim((string) ($filters['category'] ?? ''));

        if ($category !== '') {
            $query->where('category', $category);
        }

        $groupId = $filters['group_id'] ?? null;

        if ($groupId !== null && $groupId !== '') {
            $query->where('group_id', (int) $groupId);
        }

        $availability = $filters['availability'] ?? '';

        if ($availability === 'downloadable') {
            $query
                ->whereNotNull('path')
                ->where('path', '<>', '');
        }

        if ($availability === 'unavailable') {
            $query->where(function ($query) {
                $query
                    ->whereNull('path')
                    ->orWhere('path', '');
            });
        }
    }

    protected function applyStudentResourceSort($query, string $sort): void
    {
        match ($sort) {
            'most_downloaded' => $query->orderByDesc('download_count'),
            'highest_rated' => $query->orderByDesc('rating_average')->orderByDesc('rating_count'),
            default => null,
        };

        $query
            ->orderByDesc('uploaded_at')
            ->orderByDesc('created_at');
    }

    protected function recordResourceView(StudyResource $resource, ?User $user = null): void
    {
        $user ??= $this->studentUser();

        ResourceView::updateOrCreate([
            'user_id' => $user->id,
            'study_resource_id' => $resource->id,
        ], [
            'viewed_at' => now(),
        ]);
    }

    protected function getStudentDiscussions(): array
    {
        return $this->visibleDiscussionsQuery()
            ->with([
                'author',
                'group',
                'helpfulVotes' => fn ($query) => $query->where('user_id', auth()->id()),
            ])
            ->withCount(['replies', 'helpfulVotes'])
            ->orderByDesc('last_active_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Discussion $discussion) => $this->formatDiscussion($discussion))
            ->all();
    }

    protected function getPaginatedStudentDiscussions(int $perPage = 10, array $filters = [])
    {
        $query = $this->visibleDiscussionsQuery()
            ->with([
                'author',
                'group',
                'helpfulVotes' => fn ($query) => $query->where('user_id', auth()->id()),
            ])
            ->withCount(['replies', 'helpfulVotes']);

        $this->applyStudentDiscussionFilters($query, $filters);
        $this->applyStudentDiscussionSort($query, $filters['tab'] ?? 'all');

        return $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Discussion $discussion) => $this->formatDiscussion($discussion));
    }

    protected function applyStudentDiscussionFilters($query, array $filters): void
    {
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function ($query) use ($like) {
                $query
                    ->where('title', 'like', $like)
                    ->orWhere('body', 'like', $like)
                    ->orWhereHas('group', fn ($query) => $query->where('name', 'like', $like))
                    ->orWhereHas('author', function ($query) use ($like) {
                        $query
                            ->where('name', 'like', $like)
                            ->orWhere('display_name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        match ($filters['tab'] ?? 'all') {
            'unanswered' => $query->whereDoesntHave('replies'),
            'mine' => $query->whereIn('group_id', $this->getJoinedGroupIds()),
            default => null,
        };
    }

    protected function applyStudentDiscussionSort($query, string $tab): void
    {
        if ($tab === 'helpful') {
            $query->orderByDesc('helpful_votes_count');
        }

        $query
            ->orderByDesc('last_active_at')
            ->orderByDesc('created_at');
    }

    protected function getStudentSessions(): array
    {
        return $this->visibleSessionsQuery()
            ->with(['group', 'creator', 'attendees'])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (StudySession $session) => $this->formatSession($session))
            ->all();
    }

    protected function getPaginatedStudentSessions(int $perPage = 10)
    {
        return $this->visibleSessionsQuery()
            ->with(['group', 'creator', 'attendees'])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (StudySession $session) => $this->formatSession($session));
    }

    protected function getStudentDiscussionReplies(int $discussionId): array
    {
        $discussion = Discussion::query()
            ->with(['group', 'replies.author', 'replies.childReplies.author'])
            ->find($discussionId);

        if (! $discussion || Gate::denies('view', $discussion)) {
            return [];
        }

        return $discussion->replies
            ->whereNull('parent_reply_id')
            ->sortBy('created_at')
            ->values()
            ->map(fn ($reply) => [
                'id' => $reply->id,
                'author' => $reply->author?->display_name ?: $reply->author?->name ?: 'Unknown',
                'body' => $reply->body,
                'time' => $this->humanizeTime($reply->created_at),
                'children' => $reply->childReplies
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn ($childReply) => [
                        'id' => $childReply->id,
                        'author' => $childReply->author?->display_name ?: $childReply->author?->name ?: 'Unknown',
                        'body' => $childReply->body,
                        'time' => $this->humanizeTime($childReply->created_at),
                        'parent_author' => $reply->author?->display_name ?: $reply->author?->name ?: 'Unknown',
                    ])
                    ->all(),
            ])
            ->all();
    }

    protected function getJoinedGroupIds(): array
    {
        return $this->studentUser()
            ->joinedGroups()
            ->pluck('study_groups.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function getJoinedGroups(): array
    {
        $joinedIds = $this->getJoinedGroupIds();

        return collect($this->getStudentGroups())
            ->filter(fn (array $group) => in_array((int) $group['id'], $joinedIds, true))
            ->values()
            ->all();
    }

    protected function getGroupChatMessages(StudyGroup $group, int $limit = 80): array
    {
        return $group->chatMessages()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (GroupChatMessage $message) => [
                'id' => $message->id,
                'author' => $message->user?->display_name ?: $message->user?->name ?: 'StudyHub Member',
                'author_initials' => $this->initials($message->user?->display_name ?: $message->user?->name ?: 'StudyHub Member'),
                'author_avatar_url' => $message->user?->avatar_url ?: '',
                'is_mine' => (int) $message->user_id === (int) auth()->id(),
                'body' => $message->body,
                'time' => $this->humanizeTime($message->created_at),
                'timestamp' => optional($message->created_at)->format('M j, g:i A'),
            ])
            ->all();
    }

    protected function getStudentChatThreads(): array
    {
        $user = $this->studentUser();
        $joinedGroupIds = $this->getJoinedGroupIds();

        if (empty($joinedGroupIds)) {
            return [];
        }

        return StudyGroup::query()
            ->whereIn('id', $joinedGroupIds)
            ->with([
                'chatReads' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->withMax('chatMessages', 'created_at')
            ->orderByDesc('chat_messages_max_created_at')
            ->orderBy('name')
            ->get()
            ->map(function (StudyGroup $group) use ($user) {
                $latestMessage = $group->chatMessages()->with('user')->latest()->first();
                $lastReadAt = $group->chatReads->first()?->last_read_at;
                $unreadCount = $group->chatMessages()
                    ->where('user_id', '<>', $user->id)
                    ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
                    ->count();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'initials' => $this->initials($group->name),
                    'latest_author' => $latestMessage?->user?->display_name ?: $latestMessage?->user?->name ?: '',
                    'latest_body' => $latestMessage?->body ?: 'No messages yet',
                    'latest_time' => $latestMessage ? $this->humanizeTime($latestMessage->created_at) : '',
                    'unread_count' => $unreadCount,
                    'messages' => $this->getGroupChatMessages($group, 30),
                ];
            })
            ->all();
    }

    protected function getUnreadChatCountForStudent(): int
    {
        $user = $this->studentUser();
        $joinedGroupIds = $this->getJoinedGroupIds();

        if (empty($joinedGroupIds)) {
            return 0;
        }

        return StudyGroup::query()
            ->whereIn('id', $joinedGroupIds)
            ->get()
            ->sum(function (StudyGroup $group) use ($user) {
                $lastReadAt = GroupChatRead::query()
                    ->where('study_group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->value('last_read_at');

                return $group->chatMessages()
                    ->where('user_id', '<>', $user->id)
                    ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
                    ->count();
            });
    }

    protected function markGroupChatRead(StudyGroup $group): void
    {
        GroupChatRead::updateOrCreate([
            'study_group_id' => $group->id,
            'user_id' => $this->studentUser()->id,
        ], [
            'last_read_at' => now(),
        ]);
    }

    protected function visibleResourcesQuery(?User $user = null)
    {
        $user ??= $this->studentUser();

        return StudyResource::query()
            ->whereHas('group', fn ($query) => $this->applyVisibleGroupContentConstraint($query, $user));
    }

    protected function visibleDiscussionsQuery(?User $user = null)
    {
        $user ??= $this->studentUser();

        return Discussion::query()
            ->whereHas('group', fn ($query) => $this->applyVisibleGroupContentConstraint($query, $user));
    }

    protected function visibleSessionsQuery(?User $user = null)
    {
        $user ??= $this->studentUser();

        return StudySession::query()
            ->whereHas('group', fn ($query) => $this->applyVisibleGroupContentConstraint($query, $user));
    }

    protected function applyVisibleGroupContentConstraint($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($query) use ($user) {
            $query->where('visibility', 'public')
                ->orWhere('owner_id', $user->id)
                ->orWhereHas('members', fn ($query) => $query->where('users.id', $user->id));
        });
    }

    protected function humanizeTime(Carbon|string|null $value): string
    {
        return app(StudyHubFormatter::class)->humanizeTime($value);
    }

    protected function initials(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return '??';
        }

        return $parts
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    protected function groupColorForMeetingStyle(string $meetingStyle): string
    {
        return match ($meetingStyle) {
            'online' => '#3282B8',
            'hybrid' => '#FF6B35',
            default => '#4A955F',
        };
    }

    protected function studentResourceCategories(): array
    {
        return ['All', ...$this->studentResourceCategoriesWithoutAll()];
    }

    protected function studentResourceCategoriesWithoutAll(): array
    {
        return ['Lecture Notes', 'Study Guide', 'Assignments', 'Code', 'Presentations'];
    }

    protected function studentGroupCategories(): array
    {
        return [
            'General',
            'Mathematics',
            'Programming',
            'Science',
            'Language',
            'Business',
            'Design',
            'Computer Science',
            'Information Systems',
            'Artificial Intelligence',
            'Other',
        ];
    }

    protected function formatFileSize(int $bytes): string
    {
        return app(StudyHubFormatter::class)->fileSize($bytes);
    }

    protected function studentProfileOptions(): array
    {
        return [
            'themes' => [
                ['value' => 'light', 'label' => 'Light', 'description' => 'Bright student workspace.'],
                ['value' => 'dark', 'label' => 'Dark', 'description' => 'Dark student workspace.'],
            ],
            'surface_styles' => [
                ['value' => 'soft', 'label' => 'Soft', 'description' => 'Rounded, airy surfaces and smooth shadows.'],
                ['value' => 'glass', 'label' => 'Glass', 'description' => 'More layered cards with translucent highlights.'],
                ['value' => 'contrast', 'label' => 'Contrast', 'description' => 'Sharper panels and stronger definition.'],
            ],
            'densities' => [
                ['value' => 'comfortable', 'label' => 'Comfortable', 'description' => 'Relaxed spacing for everyday use.'],
                ['value' => 'compact', 'label' => 'Compact', 'description' => 'Tighter spacing for more content.'],
            ],
        ];
    }

    protected function engagementRate(): int
    {
        $totalUsers = max(User::count(), 1);
        $engagedUsers = User::query()
            ->whereHas('joinedGroups')
            ->orWhereHas('discussions')
            ->orWhereHas('attendingSessions')
            ->count();

        return (int) round(($engagedUsers / $totalUsers) * 100);
    }

    protected function getStudentTheme(string $theme, string $surfaceStyle): array
    {
        $themes = [
            'forest' => [
                'page_bg' => 'radial-gradient(circle at top left, rgba(145, 212, 164, 0.28), transparent 28%), linear-gradient(180deg, #f4f6f1 0%, #edf2ec 100%)',
                'sidebar_bg' => 'linear-gradient(180deg, rgba(17, 66, 40, 0.96) 0%, rgba(32, 95, 59, 0.98) 52%, rgba(41, 117, 72, 1) 100%)',
                'accent' => '#49b670',
                'accent_soft' => '#67d38b',
                'accent_pale' => '#dff6e3',
                'accent_text' => '#133521',
            ],
            'ocean' => [
                'page_bg' => 'radial-gradient(circle at top left, rgba(152, 205, 226, 0.26), transparent 26%), linear-gradient(180deg, #f2f7fa 0%, #e8f0f6 100%)',
                'sidebar_bg' => 'linear-gradient(180deg, rgba(20, 58, 94, 0.98) 0%, rgba(28, 89, 130, 0.98) 54%, rgba(45, 123, 171, 1) 100%)',
                'accent' => '#3f8fcb',
                'accent_soft' => '#64afdf',
                'accent_pale' => '#deeffa',
                'accent_text' => '#163655',
            ],
            'sunset' => [
                'page_bg' => 'radial-gradient(circle at top left, rgba(255, 205, 169, 0.26), transparent 26%), linear-gradient(180deg, #f9f4ef 0%, #f4ece4 100%)',
                'sidebar_bg' => 'linear-gradient(180deg, rgba(105, 49, 31, 0.98) 0%, rgba(144, 73, 45, 0.98) 54%, rgba(191, 110, 67, 1) 100%)',
                'accent' => '#d17344',
                'accent_soft' => '#e09362',
                'accent_pale' => '#f9e1d3',
                'accent_text' => '#5c2d18',
            ],
            'dark' => [
                'page_bg' => 'radial-gradient(circle at top left, rgba(73, 182, 112, 0.18), transparent 30%), radial-gradient(circle at top right, rgba(71, 115, 255, 0.12), transparent 28%), linear-gradient(180deg, #101813 0%, #0b1110 100%)',
                'sidebar_bg' => 'linear-gradient(180deg, rgba(9, 18, 15, 0.98) 0%, rgba(16, 38, 28, 0.98) 54%, rgba(22, 67, 42, 1) 100%)',
                'accent' => '#55d987',
                'accent_soft' => '#7ee6a4',
                'accent_pale' => '#1d3f2d',
                'accent_text' => '#dfffe9',
            ],
        ];

        $surfaceStyles = [
            'soft' => ['card_radius' => '22px', 'card_shadow' => '0 24px 42px rgba(66, 95, 76, 0.10)', 'card_border' => 'rgba(195, 215, 203, 0.92)'],
            'glass' => ['card_radius' => '24px', 'card_shadow' => '0 24px 46px rgba(45, 75, 62, 0.12)', 'card_border' => 'rgba(255, 255, 255, 0.40)'],
            'contrast' => ['card_radius' => '18px', 'card_shadow' => '0 20px 38px rgba(37, 58, 48, 0.16)', 'card_border' => 'rgba(149, 176, 159, 0.95)'],
        ];

        return array_merge($themes[$theme] ?? $themes['forest'], $surfaceStyles[$surfaceStyle] ?? $surfaceStyles['soft']);
    }

    protected function renderAdmin(string $view, array $data = []): View
    {
        return view($view, array_merge($data, [
            'icons' => config('studyhub.icons.admin', []),
        ]));
    }
}
