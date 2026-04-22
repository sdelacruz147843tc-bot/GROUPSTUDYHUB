<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudyHubRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('studyhub.login');
        }

        if ($user->role !== $role) {
            $targetRoute = $user->role === 'admin'
                ? 'studyhub.admin.dashboard'
                : 'studyhub.student.dashboard';

            return redirect()
                ->route($targetRoute)
                ->with('status', 'That section is only available for '.$role.' accounts.');
        }

        return $next($request);
    }
}
