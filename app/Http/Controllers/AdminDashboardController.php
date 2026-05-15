<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends StudyHubController
{
    public function index(): View
    {
        $totalUsers = User::count();
        $studentCount = User::query()->where('role', 'student')->count();
        $activeStudentCount = User::query()
            ->where('role', 'student')
            ->where(function (Builder $query) {
                $query
                    ->whereHas('joinedGroups')
                    ->orWhereHas('discussions')
                    ->orWhereHas('attendingSessions');
            })
            ->count();
        $groupCount = StudyGroup::count();
        $resourceCount = StudyResource::count();
        $discussionCount = Discussion::count();
        $sessionCount = StudySession::count();

        $stats = [
            ['label' => 'Total Users', 'value' => (string) $totalUsers, 'change' => $studentCount.' students enrolled', 'icon' => 'users', 'color' => '#0F4C75'],
            ['label' => 'Active Students', 'value' => (string) $activeStudentCount, 'change' => $this->percentLabel($activeStudentCount, max($studentCount, 1)).' of students engaged', 'icon' => 'trend', 'color' => '#06D6A0'],
            ['label' => 'Study Groups', 'value' => (string) $groupCount, 'change' => StudyGroup::query()->where('visibility', 'private')->count().' private groups', 'icon' => 'groups', 'color' => '#3282B8'],
            ['label' => 'Resources', 'value' => (string) $resourceCount, 'change' => StudyResource::query()->where('created_at', '>=', now()->subDays(7))->count().' uploaded this week', 'icon' => 'book', 'color' => '#79532d'],
            ['label' => 'Discussions', 'value' => (string) $discussionCount, 'change' => Discussion::query()->where('created_at', '>=', now()->subDays(7))->count().' posted this week', 'icon' => 'discussion', 'color' => '#FF6B35'],
            ['label' => 'Sessions', 'value' => (string) $sessionCount, 'change' => StudySession::query()->where('session_date', '>=', today())->count().' upcoming sessions', 'icon' => 'activity', 'color' => '#0F4C75'],
        ];

        $userActivityData = $this->withPercentages($this->monthlyUserActivity(), 'users');

        $resourceData = $this->withPercentages(StudyResource::query()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category ?: 'Uncategorized',
                'count' => (int) $row->count,
            ])
            ->values()
            ->all(), 'count');

        $quickActions = [
            ['label' => 'Manage Users', 'description' => 'Create, edit, and verify accounts.', 'route' => route('studyhub.admin.users'), 'icon' => 'users', 'style' => 'primary'],
            ['label' => 'Review Groups', 'description' => 'Inspect group activity and content.', 'route' => route('studyhub.admin.groups'), 'icon' => 'groups', 'style' => 'secondary'],
            ['label' => 'View Reports', 'description' => 'Open platform analytics.', 'route' => route('studyhub.admin.reports'), 'icon' => 'reports', 'style' => 'secondary'],
            ['label' => 'Export Report', 'description' => 'Download the latest CSV summary.', 'route' => route('studyhub.admin.reports.export'), 'icon' => 'download', 'style' => 'secondary'],
        ];

        $recentAlerts = $this->dashboardAlerts();
        $recentActivity = $this->recentPlatformActivity();

        return $this->renderAdmin('studyhub.admin.dashboard', [
            'stats' => $stats,
            'quickActions' => $quickActions,
            'userActivityData' => $userActivityData,
            'resourceData' => $resourceData,
            'recentAlerts' => $recentAlerts,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function users(): View
    {
        $currentUser = auth()->user();
        $adminCount = User::query()->where('role', 'admin')->count();

        $users = User::query()
            ->withCount('joinedGroups')
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($currentUser, $adminCount) {
                $deleteBlocker = $this->userDeletionBlocker($currentUser, $user, $adminCount);

                return [
                    'id' => $user->id,
                    'name' => $user->display_name ?: $user->name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role),
                    'groups' => $user->role === 'student' ? (int) $user->joined_groups_count : 0,
                    'status' => $user->email_verified_at ? 'Verified' : 'Pending',
                    'join_date' => $user->created_at?->format('M j, Y') ?? now()->format('M j, Y'),
                    'can_delete' => $deleteBlocker === null,
                    'delete_reason' => $deleteBlocker,
                ];
            })
            ->all();

        $stats = [
            ['label' => 'Total Users', 'value' => (string) count($users), 'color' => '#0F4C75'],
            ['label' => 'Students', 'value' => (string) User::query()->where('role', 'student')->count(), 'color' => '#06D6A0'],
            ['label' => 'Admins', 'value' => (string) $adminCount, 'color' => '#FF6B35'],
            ['label' => 'Verified', 'value' => (string) User::query()->whereNotNull('email_verified_at')->count(), 'color' => '#3282B8'],
        ];

        return $this->renderAdmin('studyhub.admin.users', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    public function editUser(User $user): View
    {
        return $this->renderAdmin('studyhub.admin.user-edit', [
            'managedUser' => $user,
            'canDelete' => $this->userDeletionBlocker(auth()->user(), $user) === null,
            'deleteBlocker' => $this->userDeletionBlocker(auth()->user(), $user),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:student,admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'display_name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'bio' => $validated['role'] === 'admin'
                ? 'StudyHub administrator.'
                : 'New StudyHub member.',
            'theme' => 'forest',
            'surface_style' => 'soft',
            'interface_density' => 'comfortable',
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('studyhub.admin.users')
            ->with('status', 'User account created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:student,admin'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ((int) $user->id === (int) $request->user()->id && $validated['role'] !== $user->role) {
            return redirect()
                ->route('studyhub.admin.users.edit', $user)
                ->with('status', 'You cannot change your own admin role from this page.');
        }

        if ($user->isAdmin() && $validated['role'] !== 'admin' && User::query()->where('role', 'admin')->count() <= 1) {
            return redirect()
                ->route('studyhub.admin.users.edit', $user)
                ->with('status', 'At least one admin account must remain in the system.');
        }

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'name' => $validated['name'],
            'display_name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = now();
        }

        if (filled($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('studyhub.admin.users.edit', $user)
            ->with('status', 'User account updated successfully.');
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        $deleteBlocker = $this->userDeletionBlocker($request->user(), $user);

        if ($deleteBlocker !== null) {
            return redirect()
                ->route('studyhub.admin.users')
                ->with('status', $deleteBlocker);
        }

        $resourcePaths = $this->resourcePathsForUserDeletion($user);

        $userName = $user->display_name ?: $user->name;

        DB::transaction(fn () => $user->delete());
        $this->deleteStoredResourceFiles($resourcePaths);

        return redirect()
            ->route('studyhub.admin.users')
            ->with('status', $userName.' was deleted successfully.');
    }

    public function groups(): View
    {
        $summary = [
            ['label' => 'Monitored Groups', 'value' => (string) StudyGroup::count(), 'color' => '#0F4C75'],
            ['label' => 'Public', 'value' => (string) StudyGroup::query()->where('visibility', 'public')->count(), 'color' => '#3282B8'],
            ['label' => 'Private', 'value' => (string) StudyGroup::query()->where('visibility', 'private')->count(), 'color' => '#FF6B35'],
            ['label' => 'High Activity', 'value' => (string) StudyGroup::query()->has('discussions', '>=', 2)->count(), 'color' => '#06D6A0'],
        ];

        $groups = StudyGroup::query()
            ->with('owner:id,name,display_name,email')
            ->withCount(['members', 'resources', 'discussions', 'sessions'])
            ->orderBy('name')
            ->get()
            ->map(function (StudyGroup $group) {
                $activityScore = (int) $group->members_count + (int) $group->resources_count + (int) $group->discussions_count + (int) $group->sessions_count;

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'category' => $group->category,
                    'owner' => $group->owner?->display_name ?: $group->owner?->name ?: 'No owner',
                    'members' => (int) $group->members_count,
                    'resources' => (int) $group->resources_count,
                    'discussions' => (int) $group->discussions_count,
                    'sessions' => (int) $group->sessions_count,
                    'activity' => $activityScore >= 8 ? 'High' : ($activityScore >= 3 ? 'Medium' : 'Low'),
                    'status' => $group->visibility === 'private' ? 'Private' : 'Public',
                    'created' => $group->created_at?->format('M j, Y') ?? now()->format('M j, Y'),
                ];
            })
            ->all();

        return $this->renderAdmin('studyhub.admin.groups', [
            'summary' => $summary,
            'groups' => $groups,
        ]);
    }

    public function showGroup(StudyGroup $group): View
    {
        $group->load([
            'owner:id,name,display_name,email',
            'resources.uploader:id,name,display_name,email',
            'discussions.author:id,name,display_name,email',
            'discussions.replies.author:id,name,display_name,email',
            'sessions.creator:id,name,display_name,email',
        ])->loadCount(['members', 'resources', 'discussions', 'sessions']);

        $recentResources = $group->resources
            ->sortByDesc(fn (StudyResource $resource) => $resource->uploaded_at ?? $resource->created_at)
            ->take(6)
            ->values();

        $recentDiscussions = $group->discussions
            ->sortByDesc(fn (Discussion $discussion) => $discussion->last_active_at ?? $discussion->created_at)
            ->take(6)
            ->values();

        $upcomingSessions = $group->sessions
            ->sortBy(fn (StudySession $session) => ($session->session_date?->format('Y-m-d') ?? '').' '.$session->start_time)
            ->values();

        $recentReplies = DiscussionReply::query()
            ->whereHas('discussion', fn (Builder $query) => $query->where('group_id', $group->id))
            ->with(['author:id,name,display_name,email', 'discussion:id,title,group_id'])
            ->latest()
            ->take(8)
            ->get();

        return $this->renderAdmin('studyhub.admin.group-detail', [
            'group' => $group,
            'recentResources' => $recentResources,
            'recentDiscussions' => $recentDiscussions,
            'upcomingSessions' => $upcomingSessions,
            'recentReplies' => $recentReplies,
            'meetingStyles' => [
                'in-person' => 'In person',
                'online' => 'Online',
                'hybrid' => 'Hybrid',
            ],
            'visibilities' => [
                'public' => 'Public',
                'private' => 'Private',
            ],
        ]);
    }

    public function updateGroup(Request $request, StudyGroup $group): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:80', Rule::unique('study_groups', 'name')->ignore($group->id)],
            'description' => ['required', 'string', 'max:240'],
            'category' => ['nullable', 'string', 'max:40'],
            'meeting_style' => ['required', 'in:in-person,online,hybrid'],
            'visibility' => ['required', 'in:public,private'],
            'join_code' => ['nullable', 'string', 'max:24'],
        ];

        if ($request->input('visibility') === 'private') {
            $rules['join_code'] = ['required', 'string', 'min:4', 'max:24'];
        }

        $validated = $request->validate($rules);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => trim($validated['category'] ?? '') !== '' ? trim($validated['category']) : 'General',
            'meeting_style' => $validated['meeting_style'],
            'visibility' => $validated['visibility'],
            'join_code' => $validated['visibility'] === 'private' ? strtoupper(trim($validated['join_code'])) : null,
            'color' => $this->groupColorForMeetingStyle($validated['meeting_style']),
        ]);

        return redirect()
            ->route('studyhub.admin.groups.show', $group)
            ->with('status', 'Study group updated successfully.');
    }

    public function deleteGroup(StudyGroup $group): RedirectResponse
    {
        $groupName = $group->name;
        $resourcePaths = $group->resources()->pluck('path')->filter()->all();

        DB::transaction(fn () => $group->delete());
        $this->deleteStoredResourceFiles($resourcePaths);

        return redirect()
            ->route('studyhub.admin.groups')
            ->with('status', $groupName.' was deleted successfully.');
    }

    public function deleteSession(Request $request, StudySession $session): RedirectResponse
    {
        $session->loadMissing('group');

        $group = $session->group;
        $sessionTitle = $session->title;
        $groupName = $group?->name ?: 'a StudyHub group';

        $this->logActivity(
            'session_deleted',
            'Study session deleted',
            ($request->user()->display_name ?: $request->user()->name).' deleted '.$sessionTitle.' from '.$groupName.'.',
            $group,
            $session,
        );

        DB::transaction(fn () => $session->delete());

        return $this->sessionDeletionRedirect($request, $group)
            ->with('status', $sessionTitle.' was deleted successfully.');
    }

    public function deleteDiscussion(Request $request, Discussion $discussion): RedirectResponse
    {
        $discussion->loadMissing('group');

        $group = $discussion->group;
        $discussionTitle = $discussion->title;
        $groupName = $group?->name ?: 'a StudyHub group';

        $this->logActivity(
            'discussion_deleted',
            'Discussion deleted',
            ($request->user()->display_name ?: $request->user()->name).' deleted "'.$discussionTitle.'" from '.$groupName.'.',
            $group,
            $discussion,
        );

        collect($this->discussionImagePaths($discussion))
            ->each(fn (string $path) => Storage::disk('local')->delete($path));

        DB::transaction(fn () => $discussion->delete());

        return $this->adminModerationRedirect($request, $group)
            ->with('status', $discussionTitle.' was deleted successfully.');
    }

    public function deleteDiscussionReply(Request $request, DiscussionReply $reply): RedirectResponse
    {
        $reply->loadMissing(['discussion.group', 'author']);

        $discussion = $reply->discussion;
        $group = $discussion?->group;
        $replyAuthor = $reply->author?->display_name ?: $reply->author?->name ?: 'Unknown member';
        $replyPreview = str($reply->body)->limit(72)->toString();

        $this->logActivity(
            'discussion_reply_deleted',
            'Discussion reply deleted',
            ($request->user()->display_name ?: $request->user()->name).' deleted a reply by '.$replyAuthor.': "'.$replyPreview.'".',
            $group,
            $reply,
        );

        DB::transaction(fn () => $reply->delete());

        return $this->adminModerationRedirect($request, $group)
            ->with('status', 'Discussion reply deleted successfully.');
    }

    public function reports(Request $request): View
    {
        $range = $request->query('range', '6_months');
        $rangeStart = $range === 'all' ? null : now()->subMonths(5)->startOfMonth();

        $stats = [
            ['label' => 'Total Users', 'value' => (string) User::count(), 'change' => 'Live', 'color' => '#0F4C75'],
            ['label' => 'Active Groups', 'value' => (string) StudyGroup::count(), 'change' => 'Live', 'color' => '#3282B8'],
            ['label' => 'Resources', 'value' => (string) StudyResource::count(), 'change' => 'Live', 'color' => '#06D6A0'],
            ['label' => 'Engagement Rate', 'value' => (string) $this->engagementRate().'%', 'change' => 'Auto', 'color' => '#FF6B35'],
        ];

        $monthlyUserData = $this->withPercentages($this->monthlyUserRoleData($rangeStart), 'total');

        $resourceTypeData = StudyResource::query()
            ->selectRaw('category as name, count(*) as value')
            ->groupBy('category')
            ->orderByDesc('value')
            ->get()
            ->values()
            ->map(function ($row, int $index) {
                $colors = ['#0F4C75', '#3282B8', '#06D6A0', '#FF6B35', '#79532d'];

                return [
                    'name' => $row->name,
                    'value' => (int) $row->value,
                    'color' => $colors[$index % count($colors)],
                ];
            })
            ->all();

        $activityData = [
            ['category' => 'Resources Uploaded', 'count' => StudyResource::count()],
            ['category' => 'Discussions Started', 'count' => Discussion::count()],
            ['category' => 'Study Sessions', 'count' => StudySession::count()],
            ['category' => 'New Groups', 'count' => StudyGroup::count()],
        ];

        return $this->renderAdmin('studyhub.admin.reports', [
            'stats' => $stats,
            'monthlyUserData' => $monthlyUserData,
            'resourceTypeData' => $resourceTypeData,
            'activityData' => $activityData,
            'range' => $range,
        ]);
    }

    public function exportReports(): StreamedResponse
    {
        $fileName = 'studyhub-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['StudyHub Admin Report']);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, []);

            fputcsv($handle, ['Overview']);
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Users', User::count()]);
            fputcsv($handle, ['Students', User::query()->where('role', 'student')->count()]);
            fputcsv($handle, ['Admins', User::query()->where('role', 'admin')->count()]);
            fputcsv($handle, ['Study Groups', StudyGroup::count()]);
            fputcsv($handle, ['Resources', StudyResource::count()]);
            fputcsv($handle, ['Discussions', Discussion::count()]);
            fputcsv($handle, ['Study Sessions', StudySession::count()]);
            fputcsv($handle, ['Engagement Rate', $this->engagementRate().'%']);
            fputcsv($handle, []);

            fputcsv($handle, ['Resource Distribution']);
            fputcsv($handle, ['Category', 'Count']);
            StudyResource::query()
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->each(fn ($row) => fputcsv($handle, [$row->category, (int) $row->count]));
            fputcsv($handle, []);

            fputcsv($handle, ['Group Activity']);
            fputcsv($handle, ['Group', 'Visibility', 'Members', 'Resources', 'Discussions', 'Sessions']);
            StudyGroup::query()
                ->withCount(['members', 'resources', 'discussions', 'sessions'])
                ->orderBy('name')
                ->each(fn (StudyGroup $group) => fputcsv($handle, [
                    $group->name,
                    ucfirst($group->visibility),
                    (int) $group->members_count,
                    (int) $group->resources_count,
                    (int) $group->discussions_count,
                    (int) $group->sessions_count,
                ]));

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function dashboardAlerts(): array
    {
        $pendingUsers = User::query()->whereNull('email_verified_at')->count();
        $privateGroups = StudyGroup::query()->where('visibility', 'private')->count();
        $recentUploads = StudyResource::query()->where('created_at', '>=', now()->subDays(7))->count();
        $emptyGroups = StudyGroup::query()
            ->whereDoesntHave('members')
            ->whereDoesntHave('resources')
            ->whereDoesntHave('discussions')
            ->whereDoesntHave('sessions')
            ->count();

        $activityGroups = StudyGroup::query()
            ->withCount(['members', 'resources', 'discussions', 'sessions'])
            ->get()
            ->map(fn (StudyGroup $group) => (int) $group->members_count + (int) $group->resources_count + (int) $group->discussions_count + (int) $group->sessions_count);

        $highActivityGroups = $activityGroups->filter(fn (int $score): bool => $score >= 8)->count();
        $lowActivityGroups = $activityGroups->filter(fn (int $score): bool => $score > 0 && $score <= 1)->count();

        return [
            [
                'type' => $pendingUsers > 0 ? 'warning' : 'info',
                'label' => 'Pending users',
                'message' => $pendingUsers > 0
                    ? $pendingUsers.' user '.str('account')->plural($pendingUsers).' still '.($pendingUsers === 1 ? 'needs' : 'need').' verification.'
                    : 'No user accounts are waiting for verification.',
                'time' => 'Live',
            ],
            [
                'type' => $privateGroups > 0 ? 'warning' : 'info',
                'label' => 'Private groups',
                'message' => $privateGroups.' private '.str('group')->plural($privateGroups).' currently '.($privateGroups === 1 ? 'requires' : 'require').' admin visibility.',
                'time' => 'Live',
            ],
            [
                'type' => $recentUploads > 0 ? 'info' : 'warning',
                'label' => 'Recent uploads',
                'message' => $recentUploads.' resource '.str('upload')->plural($recentUploads).' recorded in the last 7 days.',
                'time' => '7 days',
            ],
            [
                'type' => $highActivityGroups > 0 ? 'info' : 'warning',
                'label' => 'High activity groups',
                'message' => $highActivityGroups.' high-activity '.str('group')->plural($highActivityGroups).' found.',
                'time' => 'Live',
            ],
            [
                'type' => ($emptyGroups + $lowActivityGroups) > 0 ? 'warning' : 'info',
                'label' => 'Low activity groups',
                'message' => $emptyGroups.' empty and '.$lowActivityGroups.' low-activity '.str('group')->plural($emptyGroups + $lowActivityGroups).' found.',
                'time' => 'Live',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function recentPlatformActivity(): array
    {
        $resources = StudyResource::query()
            ->with(['uploader:id,name,display_name', 'group:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (StudyResource $resource) => [
                'type' => 'Resource',
                'icon' => 'book',
                'message' => 'A new resource was uploaded',
                'title' => $resource->name,
                'meta' => trim(($resource->uploader?->display_name ?: $resource->uploader?->name ?: 'Unknown user').' • '.($resource->group?->name ?: 'No group')),
                'timestamp' => $resource->created_at,
            ]);

        $groups = StudyGroup::query()
            ->with('owner:id,name,display_name')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (StudyGroup $group) => [
                'type' => 'Group',
                'icon' => 'groups',
                'message' => 'A group was created',
                'title' => $group->name,
                'meta' => ($group->owner?->display_name ?: $group->owner?->name ?: 'No owner').' • '.ucfirst($group->visibility),
                'timestamp' => $group->created_at,
            ]);

        $discussions = Discussion::query()
            ->with(['author:id,name,display_name', 'group:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Discussion $discussion) => [
                'type' => 'Discussion',
                'icon' => 'discussion',
                'message' => 'A discussion was posted',
                'title' => $discussion->title,
                'meta' => trim(($discussion->author?->display_name ?: $discussion->author?->name ?: 'Unknown user').' • '.($discussion->group?->name ?: 'No group')),
                'timestamp' => $discussion->created_at,
            ]);

        $sessions = StudySession::query()
            ->with(['creator:id,name,display_name', 'group:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (StudySession $session) => [
                'type' => 'Session',
                'icon' => 'activity',
                'message' => 'A session was scheduled',
                'title' => $session->title,
                'meta' => trim(($session->creator?->display_name ?: $session->creator?->name ?: 'Unknown user').' • '.($session->group?->name ?: 'No group')),
                'timestamp' => $session->created_at,
            ]);

        return $resources
            ->concat($groups)
            ->concat($discussions)
            ->concat($sessions)
            ->filter(fn (array $activity): bool => $activity['timestamp'] !== null)
            ->sortByDesc('timestamp')
            ->take(8)
            ->map(fn (array $activity) => [
                'type' => $activity['type'],
                'icon' => $activity['icon'],
                'message' => $activity['message'],
                'title' => $activity['title'],
                'meta' => $activity['meta'],
                'time' => $activity['timestamp']->diffForHumans(),
            ])
            ->values()
            ->all();
    }

    private function percentLabel(int $value, int $total): string
    {
        return ((int) round(($value / max($total, 1)) * 100)).'%';
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function monthlyUserActivity(?CarbonInterface $since = null): array
    {
        return $this->monthlyUserBaseQuery($since)
            ->selectRaw("strftime('%m', created_at) as month_number, count(*) as users")
            ->groupBy('month_number')
            ->orderBy('month_number')
            ->get()
            ->map(fn ($row) => [
                'month' => Carbon::create()->month((int) $row->month_number)->format('M'),
                'users' => (int) $row->users,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function monthlyUserRoleData(?CarbonInterface $since = null): array
    {
        return $this->monthlyUserBaseQuery($since)
            ->selectRaw("strftime('%m', created_at) as month_number")
            ->selectRaw("sum(case when role = 'student' then 1 else 0 end) as students")
            ->selectRaw("sum(case when role = 'admin' then 1 else 0 end) as admins")
            ->groupBy('month_number')
            ->orderBy('month_number')
            ->get()
            ->map(function ($row) {
                $students = (int) $row->students;
                $admins = (int) $row->admins;

                return [
                    'month' => Carbon::create()->month((int) $row->month_number)->format('M'),
                    'students' => $students,
                    'admins' => $admins,
                    'total' => $students + $admins,
                ];
            })
            ->values()
            ->all();
    }

    private function monthlyUserBaseQuery(?CarbonInterface $since = null): Builder
    {
        return User::query()
            ->whereNotNull('created_at')
            ->when($since, fn (Builder $query) => $query->where('created_at', '>=', $since));
    }

    /**
     * @param  array<int, array<string, int|string>>  $rows
     * @return array<int, array<string, int|string>>
     */
    private function withPercentages(array $rows, string $valueKey): array
    {
        $max = max(array_map(fn (array $row) => (int) ($row[$valueKey] ?? 0), $rows) ?: [0]);

        return array_map(function (array $row) use ($max, $valueKey): array {
            $value = (int) ($row[$valueKey] ?? 0);
            $row['percent'] = $max > 0 && $value > 0
                ? max(6, (int) round(($value / $max) * 100))
                : 0;

            return $row;
        }, $rows);
    }

    private function userDeletionBlocker(?User $currentUser, User $targetUser, ?int $adminCount = null): ?string
    {
        if ($currentUser && (int) $targetUser->id === (int) $currentUser->id) {
            return 'You cannot delete your own admin account from this page.';
        }

        $adminCount ??= User::query()->where('role', 'admin')->count();

        if ($targetUser->isAdmin() && $adminCount <= 1) {
            return 'At least one admin account must remain in the system.';
        }

        return null;
    }

    private function sessionDeletionRedirect(Request $request, ?StudyGroup $group): RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/admin/'))) {
            return redirect($redirectTo);
        }

        if ($group) {
            return redirect()->route('studyhub.admin.groups.show', $group);
        }

        return redirect()->route('studyhub.admin.groups');
    }

    private function adminModerationRedirect(Request $request, ?StudyGroup $group): RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/admin/'))) {
            return redirect($redirectTo);
        }

        if ($group) {
            return redirect()->route('studyhub.admin.groups.show', $group);
        }

        return redirect()->route('studyhub.admin.groups');
    }

    /**
     * @return array<int, string>
     */
    private function discussionImagePaths(Discussion $discussion): array
    {
        $paths = collect($discussion->images ?: [])
            ->pluck('path')
            ->filter()
            ->values();

        if ($discussion->image_path) {
            $paths->push($discussion->image_path);
        }

        return $paths->unique()->values()->all();
    }

    /**
     * @return array<int, string>
     */
    private function resourcePathsForUserDeletion(User $user): array
    {
        return StudyResource::query()
            ->where('uploaded_by', $user->id)
            ->orWhereIn('group_id', $user->ownedGroups()->select('id'))
            ->pluck('path')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  iterable<int, string|null>  $paths
     */
    private function deleteStoredResourceFiles(iterable $paths): void
    {
        collect($paths)
            ->filter()
            ->unique()
            ->each(fn (string $path) => Storage::disk('local')->delete($path));
    }
}
