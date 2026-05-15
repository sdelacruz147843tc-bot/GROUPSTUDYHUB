<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Discussion;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StudentDashboardController extends StudyHubController
{
    public function index(Request $request): View|JsonResponse
    {
        $student = $this->studentUser();
        $search = trim((string) $request->query('q', ''));

        if ($request->boolean('live_search')) {
            return response()->json([
                'query' => $search,
                'results' => $search !== '' ? $this->dashboardSearchResults($student, $search) : [],
            ]);
        }

        $joinedGroupIds = $student->joinedGroups()
            ->pluck('study_groups.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $joinedGroups = StudyGroup::query()
            ->whereIn('id', $joinedGroupIds)
            ->withCount(['members', 'resources'])
            ->orderBy('name')
            ->limit(3)
            ->get()
            ->map(fn (StudyGroup $group) => $this->formatGroup($group))
            ->all();
        $resourceCount = $this->visibleResourcesQuery($student)->count();
        $discussionCount = $this->visibleDiscussionsQuery($student)->count();
        $upcomingSessionCount = $this->upcomingSessionsQuery($student)->count();
        $latestResources = $this->visibleResourcesQuery($student)
            ->with(['group', 'uploader'])
            ->orderByDesc('uploaded_at')
            ->orderByDesc('created_at')
            ->limit(2)
            ->get()
            ->map(fn (StudyResource $resource) => $this->formatResource($resource))
            ->all();
        $latestUpcomingSession = $this->upcomingSessionsQuery($student)
            ->with(['group', 'creator'])
            ->withCount('attendees')
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->first();
        $upcomingSessions = $this->upcomingSessionsQuery($student)
            ->with(['group', 'creator'])
            ->withCount('attendees')
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(3)
            ->get()
            ->map(fn (StudySession $session) => $this->formatSession($session))
            ->all();
        $latestDiscussion = $this->visibleDiscussionsQuery($student)
            ->with(['author', 'group'])
            ->withCount('replies')
            ->orderByDesc('last_active_at')
            ->orderByDesc('created_at')
            ->first();
        $latestSession = $this->visibleSessionsQuery($student)
            ->with(['group', 'creator'])
            ->withCount('attendees')
            ->orderByDesc('created_at')
            ->first();

        $stats = [
            ['icon' => 'users', 'value' => (string) count($joinedGroupIds), 'label' => 'Active Groups', 'color' => '#84d8a0'],
            ['icon' => 'file', 'value' => (string) $resourceCount, 'label' => 'Resources Shared', 'color' => '#9fa8ff'],
            ['icon' => 'message', 'value' => (string) $discussionCount, 'label' => 'Discussion', 'color' => '#9cf0c1'],
            ['icon' => 'calendar', 'value' => (string) $upcomingSessionCount, 'label' => 'Study Session', 'color' => '#ffb3a3'],
        ];

        $notifications = collect($latestResources)
            ->take(1)
            ->map(fn (array $resource) => [
                'text' => 'New resource uploaded in '.$resource['group'],
                'time' => $resource['date'],
            ])
            ->when($latestUpcomingSession, fn ($collection) => $collection->push([
                'text' => $latestUpcomingSession->title.' is scheduled for '.$latestUpcomingSession->session_date->format('M j, Y'),
                'time' => Carbon::parse($latestUpcomingSession->session_date->format('Y-m-d').' '.$latestUpcomingSession->start_time)->format('g:i A'),
            ]))
            ->take(2)
            ->values()
            ->all();

        $latestDiscussionActivity = $latestDiscussion instanceof Discussion ? $this->formatDiscussion($latestDiscussion) : null;
        $latestSessionActivity = $latestSession instanceof StudySession ? $this->formatSession($latestSession) : null;

        $recentActivity = collect()
            ->merge(collect($latestResources)->map(fn (array $resource) => [
                'actor' => $resource['uploaded_by'],
                'action' => 'uploaded '.$resource['name'].' in',
                'group' => $resource['group'],
                'time' => $resource['date'],
            ]))
            ->when($latestDiscussionActivity, fn ($collection) => $collection->push([
                'actor' => $latestDiscussionActivity['author'],
                'action' => 'posted a discussion in',
                'group' => $latestDiscussionActivity['group'],
                'time' => $latestDiscussionActivity['last_active'],
            ]))
            ->when($latestSessionActivity, fn ($collection) => $collection->push([
                'actor' => $latestSessionActivity['created_by'],
                'action' => 'scheduled a study session in',
                'group' => $latestSessionActivity['group'],
                'time' => $latestSessionActivity['date'],
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

        $weeklyActivity = $this->weeklyActivity($student);
        $searchResults = $search !== '' ? $this->dashboardSearchResults($student, $search) : [];

        return $this->renderStudent('studyhub.student.dashboard', [
            'stats' => $stats,
            'groups' => $joinedGroups,
            'notifications' => $notifications,
            'upcomingSessions' => $upcomingSessions,
            'recentActivity' => $recentActivity,
            'weeklyActivity' => $weeklyActivity,
            'search' => $search,
            'searchResults' => $searchResults,
        ]);
    }

    private function weeklyActivity($student): array
    {
        $start = now()->startOfWeek(Carbon::SUNDAY);
        $end = now()->endOfWeek(Carbon::SATURDAY);
        $visibleGroupIds = StudyGroup::query()
            ->where(fn ($query) => $this->applyVisibleGroupContentConstraint($query, $student))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $counts = ActivityLog::query()
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($query) use ($student, $visibleGroupIds) {
                $query->where('user_id', $student->id);

                if ($visibleGroupIds !== []) {
                    $query->orWhereIn('group_id', $visibleGroupIds);
                }
            })
            ->get()
            ->groupBy(fn (ActivityLog $activity) => $activity->created_at->toDateString())
            ->map->count();

        $max = max(4, (int) $counts->max());

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $counts, $max) {
                $date = $start->copy()->addDays($offset);
                $count = (int) ($counts[$date->toDateString()] ?? 0);

                return [
                    'label' => $date->format('D'),
                    'count' => $count,
                    'height' => $count > 0 ? max(12, (int) round(($count / $max) * 100)) : 0,
                ];
            })
            ->all();
    }

    private function dashboardSearchResults($student, string $search): array
    {
        $groups = StudyGroup::query()
            ->where(fn ($query) => $this->applyVisibleGroupContentConstraint($query, $student))
            ->where('name', 'like', '%'.$search.'%')
            ->orderBy('name')
            ->limit(3)
            ->get()
            ->map(fn (StudyGroup $group) => [
                'type' => 'Group',
                'title' => $group->name,
                'meta' => ucfirst($group->visibility).' study group',
                'url' => route('studyhub.student.group.show', $group),
            ]);

        $resources = $this->visibleResourcesQuery($student)
            ->with('group')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%');
            })
            ->orderByDesc('uploaded_at')
            ->limit(3)
            ->get()
            ->map(fn (StudyResource $resource) => [
                'type' => 'Resource',
                'title' => $resource->name,
                'meta' => $resource->group?->name ?: 'Study resource',
                'url' => route('studyhub.student.resources', ['q' => $search]),
            ]);

        $discussions = $this->visibleDiscussionsQuery($student)
            ->with('group')
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            })
            ->orderByDesc('last_active_at')
            ->limit(3)
            ->get()
            ->map(fn (Discussion $discussion) => [
                'type' => 'Discussion',
                'title' => $discussion->title,
                'meta' => $discussion->group?->name ?: 'Discussion',
                'url' => route('studyhub.student.discussions.show', $discussion),
            ]);

        $sessions = $this->visibleSessionsQuery($student)
            ->with('group')
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhereHas('group', fn ($groupQuery) => $groupQuery->where('name', 'like', '%'.$search.'%'));
            })
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(3)
            ->get()
            ->map(fn (StudySession $session) => [
                'type' => 'Session',
                'title' => $session->title,
                'meta' => ($session->group?->name ?: 'Study session').' - '.$session->session_date->format('M j, Y'),
                'url' => route('studyhub.student.sessions', ['tab' => 'calendar', 'week_start' => $session->session_date->copy()->startOfWeek()->toDateString()]),
            ]);

        return collect($groups->all())
            ->merge($resources->all())
            ->merge($discussions->all())
            ->merge($sessions->all())
            ->take(8)
            ->values()
            ->all();
    }
}
