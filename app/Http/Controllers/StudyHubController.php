<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudyHubController extends Controller
{
    public function studentDashboard(): View
    {
        $joinedGroups = $this->getJoinedGroups();
        $student = $this->studentUser();
        $resources = $this->getStudentResources();
        $discussions = $this->getStudentDiscussions();
        $sessions = $this->getStudentSessions();

        $stats = [
            ['icon' => 'users', 'value' => (string) count($joinedGroups), 'label' => 'Active Groups', 'color' => '#84d8a0'],
            ['icon' => 'file', 'value' => (string) count($resources), 'label' => 'Resources Shared', 'color' => '#9fa8ff'],
            ['icon' => 'message', 'value' => (string) count($discussions), 'label' => 'Discussion', 'color' => '#9cf0c1'],
            ['icon' => 'calendar', 'value' => (string) count(collect($sessions)->where('phase', 'upcoming')), 'label' => 'Study Session', 'color' => '#ffb3a3'],
        ];

        $notifications = collect($resources)
            ->take(1)
            ->map(fn (array $resource) => [
                'text' => 'New resource uploaded in '.$resource['group'],
                'time' => $resource['date'],
            ])
            ->merge(collect($sessions)
                ->where('phase', 'upcoming')
                ->take(1)
                ->map(fn (array $session) => [
                    'text' => $session['title'].' is scheduled for '.$session['date'],
                    'time' => $session['time'],
                ]))
            ->take(2)
            ->values()
            ->all();

        $recentActivity = collect()
            ->merge(collect($resources)->take(2)->map(fn (array $resource) => [
                'actor' => $resource['uploaded_by'],
                'action' => 'uploaded '.$resource['name'].' in',
                'group' => $resource['group'],
                'time' => $resource['date'],
            ]))
            ->merge(collect($discussions)->take(1)->map(fn (array $discussion) => [
                'actor' => $discussion['author'],
                'action' => 'posted a discussion in',
                'group' => $discussion['group'],
                'time' => $discussion['last_active'],
            ]))
            ->merge(collect($sessions)->take(1)->map(fn (array $session) => [
                'actor' => $session['created_by'],
                'action' => 'scheduled a study session in',
                'group' => $session['group'],
                'time' => $session['date'],
            ]))
            ->when($student && count($joinedGroups) > 0, fn ($collection) => $collection->push([
                'actor' => 'You',
                'action' => 'are learning with',
                'group' => $joinedGroups[0]['name'],
                'time' => 'Today',
            ]))
            ->take(4)
            ->values()
            ->all();

        return $this->renderStudent('studyhub.student.dashboard', [
            'stats' => $stats,
            'groups' => collect($joinedGroups)
                ->take(3)
                ->map(fn (array $group) => [
                    'id' => $group['id'],
                    'initial' => $group['initial'],
                    'name' => $group['name'],
                ])
                ->all(),
            'notifications' => $notifications,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function studentGroups(): View
    {
        $groups = $this->getStudentGroups();

        return $this->renderStudent('studyhub.student.groups', [
            'groups' => $groups,
            'joinedGroupIds' => $this->getJoinedGroupIds(),
        ]);
    }

    public function storeStudentGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:40'],
            'meeting_style' => ['required', 'in:in-person,online,hybrid'],
            'visibility' => ['required', 'in:public,private'],
            'join_code' => ['nullable', 'string', 'max:24'],
        ]);

        if ($validated['visibility'] === 'private') {
            $request->validate([
                'join_code' => ['required', 'string', 'min:4', 'max:24'],
            ]);
        }

        $group = StudyGroup::create([
            'owner_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'color' => $this->groupColorForMeetingStyle($validated['meeting_style']),
            'category' => $validated['category'] ?: 'General',
            'meeting_style' => $validated['meeting_style'],
            'visibility' => $validated['visibility'],
            'join_code' => $validated['visibility'] === 'private' ? strtoupper(trim($validated['join_code'])) : null,
        ]);
        $group->members()->syncWithoutDetaching([$request->user()->id]);

        return redirect()
            ->route('studyhub.student.groups')
            ->with('status', 'New study group created successfully.');
    }

    public function joinStudentGroup(Request $request, string $id): RedirectResponse
    {
        $groupId = (int) $id;
        $group = StudyGroup::find($groupId);

        if (! $group) {
            return back()->with('status', 'That study group could not be found.');
        }

        if ($group->members()->where('users.id', $request->user()->id)->exists()) {
            return back()->with('status', 'You already joined this group.');
        }

        $isPrivate = $group->visibility === 'private';

        if ($isPrivate) {
            $validated = $request->validate([
                'join_code' => ['required', 'string', 'max:24'],
            ]);

            if (strtoupper(trim($validated['join_code'])) !== ($group->join_code ?? '')) {
                return back()
                    ->withErrors(['join_code' => 'Wrong join code for this private group.'])
                    ->with('status', 'Private group code did not match.');
            }
        }

        $group->members()->attach($request->user()->id);

        return back()->with('status', 'You joined the study group.');
    }

    public function leaveStudentGroup(Request $request, string $id): RedirectResponse
    {
        $groupId = (int) $id;
        $group = StudyGroup::find($groupId);

        if (! $group || ! $group->members()->where('users.id', $request->user()->id)->exists()) {
            return back()->with('status', 'You are not a member of this group.');
        }

        if ((int) $group->owner_id === (int) $request->user()->id && $group->members()->count() === 1) {
            return back()->with('status', 'As the only member, you cannot leave your own group yet.');
        }

        $group->members()->detach($request->user()->id);

        return back()->with('status', 'You left the study group.');
    }

    public function studentGroupDetail(string $id): View
    {
        $group = collect($this->getStudentGroups())
            ->firstWhere('id', (int) $id)
            ?? $this->getStudentGroups()[0];

        $resources = collect($this->getStudentResources())
            ->where('group', $group['name'])
            ->values()
            ->all();

        $discussions = collect($this->getStudentDiscussions())
            ->where('group', $group['name'])
            ->take(3)
            ->map(fn (array $discussion) => [
                'title' => $discussion['title'],
                'author' => $discussion['author'],
                'replies' => $discussion['replies'],
                'last_active' => $discussion['last_active'],
            ])
            ->values()
            ->all();

        $sessions = collect($this->getStudentSessions())
            ->where('group', $group['name'])
            ->where('phase', 'upcoming')
            ->values()
            ->all();

        return $this->renderStudent('studyhub.student.group-detail', [
            'group' => $group,
            'resources' => $resources,
            'discussions' => $discussions,
            'sessions' => $sessions,
            'isJoined' => in_array((int) $group['id'], $this->getJoinedGroupIds(), true),
            'joinCodeError' => session('errors')?->first('join_code'),
            'resourceCategories' => $this->studentResourceCategoriesWithoutAll(),
        ]);
    }

    public function studentResources(): View
    {
        return $this->renderStudent('studyhub.student.resources', [
            'categories' => $this->studentResourceCategories(),
            'resources' => $this->getStudentResources(),
            'uploadGroups' => $this->getJoinedGroups(),
        ]);
    }

    public function storeStudentResource(Request $request): RedirectResponse
    {
        $joinedGroups = $this->getJoinedGroups();
        $joinedGroupIds = collect($joinedGroups)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'group_id' => ['required', 'in:'.implode(',', $joinedGroupIds)],
            'category' => ['required', 'in:'.implode(',', $this->studentResourceCategoriesWithoutAll())],
            'resource_file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('resource_file');
        $storedPath = $file->store('studyhub-resources', 'public');
        StudyResource::create([
            'group_id' => (int) $validated['group_id'],
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'category' => $validated['category'],
            'path' => $storedPath,
            'size_bytes' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/student/groups/'))) {
            return redirect($redirectTo)->with('status', 'Resource uploaded successfully.');
        }

        return redirect()
            ->route('studyhub.student.resources')
            ->with('status', 'Resource uploaded successfully.');
    }

    public function studentDiscussions(): View
    {
        $discussions = $this->getStudentDiscussions();
        $profile = $this->getStudentProfile();
        $stats = [
            ['label' => 'Total Discussions', 'value' => (string) count($discussions), 'icon' => 'discussion', 'color' => '#0F4C75'],
            ['label' => 'Your Posts', 'value' => (string) collect($discussions)->where('author', $profile['display_name'])->count(), 'icon' => 'user', 'color' => '#3282B8'],
            ['label' => 'Trending Topics', 'value' => (string) collect($discussions)->where('trending', true)->count(), 'icon' => 'trend', 'color' => '#63bb7a'],
        ];

        return $this->renderStudent('studyhub.student.discussions', [
            'stats' => $stats,
            'discussions' => $discussions,
            'discussionGroups' => $this->getJoinedGroups(),
        ]);
    }

    public function storeStudentDiscussion(Request $request): RedirectResponse
    {
        $joinedGroups = $this->getJoinedGroups();
        $joinedGroupIds = collect($joinedGroups)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'group_id' => ['required', 'in:'.implode(',', $joinedGroupIds)],
            'body' => ['required', 'string', 'max:500'],
        ]);

        Discussion::create([
            'group_id' => (int) $validated['group_id'],
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'views' => 1,
            'trending' => false,
            'last_active_at' => now(),
        ]);

        return redirect()
            ->route('studyhub.student.discussions')
            ->with('status', 'Discussion posted successfully.');
    }

    public function studentDiscussionShow(string $id): View|RedirectResponse
    {
        $discussionId = (int) $id;
        $model = Discussion::with(['author', 'group', 'replies.author'])->find($discussionId);

        if (! $model) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'Discussion could not be found.');
        }

        $model->increment('views');
        $model->refresh();

        return $this->renderStudent('studyhub.student.discussion-thread', [
            'discussion' => $this->formatDiscussion($model->loadMissing(['author', 'group', 'replies'])),
            'replies' => $this->getStudentDiscussionReplies($model->id),
        ]);
    }

    public function deleteStudentDiscussion(Request $request, string $id): RedirectResponse
    {
        $discussionId = (int) $id;
        $discussion = Discussion::with('author')->find($discussionId);

        if (! $discussion) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'Discussion could not be found.');
        }

        if ((int) $discussion->author_id !== (int) $request->user()->id) {
            return back()->with('status', 'You can only delete discussions you created.');
        }

        $discussion->delete();

        return redirect()
            ->route('studyhub.student.discussions')
            ->with('status', 'Discussion deleted successfully.');
    }

    public function storeStudentDiscussionReply(Request $request, string $id): RedirectResponse
    {
        $discussionId = (int) $id;
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:500'],
            'parent_reply_id' => ['nullable', 'integer'],
        ]);

        $targetDiscussion = Discussion::find($discussionId);

        if (! $targetDiscussion) {
            return redirect()
                ->route('studyhub.student.discussions')
                ->with('status', 'Discussion could not be found.');
        }

        $parentReplyId = $validated['parent_reply_id'] ?? null;
        $parentReply = null;

        if ($parentReplyId) {
            $parentReply = DiscussionReply::query()
                ->where('discussion_id', $discussionId)
                ->find($parentReplyId);

            if (! $parentReply) {
                return back()->with('status', 'The reply you selected could not be found.');
            }
        }

        $targetDiscussion->replies()->create([
            'parent_reply_id' => $parentReply?->id,
            'author_id' => $request->user()->id,
            'body' => $request->string('reply')->trim()->value(),
        ]);
        $targetDiscussion->update([
            'last_active_at' => now(),
        ]);

        return redirect()
            ->route('studyhub.student.discussions.show', $discussionId)
            ->with('status', 'Reply posted successfully.');
    }

    public function studentSessions(): View
    {
        $sessions = $this->getStudentSessions();

        return $this->renderStudent('studyhub.student.sessions', [
            'upcomingSessions' => collect($sessions)->where('phase', 'upcoming')->values()->all(),
            'pastSessions' => collect($sessions)->where('phase', 'past')->values()->all(),
            'sessionGroups' => $this->getJoinedGroups(),
        ]);
    }

    public function storeStudentSession(Request $request): RedirectResponse
    {
        $joinedGroups = $this->getJoinedGroups();
        $joinedGroupIds = collect($joinedGroups)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'group_id' => ['required', 'in:'.implode(',', $joinedGroupIds)],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:in-person,online'],
            'max_attendees' => ['required', 'integer', 'min:2', 'max:100'],
        ]);

        $date = Carbon::parse($validated['date']);
        $sessionEndsAt = Carbon::parse($date->format('Y-m-d').' '.$validated['end_time']);

        $session = StudySession::create([
            'group_id' => (int) $validated['group_id'],
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'session_date' => $date->toDateString(),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'type' => $validated['type'],
            'max_attendees' => (int) $validated['max_attendees'],
            'status' => $sessionEndsAt->isPast() ? 'completed' : 'confirmed',
            'notes' => 'Scheduled through your StudyHub session planner.',
        ]);
        $session->attendees()->attach($request->user()->id);

        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/student/groups/'))) {
            return redirect($redirectTo)->with('status', 'Study session scheduled successfully.');
        }

        return redirect()
            ->route('studyhub.student.sessions')
            ->with('status', 'Study session scheduled successfully.');
    }

    public function rsvpStudentSession(Request $request, string $id): RedirectResponse
    {
        $sessionId = (int) $id;
        $targetSession = StudySession::with('attendees')->find($sessionId);

        if (! $targetSession) {
            return redirect()
                ->route('studyhub.student.sessions')
                ->with('status', 'Study session could not be found.');
        }

        if ($targetSession->attendees()->where('users.id', $request->user()->id)->exists()) {
            return back()->with('status', 'You already joined this study session.');
        }

        if ($targetSession->attendees()->count() >= $targetSession->max_attendees) {
            return back()->with('status', 'This study session is already full.');
        }

        $targetSession->attendees()->attach($request->user()->id);
        $targetSession->update(['status' => 'confirmed']);

        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/student/groups/'))) {
            return redirect($redirectTo)->with('status', 'You joined the study session.');
        }

        return redirect()
            ->route('studyhub.student.sessions')
            ->with('status', 'You joined the study session.');
    }

    public function studentProfile(): View
    {
        $profile = $this->getStudentProfile();

        return $this->renderStudent('studyhub.student.profile', [
            'studentProfileForm' => $profile,
        ]);
    }

    public function updateStudentProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$request->user()->id],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'bio' => ['nullable', 'string', 'max:240'],
        ]);

        $request->user()->forceFill([
            'name' => $validated['display_name'],
            'display_name' => $validated['display_name'],
            'email' => $validated['email'],
            'avatar_url' => $validated['avatar_url'] ?? '',
            'bio' => $validated['bio'] ?? '',
        ])->save();

        return redirect()
            ->route('studyhub.student.profile')
            ->with('status', 'Your StudyHub profile was updated.');
    }

    public function studentTheme(): View
    {
        $profile = $this->getStudentProfile();

        return $this->renderStudent('studyhub.student.theme', [
            'profileOptions' => $this->studentProfileOptions(),
            'studentProfileForm' => $profile,
        ]);
    }

    public function updateStudentTheme(Request $request): RedirectResponse
    {
        if ($request->boolean('reset_defaults')) {
            $request->user()->forceFill([
                'theme' => 'forest',
                'surface_style' => 'soft',
                'interface_density' => 'comfortable',
            ])->save();

            return redirect()
                ->route('studyhub.student.theme')
                ->with('status', 'Your student theme settings have been reset to defaults.');
        }

        $validated = $request->validate([
            'theme' => ['required', 'in:forest,ocean,sunset'],
            'surface_style' => ['required', 'in:soft,glass,contrast'],
            'interface_density' => ['required', 'in:comfortable,compact'],
        ]);

        $request->user()->forceFill([
            'theme' => $validated['theme'],
            'surface_style' => $validated['surface_style'],
            'interface_density' => $validated['interface_density'],
        ])->save();

        return redirect()
            ->route('studyhub.student.theme')
            ->with('status', 'Your StudyHub theme settings were saved.');
    }

    public function adminDashboard(): View
    {
        $totalUsers = User::count();
        $studentCount = User::query()->where('role', 'student')->count();
        $adminCount = User::query()->where('role', 'admin')->count();
        $groupCount = StudyGroup::count();
        $resourceCount = StudyResource::count();
        $discussionCount = Discussion::count();

        $stats = [
            ['label' => 'Total Users', 'value' => (string) $totalUsers, 'change' => $studentCount.' students', 'icon' => 'users', 'color' => '#0F4C75'],
            ['label' => 'Active Groups', 'value' => (string) $groupCount, 'change' => 'Live group records', 'icon' => 'book', 'color' => '#3282B8'],
            ['label' => 'Resources Shared', 'value' => (string) $resourceCount, 'change' => 'Library items', 'icon' => 'activity', 'color' => '#06D6A0'],
            ['label' => 'Admins', 'value' => (string) $adminCount, 'change' => $discussionCount.' discussions', 'icon' => 'discussion', 'color' => '#FF6B35'],
        ];

        $userActivityData = User::query()
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

        $resourceData = StudyResource::query()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();

        $recentAlerts = [
            ['type' => 'info', 'message' => 'Role-based dashboard routing is active.', 'time' => 'Just now'],
            ['type' => 'info', 'message' => $totalUsers.' user accounts are available in the database.', 'time' => 'Live'],
            ['type' => 'info', 'message' => StudySession::count().' study sessions are stored in the database.', 'time' => 'Live'],
        ];

        return $this->renderAdmin('studyhub.admin.dashboard', [
            'stats' => $stats,
            'userActivityData' => $userActivityData,
            'resourceData' => $resourceData,
            'recentAlerts' => $recentAlerts,
        ]);
    }

    public function adminUsers(): View
    {
        $users = User::query()
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->display_name ?: $user->name,
                'email' => $user->email,
                'role' => ucfirst($user->role),
                'groups' => $user->role === 'student' ? $user->joinedGroups()->count() : 0,
                'status' => 'Active',
                'join_date' => $user->created_at?->format('M j, Y') ?? now()->format('M j, Y'),
                'can_delete' => (int) $user->id !== (int) auth()->id(),
            ])
            ->all();

        $stats = [
            ['label' => 'Total Users', 'value' => (string) count($users), 'color' => '#0F4C75'],
            ['label' => 'Students', 'value' => (string) User::query()->where('role', 'student')->count(), 'color' => '#06D6A0'],
            ['label' => 'Admins', 'value' => (string) User::query()->where('role', 'admin')->count(), 'color' => '#FF6B35'],
            ['label' => 'Verified', 'value' => (string) User::query()->whereNotNull('email_verified_at')->count(), 'color' => '#3282B8'],
        ];

        return $this->renderAdmin('studyhub.admin.users', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    public function storeAdminUser(Request $request): RedirectResponse
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

    public function deleteAdminUser(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return redirect()
                ->route('studyhub.admin.users')
                ->with('status', 'You cannot delete your own admin account from this page.');
        }

        if ($user->isAdmin() && User::query()->where('role', 'admin')->count() <= 1) {
            return redirect()
                ->route('studyhub.admin.users')
                ->with('status', 'At least one admin account must remain in the system.');
        }

        $userName = $user->display_name ?: $user->name;
        $user->delete();

        return redirect()
            ->route('studyhub.admin.users')
            ->with('status', $userName.' was deleted successfully.');
    }

    public function adminGroups(): View
    {
        $summary = [
            ['label' => 'Monitored Groups', 'value' => (string) StudyGroup::count(), 'color' => '#0F4C75'],
            ['label' => 'Private', 'value' => (string) StudyGroup::query()->where('visibility', 'private')->count(), 'color' => '#FF6B35'],
            ['label' => 'High Activity', 'value' => (string) StudyGroup::query()->has('discussions', '>=', 2)->count(), 'color' => '#06D6A0'],
        ];

        $groups = StudyGroup::query()
            ->withCount(['members', 'resources', 'discussions'])
            ->orderBy('name')
            ->get()
            ->map(fn (StudyGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'members' => $group->members_count,
                'resources' => $group->resources_count,
                'discussions' => $group->discussions_count,
                'activity' => $group->discussions_count >= 3 ? 'High' : ($group->discussions_count >= 1 ? 'Medium' : 'Low'),
                'status' => $group->visibility === 'private' ? 'Private' : 'Active',
                'created' => $group->created_at?->format('M j, Y') ?? now()->format('M j, Y'),
            ])
            ->all();

        return $this->renderAdmin('studyhub.admin.groups', [
            'summary' => $summary,
            'groups' => $groups,
        ]);
    }

    public function adminReports(): View
    {
        $stats = [
            ['label' => 'Total Users', 'value' => (string) User::count(), 'change' => 'Live', 'color' => '#0F4C75'],
            ['label' => 'Active Groups', 'value' => (string) StudyGroup::count(), 'change' => 'Live', 'color' => '#3282B8'],
            ['label' => 'Resources', 'value' => (string) StudyResource::count(), 'change' => 'Live', 'color' => '#06D6A0'],
            ['label' => 'Engagement Rate', 'value' => (string) $this->engagementRate().'%', 'change' => 'Auto', 'color' => '#FF6B35'],
        ];

        $monthlyUserData = User::query()
            ->selectRaw("strftime('%m', created_at) as month_number")
            ->selectRaw("sum(case when role = 'student' then 1 else 0 end) as students")
            ->selectRaw("sum(case when role = 'admin' then 1 else 0 end) as admins")
            ->groupBy('month_number')
            ->orderBy('month_number')
            ->get()
            ->map(fn ($row) => [
                'month' => Carbon::create()->month((int) $row->month_number)->format('M'),
                'students' => (int) $row->students,
                'admins' => (int) $row->admins,
            ])
            ->values()
            ->all();

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
        ]);
    }

    private function studentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    private function formatDiscussion(Discussion $discussion): array
    {
        return [
            'id' => $discussion->id,
            'title' => $discussion->title,
            'author' => $discussion->author?->display_name ?: $discussion->author?->name ?: 'Unknown',
            'group' => $discussion->group?->name ?? 'Unknown Group',
            'replies' => $discussion->relationLoaded('replies') ? $discussion->replies->count() : $discussion->replies()->count(),
            'views' => (int) $discussion->views,
            'last_active' => $this->humanizeTime($discussion->last_active_at ?: $discussion->updated_at ?: $discussion->created_at),
            'trending' => (bool) $discussion->trending,
            'body' => $discussion->body,
        ];
    }

    private function renderStudent(string $view, array $data = []): View
    {
        $studentProfile = $this->getStudentProfile();
        $studentTheme = $this->getStudentTheme($studentProfile['theme'], $studentProfile['surface_style']);

        return view($view, array_merge($data, [
            'studentProfile' => $studentProfile,
            'studentTheme' => $studentTheme,
            'icons' => [
                'home' => '<svg viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/></svg>',
                'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'resources' => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
                'discussion' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                'calendar' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>',
                'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>',
                'file' => '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
                'message' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                'bell' => '<svg viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>',
                'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
                'plus' => '<svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
                'download' => '<svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>',
                'eye' => '<svg viewBox="0 0 24 24"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
                'clock' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>',
                'user' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>',
                'settings' => '<svg viewBox="0 0 24 24"><path d="M12 3l1.7 2.7 3.1.5.5 3.1L20 11l-2.7 1.7-.5 3.1-3.1.5L12 19l-1.7-2.7-3.1-.5-.5-3.1L4 11l2.7-1.7.5-3.1 3.1-.5L12 3z"/><circle cx="12" cy="11" r="3"/></svg>',
                'trend' => '<svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7"/><path d="M14 8h6v6"/></svg>',
                'map-pin' => '<svg viewBox="0 0 24 24"><path d="M12 21s-6-4.5-6-10a6 6 0 1 1 12 0c0 5.5-6 10-6 10z"/><circle cx="12" cy="11" r="2.5"/></svg>',
                'video' => '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3"/></svg>',
                'arrow-left' => '<svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>',
                'lock' => '<svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>',
            ],
        ]));
    }

    private function getStudentProfile(): array
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

    private function defaultStudentProfile(): array
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

    private function getStudentGroups(): array
    {
        return StudyGroup::query()
            ->withCount(['members', 'resources'])
            ->orderBy('name')
            ->get()
            ->map(fn (StudyGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'members' => $group->members_count,
                'resources' => $group->resources_count,
                'description' => $group->description,
                'color' => $group->color,
                'category' => $group->category,
                'meeting_style' => $group->meeting_style,
                'initial' => strtoupper(substr($group->name, 0, 1)),
                'visibility' => $group->visibility,
                'join_code' => $group->join_code,
            ])
            ->all();
    }

    private function getStudentResources(): array
    {
        return StudyResource::query()
            ->with(['group', 'uploader'])
            ->orderByDesc('uploaded_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StudyResource $resource) => [
                'id' => $resource->id,
                'name' => $resource->name,
                'category' => $resource->category,
                'group' => $resource->group?->name ?? 'Unknown Group',
                'size' => $this->formatFileSize((int) $resource->size_bytes),
                'date' => optional($resource->uploaded_at ?: $resource->created_at)->format('M j, Y'),
                'uploaded_by' => $resource->uploader?->display_name ?: $resource->uploader?->name ?: 'Unknown',
                'path' => $resource->path && Storage::disk('public')->exists($resource->path) ? $resource->path : null,
            ])
            ->all();
    }

    private function getStudentDiscussions(): array
    {
        return Discussion::query()
            ->with(['author', 'group', 'replies'])
            ->orderByDesc('last_active_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Discussion $discussion) => $this->formatDiscussion($discussion))
            ->all();
    }

    private function getStudentSessions(): array
    {
        return StudySession::query()
            ->with(['group', 'creator', 'attendees'])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get()
            ->map(function (StudySession $session) {
                $start = Carbon::parse($session->session_date->format('Y-m-d').' '.$session->start_time);
                $end = Carbon::parse($session->session_date->format('Y-m-d').' '.$session->end_time);
                $phase = $end->isPast() ? 'past' : 'upcoming';

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'group' => $session->group?->name ?? 'Unknown Group',
                    'date' => $session->session_date->format('M j, Y'),
                    'time' => $start->format('g:i A').' - '.$end->format('g:i A'),
                    'location' => $session->location,
                    'type' => $session->type,
                    'attendees' => $session->attendees->count(),
                    'max_attendees' => $session->max_attendees,
                    'status' => $session->status,
                    'phase' => $phase,
                    'created_by' => $session->creator?->display_name ?: $session->creator?->name ?: 'Unknown',
                    'attendee_names' => $session->attendees
                        ->map(fn (User $user) => $user->display_name ?: $user->name)
                        ->values()
                        ->all(),
                    'notes' => $session->notes ?: '',
                ];
            })
            ->all();
    }

    private function getStudentDiscussionReplies(int $discussionId): array
    {
        $discussion = Discussion::query()
            ->with(['replies.author', 'replies.childReplies.author'])
            ->find($discussionId);

        if (! $discussion) {
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

    private function getJoinedGroupIds(): array
    {
        return $this->studentUser()
            ->joinedGroups()
            ->pluck('study_groups.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function getJoinedGroups(): array
    {
        $joinedIds = $this->getJoinedGroupIds();

        return collect($this->getStudentGroups())
            ->filter(fn (array $group) => in_array((int) $group['id'], $joinedIds, true))
            ->values()
            ->all();
    }

    private function humanizeTime(Carbon|string|null $value): string
    {
        if (! $value) {
            return 'Just now';
        }

        $time = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $time->diffForHumans();
    }

    private function groupColorForMeetingStyle(string $meetingStyle): string
    {
        return match ($meetingStyle) {
            'online' => '#3282B8',
            'hybrid' => '#FF6B35',
            default => '#4A955F',
        };
    }

    private function studentResourceCategories(): array
    {
        return ['All', ...$this->studentResourceCategoriesWithoutAll()];
    }

    private function studentResourceCategoriesWithoutAll(): array
    {
        return ['Lecture Notes', 'Study Guide', 'Assignments', 'Code', 'Presentations'];
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).' MB';
        }

        return round($bytes / 1024).' KB';
    }

    private function studentProfileOptions(): array
    {
        return [
            'themes' => [
                ['value' => 'forest', 'label' => 'Forest', 'description' => 'Calm green workspace with soft neutrals.'],
                ['value' => 'ocean', 'label' => 'Ocean', 'description' => 'Cool blue palette with crisp contrast.'],
                ['value' => 'sunset', 'label' => 'Sunset', 'description' => 'Warm coral and gold tones with energy.'],
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

    private function engagementRate(): int
    {
        $totalUsers = max(User::count(), 1);
        $engagedUsers = User::query()
            ->whereHas('joinedGroups')
            ->orWhereHas('discussions')
            ->orWhereHas('attendingSessions')
            ->count();

        return (int) round(($engagedUsers / $totalUsers) * 100);
    }

    private function getStudentTheme(string $theme, string $surfaceStyle): array
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
        ];

        $surfaceStyles = [
            'soft' => ['card_radius' => '22px', 'card_shadow' => '0 24px 42px rgba(66, 95, 76, 0.10)', 'card_border' => 'rgba(195, 215, 203, 0.92)'],
            'glass' => ['card_radius' => '24px', 'card_shadow' => '0 24px 46px rgba(45, 75, 62, 0.12)', 'card_border' => 'rgba(255, 255, 255, 0.40)'],
            'contrast' => ['card_radius' => '18px', 'card_shadow' => '0 20px 38px rgba(37, 58, 48, 0.16)', 'card_border' => 'rgba(149, 176, 159, 0.95)'],
        ];

        return array_merge($themes[$theme] ?? $themes['forest'], $surfaceStyles[$surfaceStyle] ?? $surfaceStyles['soft']);
    }

    private function renderAdmin(string $view, array $data = []): View
    {
        return view($view, array_merge($data, [
            'icons' => [
                'dashboard' => '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
                'users' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'groups' => '<svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'reports' => '<svg viewBox="0 0 24 24"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-6"/></svg>',
                'logout' => '<svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>',
                'book' => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
                'activity' => '<svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 8-6-16-3 8H2"/></svg>',
                'discussion' => '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                'trend' => '<svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7"/><path d="M14 8h6v6"/></svg>',
                'search' => '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
                'warning' => '<svg viewBox="0 0 24 24"><path d="M12 3l10 18H2L12 3z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
                'info' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6"/><path d="M12 7h.01"/></svg>',
                'mail' => '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>',
                'shield' => '<svg viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"/></svg>',
                'ban' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg>',
            ],
        ]));
    }
}
