<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudySession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class StudentSessionController extends StudyHubController
{
    public function index(Request $request): View
    {
        $joinedGroups = $this->getJoinedGroups();
        $joinedGroupIds = collect($joinedGroups)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $sessionFilters = [
            'tab' => in_array($request->query('tab'), ['all', 'upcoming', 'calendar', 'past'], true) ? $request->query('tab') : 'all',
            'view' => in_array($request->query('view'), ['calendar', 'list'], true) ? $request->query('view') : 'calendar',
            'group_id' => in_array((string) $request->query('group_id'), $joinedGroupIds, true) ? (string) $request->query('group_id') : '',
            'week_start' => '',
        ];
        $weekStart = now()->startOfWeek();

        if ($request->filled('week_start')) {
            try {
                $weekStart = Carbon::parse($request->query('week_start'))->startOfWeek();
                $sessionFilters['week_start'] = $weekStart->toDateString();
            } catch (\Throwable) {
                $sessionFilters['week_start'] = '';
            }
        }

        $allSessions = collect($this->getStudentSessions());
        $filteredSessions = $allSessions
            ->when($sessionFilters['group_id'] !== '', fn ($sessions) => $sessions->where('group_id', (int) $sessionFilters['group_id']))
            ->when($sessionFilters['tab'] === 'upcoming', fn ($sessions) => $sessions->where('phase', 'upcoming'))
            ->when($sessionFilters['tab'] === 'past', fn ($sessions) => $sessions->where('phase', 'past'))
            ->values();
        $upcomingSessions = $filteredSessions->where('phase', 'upcoming')->values()->all();
        $pastSessions = $filteredSessions->where('phase', 'past')->values()->all();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $thisWeekCount = $allSessions
            ->filter(function (array $session) {
                $date = Carbon::parse($session['date']);

                return $date->betweenIncluded(now()->startOfWeek(), now()->endOfWeek());
            })
            ->count();
        $nextSession = collect($upcomingSessions)->first();
        $calendarSessionModels = $this->visibleSessionsQuery()
            ->with(['group', 'creator', 'attendees'])
            ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->when($sessionFilters['group_id'] !== '', fn ($query) => $query->where('group_id', (int) $sessionFilters['group_id']))
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get();
        $calendarSessions = $calendarSessionModels
            ->map(function (StudySession $session) use ($weekStart) {
                $start = Carbon::parse($session->session_date->format('Y-m-d').' '.$session->start_time);
                $end = Carbon::parse($session->session_date->format('Y-m-d').' '.$session->end_time);
                $durationMinutes = max($start->diffInMinutes($end), 45);
                $top = (($start->hour - 8) * 64) + ($start->minute / 60 * 64);
                $height = max(50, $durationMinutes / 60 * 64);

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'group' => $session->group?->name ?? 'Unknown Group',
                    'day_index' => $weekStart->diffInDays($session->session_date),
                    'time' => $start->format('g:i A').' - '.$end->format('g:i A'),
                    'top' => max(0, $top),
                    'height' => $height,
                    'type' => $session->type,
                ];
            })
            ->filter(fn (array $session) => $session['day_index'] >= 0 && $session['day_index'] <= 6)
            ->values()
            ->all();
        $todaySchedule = $allSessions
            ->filter(fn (array $session) => Carbon::parse($session['date'])->isToday())
            ->take(3)
            ->values()
            ->all();
        $upcomingReminders = collect($upcomingSessions)->take(3)->values()->all();
        $activeSessionGroups = $allSessions
            ->groupBy('group')
            ->map(fn ($items, string $group) => [
                'name' => $group,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(4)
            ->values()
            ->all();

        return $this->renderStudent('studyhub.student.sessions', [
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
            'sessionGroups' => $joinedGroups,
            'sessionFilters' => $sessionFilters,
            'calendarWeekLabel' => $weekStart->format('M j').' - '.$weekEnd->format('M j, Y'),
            'calendarPrevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'calendarCurrentWeek' => now()->startOfWeek()->toDateString(),
            'calendarNextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'calendarDays' => collect(range(0, 6))
                ->map(fn (int $offset) => [
                    'label' => $weekStart->copy()->addDays($offset)->format('D'),
                    'day' => $weekStart->copy()->addDays($offset)->format('j'),
                    'is_today' => $weekStart->copy()->addDays($offset)->isToday(),
                ])
                ->all(),
            'calendarHours' => collect(range(8, 20))->map(fn (int $hour) => Carbon::createFromTime($hour)->format('g A'))->all(),
            'calendarSessions' => $calendarSessions,
            'todaySchedule' => $todaySchedule,
            'upcomingReminders' => $upcomingReminders,
            'activeSessionGroups' => $activeSessionGroups,
            'sessionStats' => [
                [
                    'label' => 'Upcoming Sessions',
                    'value' => count($upcomingSessions),
                    'hint' => $nextSession ? 'Next: '.$nextSession['date'] : 'No upcoming session',
                    'icon' => 'calendar',
                ],
                [
                    'label' => 'Total Attendees',
                    'value' => $allSessions->sum('attendees'),
                    'hint' => 'Across all sessions',
                    'icon' => 'users',
                ],
                [
                    'label' => 'This Week',
                    'value' => $thisWeekCount,
                    'hint' => 'Sessions scheduled',
                    'icon' => 'trend',
                ],
                [
                    'label' => 'Completed Sessions',
                    'value' => count($pastSessions),
                    'hint' => 'This month',
                    'icon' => 'clock',
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $joinedGroupIds = collect($this->getJoinedGroups())
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $locationRules = $request->input('type') === 'online'
            ? ['required', 'url', 'max:255']
            : ['required', 'string', 'max:120'];

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'group_id' => ['required', 'in:'.implode(',', $joinedGroupIds)],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => $locationRules,
            'type' => ['required', 'in:in-person,online'],
            'max_attendees' => ['required', 'integer', 'min:2', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'location.url' => 'Please enter a valid meeting link for online sessions.',
        ]);

        $group = StudyGroup::find((int) $validated['group_id']);

        if (! $group || Gate::denies('createContent', $group)) {
            return back()->with('status', 'You can only schedule sessions for groups you joined.');
        }

        $date = Carbon::parse($validated['date']);
        $sessionEndsAt = Carbon::parse($date->format('Y-m-d').' '.$validated['end_time']);

        $notes = trim($validated['notes'] ?? '');
        $storedNotes = $notes !== ''
            ? $notes
            : 'Scheduled through your StudyHub session planner.';

        $session = StudySession::create([
            'group_id' => (int) $validated['group_id'],
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'session_date' => $date->toDateString(),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['type'] === 'online' ? 'Online meeting' : $validated['location'],
            'meeting_url' => $validated['type'] === 'online' ? $validated['location'] : null,
            'type' => $validated['type'],
            'max_attendees' => (int) $validated['max_attendees'],
            'status' => $sessionEndsAt->isPast() ? 'completed' : 'confirmed',
            'notes' => $storedNotes,
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

    public function rsvp(Request $request, StudySession $session): RedirectResponse
    {
        $targetSession = $session->load(['attendees', 'group']);

        if (! $targetSession->group || Gate::denies('createContent', $targetSession->group)) {
            return redirect()
                ->route('studyhub.student.sessions')
                ->with('status', 'You need to join that private group before joining its session.');
        }

        if ($targetSession->attendees()->where('users.id', $request->user()->id)->exists()) {
            return back()->with('status', 'You already joined this study session.');
        }

        if ($targetSession->attendees()->count() >= $targetSession->max_attendees) {
            return back()->with('status', 'This study session is already full.');
        }

        $targetSession->attendees()->attach($request->user()->id);
        $targetSession->update(['status' => 'confirmed']);
        $this->logActivity(
            'session_rsvp',
            'Session RSVP',
            ($request->user()->display_name ?: $request->user()->name).' joined '.$targetSession->title.'.',
            $targetSession->group,
            $targetSession,
        );

        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/student/groups/'))) {
            return redirect($redirectTo)->with('status', 'You joined the study session.');
        }

        return redirect()
            ->route('studyhub.student.sessions')
            ->with('status', 'You joined the study session.');
    }
}
