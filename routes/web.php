<?php

use App\Http\Controllers\StudyHubAuthController;
use App\Http\Controllers\StudyHubController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::prefix('studyhub')->name('studyhub.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', [StudyHubAuthController::class, 'create'])->name('login');
        Route::post('/login', [StudyHubAuthController::class, 'store'])->name('authenticate');
        Route::post('/register', [StudyHubAuthController::class, 'register'])->name('register');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [StudyHubAuthController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [StudyHubAuthController::class, 'redirect'])->name('dashboard');

        Route::prefix('student')->name('student.')->middleware('studyhub.role:student')->group(function () {
            Route::get('/', [StudyHubController::class, 'studentDashboard'])->name('index');
            Route::get('/dashboard', [StudyHubController::class, 'studentDashboard'])->name('dashboard');
            Route::get('/profile', [StudyHubController::class, 'studentProfile'])->name('profile');
            Route::put('/profile', [StudyHubController::class, 'updateStudentProfile'])->name('profile.update');
            Route::get('/theme', [StudyHubController::class, 'studentTheme'])->name('theme');
            Route::put('/theme', [StudyHubController::class, 'updateStudentTheme'])->name('theme.update');
            Route::get('/groups', [StudyHubController::class, 'studentGroups'])->name('groups');
            Route::post('/groups', [StudyHubController::class, 'storeStudentGroup'])->name('groups.store');
            Route::post('/groups/{id}/join', [StudyHubController::class, 'joinStudentGroup'])->name('groups.join');
            Route::post('/groups/{id}/leave', [StudyHubController::class, 'leaveStudentGroup'])->name('groups.leave');
            Route::get('/groups/{id}', [StudyHubController::class, 'studentGroupDetail'])->name('group.show');
            Route::get('/resources', [StudyHubController::class, 'studentResources'])->name('resources');
            Route::post('/resources', [StudyHubController::class, 'storeStudentResource'])->name('resources.store');
            Route::get('/discussions', [StudyHubController::class, 'studentDiscussions'])->name('discussions');
            Route::post('/discussions', [StudyHubController::class, 'storeStudentDiscussion'])->name('discussions.store');
            Route::get('/discussions/{id}', [StudyHubController::class, 'studentDiscussionShow'])->name('discussions.show');
            Route::post('/discussions/{id}/delete', [StudyHubController::class, 'deleteStudentDiscussion'])->name('discussions.delete');
            Route::post('/discussions/{id}/reply', [StudyHubController::class, 'storeStudentDiscussionReply'])->name('discussions.reply');
            Route::get('/sessions', [StudyHubController::class, 'studentSessions'])->name('sessions');
            Route::post('/sessions', [StudyHubController::class, 'storeStudentSession'])->name('sessions.store');
            Route::post('/sessions/{id}/rsvp', [StudyHubController::class, 'rsvpStudentSession'])->name('sessions.rsvp');
        });

        Route::prefix('admin')->name('admin.')->middleware('studyhub.role:admin')->group(function () {
            Route::get('/', [StudyHubController::class, 'adminDashboard'])->name('index');
            Route::get('/dashboard', [StudyHubController::class, 'adminDashboard'])->name('dashboard');
            Route::get('/users', [StudyHubController::class, 'adminUsers'])->name('users');
            Route::post('/users', [StudyHubController::class, 'storeAdminUser'])->name('users.store');
            Route::post('/users/{user}/delete', [StudyHubController::class, 'deleteAdminUser'])->name('users.delete');
            Route::get('/groups', [StudyHubController::class, 'adminGroups'])->name('groups');
            Route::get('/reports', [StudyHubController::class, 'adminReports'])->name('reports');
        });
    });
});
