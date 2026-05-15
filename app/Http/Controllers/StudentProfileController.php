<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentProfileController extends StudyHubController
{
    public function show(): View
    {
        $joinedGroups = $this->getJoinedGroups();
        $discussions = $this->getStudentDiscussions();
        $sessions = $this->getStudentSessions();
        $profile = $this->getStudentProfile();

        return $this->renderStudent('studyhub.student.profile', [
            'studentProfileForm' => $profile,
            'profileStats' => [
                ['label' => 'Groups', 'value' => count($joinedGroups), 'hint' => 'joined'],
                ['label' => 'Posts', 'value' => collect($discussions)->where('author', $profile['display_name'])->count(), 'hint' => 'started'],
                ['label' => 'Sessions', 'value' => collect($sessions)->where('phase', 'upcoming')->count(), 'hint' => 'upcoming'],
            ],
            'profileHighlights' => [
                'primary_group' => collect($joinedGroups)->first()['name'] ?? 'Join a study group',
                'next_session' => collect($sessions)->where('phase', 'upcoming')->first()['title'] ?? 'No upcoming session yet',
                'theme' => $profile['theme'] === 'dark' ? 'Dark' : 'Light',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
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

    public function theme(): View
    {
        return $this->renderStudent('studyhub.student.theme', [
            'profileOptions' => $this->studentProfileOptions(),
            'studentProfileForm' => $this->getStudentProfile(),
        ]);
    }

    public function updateTheme(Request $request): RedirectResponse
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
            'appearance' => ['required', 'in:light,dark'],
        ]);

        $request->user()->forceFill([
            'theme' => $validated['appearance'] === 'dark' ? 'dark' : 'forest',
            'surface_style' => 'soft',
            'interface_density' => 'comfortable',
        ])->save();

        return redirect()
            ->route('studyhub.student.theme')
            ->with('status', 'Your StudyHub theme settings were saved.');
    }
}
