<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends StudyHubController
{
    public function index(): View
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

    public function users(): View
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

    public function deleteUser(Request $request, User $user): RedirectResponse
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

    public function groups(): View
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

    public function reports(): View
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
}
