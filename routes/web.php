<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExclusionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\DiscussionController;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Lecturer routes (role 2)
Route::middleware(['auth'])->group(function () {
    Route::get('/lecturer/dashboard', function () {
        return view('lecturer.dashboard');
    })->name('lecturer.dashboard');
});

// Admin routes (role 3)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/lecturers/create', [AdminController::class, 'createLecturer'])->name('admin.lecturers.create');
    Route::post('/admin/lecturers', [AdminController::class, 'storeLecturer'])->name('admin.lecturers.store');
});
/*
|--------------------------------------------------------------------------
| Student routes (role 1) — dashboard + module placeholders
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/student/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');

    // --- Discussion Management Module (topics/posts/replies) ---
    Route::resource('topics', TopicController::class);
    Route::resource('topics.posts', PostController::class)->only(['create', 'store', 'show']);
    Route::resource('topics.posts.replies', ReplyController::class)->only(['store', 'destroy', 'edit', 'update']);



    Route::resource('discussions', DiscussionController::class);
    Route::get('/discussions/{discussion}/export-pdf', [DiscussionController::class, 'exportPdf'])
    ->name('discussions.exportPdf');
    Route::get('/discussions/{discussion}/topics/{topic}', [TopicController::class, 'show'])
    ->name('discussions.topics.show');

    // --- Group Management Module ---
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember'])->name('groups.addMember');
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/{groupId}/exclusions', [ExclusionController::class, 'store'])->name('exclusions.store');
    Route::delete('/groups/{groupId}/exclusions/{exclusionId}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');
    Route::post('/groups/{id}/request-join', [GroupController::class, 'requestJoin'])->name('groups.requestJoin');
    Route::post('/groups/{groupId}/approve/{userId}', [GroupController::class, 'approveMember'])->name('groups.approve');
    Route::post('/groups/{groupId}/reject/{userId}', [GroupController::class, 'rejectMember'])->name('groups.reject');
    Route::delete('/groups/{id}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::post('/groups/{id}/promote/{userId}', [GroupController::class, 'promote'])->name('groups.promote');
    Route::delete('/groups/{id}/members/{userId}', [GroupController::class, 'removeMember'])->name('groups.removeMember');
    Route::patch('/groups/{id}/visibility', [GroupController::class, 'toggleVisibility'])->name('groups.toggleVisibility');

    // --- Quiz Management Module ---
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}/attempts', [QuizController::class, 'storeAttempt'])->name('quizzes.attempts.store');
Route::post('/quizzes/{quiz}/release', [QuizController::class, 'releaseResults'])->name('quizzes.release');

    // --- Performance Management Module ---
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');

    // --- Recommendation Management Module ---
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    // --- Blacklisting and Warning Module (student-facing view) ---
    Route::get('/warnings', function () {
        $user = auth()->user();
        $warnings = $user->warnings()->orderByDesc('WarningDate')->get();
        $activeBlacklist = $user->blacklists()
        ->where('EndDate', '>=', now()->toDateString())
        ->first();

        return view('warnings.index', compact('warnings', 'activeBlacklist'));
    })->name('warnings.index');

    // --- Statistics Management Module ---
    // routes/web.php
Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');

    // --- Notification Management Module ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // --- Profile / Account ---
   // --- Profile / Account ---
    Route::get('/profile', function () {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    })->name('profile.show');

    Route::post('/profile/update', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'Theme' => 'required|in:light,dark',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    })->name('profile.update');

    Route::post('/profile/change-password', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->Password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'Password' => \Illuminate\Support\Facades\Hash::make($validated['new_password']),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password changed successfully.');
    })->name('profile.changePassword');

    Route::delete('/profile/delete', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'delete_confirm_password' => 'required',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($validated['delete_confirm_password'], $user->Password)) {
            return back()->with('error', 'Password is incorrect. Account not deleted.');
        }

        $user->update([
            'FullName' => 'Deleted User',
            'Email' => 'deleted_' . $user->UserID . '_' . uniqid() . '@deleted.local',
            'Password' => \Illuminate\Support\Facades\Hash::make(uniqid() . uniqid()),
        ]);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been deleted.');
    })->name('profile.delete');

    // --- Exclusions ---
    Route::post('/topics/{topic}/posts/{post}/exclude', [ExclusionController::class, 'store'])->name('exclusions.store');
    Route::delete('/topics/{topic}/posts/{post}/exclude/{user}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');
    Route::get('/topics/{topic}/exclusions', [ExclusionController::class, 'index'])->name('exclusions.index');
});
Route::get('/student/notifications', [NotificationController::class, 'index'])->name('student.notifications.index');
Route::post('/student/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
    ->name('student.notifications.read');

Route::get('/lecturer/notifications', [NotificationController::class, 'lecturerIndex'])
    ->name('lecturer.notifications.index');

Route::post('/lecturer/notifications/{id}/read', [NotificationController::class, 'lecturerMarkAsRead'])
    ->name('lecturer.notifications.read');
Route::middleware(['auth'])->group(function () {

    Route::get('/lecturer/notifications', [NotificationController::class, 'lecturerIndex'])
        ->name('lecturer.notifications.index');

    Route::post('/lecturer/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('lecturer.notifications.read');

});
Route::get('/lecturer/dashboard',
[\App\Http\Controllers\LecturerDashboardController::class,'index'])
->middleware('auth')
->name('lecturer.dashboard');

Route::resource('students', StudentController::class);

