<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class StudyHubAuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForRole(Auth::user()->role);
        }

        return view('studyhub.login', [
            'features' => [
                [
                    'icon' => 'users',
                    'title' => 'Study Groups',
                    'description' => 'Create and join collaborative study groups',
                ],
                [
                    'icon' => 'book',
                    'title' => 'Resource Library',
                    'description' => 'Share and access learning materials',
                ],
                [
                    'icon' => 'calendar',
                    'title' => 'Study Sessions',
                    'description' => 'Schedule and track study sessions',
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:student,admin'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->role !== $credentials['role']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'role' => 'This account does not have access to the selected dashboard.',
            ]);
        }

        return $this->redirectForRole($user->role);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:student'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'display_name' => $validated['name'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectForRole($user->role);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('studyhub.login')
            ->with('status', 'You have been signed out of StudyHub.');
    }

    public function redirect(): RedirectResponse
    {
        return $this->redirectForRole(Auth::user()->role);
    }

    private function redirectForRole(string $role): RedirectResponse
    {
        return redirect()->route($role === 'admin'
            ? 'studyhub.admin.dashboard'
            : 'studyhub.student.dashboard');
    }
}
