<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentDiscussionController;
use App\Http\Controllers\StudentGroupController;
use App\Http\Controllers\StudentLibraryController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentResourceController;
use App\Http\Controllers\StudentSessionController;
use App\Http\Controllers\StudyHubAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/studyhub/dashboard')->name('dashboard');
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
            Route::get('/', [StudentDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
            Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile');
            Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
            Route::get('/theme', [StudentProfileController::class, 'theme'])->name('theme');
            Route::put('/theme', [StudentProfileController::class, 'updateTheme'])->name('theme.update');
            Route::get('/groups', [StudentGroupController::class, 'index'])->name('groups');
            Route::post('/groups', [StudentGroupController::class, 'store'])->name('groups.store');
            Route::post('/groups/{group}/join', [StudentGroupController::class, 'join'])->name('groups.join');
            Route::post('/groups/{group}/leave', [StudentGroupController::class, 'leave'])->name('groups.leave');
            Route::post('/groups/{group}/messages', [StudentGroupController::class, 'storeMessage'])->name('groups.messages.store');
            Route::get('/groups/{group}', [StudentGroupController::class, 'show'])->name('group.show');
            Route::get('/library', [StudentLibraryController::class, 'index'])->name('library');
            Route::post('/library/folders', [StudentLibraryController::class, 'storeFolder'])->name('library.folders.store');
            Route::delete('/library/folders/{folder}', [StudentLibraryController::class, 'destroyFolder'])->name('library.folders.delete');
            Route::patch('/library/saved/{savedResource}', [StudentLibraryController::class, 'updateSaved'])->name('library.saved.update');
            Route::get('/resources', [StudentResourceController::class, 'index'])->name('resources');
            Route::post('/resources', [StudentResourceController::class, 'store'])->name('resources.store');
            Route::post('/resources/{resource}/save', [StudentLibraryController::class, 'save'])->name('resources.save');
            Route::delete('/resources/{resource}/save', [StudentLibraryController::class, 'unsave'])->name('resources.unsave');
            Route::post('/resources/{resource}/reviews', [StudentResourceController::class, 'review'])->name('resources.reviews.store');
            Route::get('/resources/{resource}/view', [StudentResourceController::class, 'view'])->name('resources.view');
            Route::get('/resources/{resource}/download', [StudentResourceController::class, 'download'])->name('resources.download');
            Route::delete('/resources/{resource}', [StudentResourceController::class, 'destroy'])->name('resources.delete');
            Route::get('/discussions', [StudentDiscussionController::class, 'index'])->name('discussions');
            Route::post('/discussions', [StudentDiscussionController::class, 'store'])->name('discussions.store');
            Route::get('/discussions/{discussion}/image', [StudentDiscussionController::class, 'image'])->name('discussions.image');
            Route::get('/discussions/{discussion}', [StudentDiscussionController::class, 'show'])->name('discussions.show');
            Route::delete('/discussions/{discussion}', [StudentDiscussionController::class, 'destroy'])->name('discussions.delete');
            Route::post('/discussions/{discussion}/helpful', [StudentDiscussionController::class, 'toggleHelpful'])->name('discussions.helpful');
            Route::post('/discussions/{discussion}/notifications', [StudentDiscussionController::class, 'toggleNotifications'])->name('discussions.notifications');
            Route::post('/discussions/{discussion}/reply', [StudentDiscussionController::class, 'reply'])->name('discussions.reply');
            Route::post('/notifications/read', [StudentDiscussionController::class, 'markNotificationsRead'])->name('notifications.read');
            Route::get('/sessions', [StudentSessionController::class, 'index'])->name('sessions');
            Route::post('/sessions', [StudentSessionController::class, 'store'])->name('sessions.store');
            Route::post('/sessions/{session}/rsvp', [StudentSessionController::class, 'rsvp'])->name('sessions.rsvp');
        });

        Route::prefix('admin')->name('admin.')->middleware('studyhub.role:admin')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
            Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminDashboardController::class, 'editUser'])->name('users.edit');
            Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');
            Route::get('/groups', [AdminDashboardController::class, 'groups'])->name('groups');
            Route::get('/groups/{group}', [AdminDashboardController::class, 'showGroup'])->name('groups.show');
            Route::put('/groups/{group}', [AdminDashboardController::class, 'updateGroup'])->name('groups.update');
            Route::delete('/groups/{group}', [AdminDashboardController::class, 'deleteGroup'])->name('groups.delete');
            Route::delete('/resources/{resource}', [StudentResourceController::class, 'destroy'])->name('resources.delete');
            Route::delete('/sessions/{session}', [AdminDashboardController::class, 'deleteSession'])->name('sessions.delete');
            Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
            Route::get('/reports/export', [AdminDashboardController::class, 'exportReports'])->name('reports.export');
        });
    });
});
