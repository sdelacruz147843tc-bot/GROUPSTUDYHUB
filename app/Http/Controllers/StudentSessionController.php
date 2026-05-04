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
    public function index(): View
    {
        $sessions = $this->getPaginatedStudentSessions();
        $sessionItems = collect($sessions->items());
        $allSessions = collect($this->getStudentSessions());
        $upcomingSessions = $sessionItems->where('phase', 'upcoming')->values()->all();
        $pastSessions = $sessionItems->where('phase', 'past')->values()->all();
        $thisWeekCount = $allSessions
            ->filter(function (array $session) {
                $date = Carbon::parse($session['date']);

                return $date->betweenIncluded(now()->startOfWeek(), now()->endOfWeek());
            })
            ->count();
        $nextSession = collect($upcomingSessions)->first();

        return $this->renderStudent('studyhub.student.sessions', [
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
            'sessionsPaginator' => $sessions,
            'sessionGroups' => $this->getJoinedGroups(),
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
        ], [
            'location.url' => 'Please enter a valid meeting link for online sessions.',
        ]);

        $group = StudyGroup::find((int) $validated['group_id']);

        if (! $group || Gate::denies('createContent', $group)) {
            return back()->with('status', 'You can only schedule sessions for groups you joined.');
        }

        $date = Carbon::parse($validated['date']);
        $sessionEndsAt = Carbon::parse($date->format('Y-m-d').' '.$validated['end_time']);

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
