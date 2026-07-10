<?php

use App\Http\Controllers\AuthController;
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
use App\Http\Controllers\NotificationController;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

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
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
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

Route::get('/discussions/{discussion}/topics/{topic}', [TopicController::class, 'show'])
    ->name('discussions.topics.show');

    // --- Group Management Module ---
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{id}/members', [GroupController::class, 'addMember'])->name('groups.addMember');
    Route::post('/groups/{groupId}/exclusions', [ExclusionController::class, 'store'])->name('exclusions.store');
    Route::delete('/groups/{groupId}/exclusions/{exclusionId}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');

    // --- Quiz Management Module ---
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{id}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{id}/attempts', [QuizController::class, 'submitAttempt'])->name('quizzes.attempts.store');

    // --- Performance Management Module ---
    Route::get('/performance', fn () => view('performance.index'))->name('performance.index');

    // --- Recommendation Management Module ---
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    // --- Blacklisting and Warning Module (student-facing view) ---
    Route::get('/warnings', fn () => view('warnings.index'))->name('warnings.index');

    // --- Statistics Management Module ---
    Route::get('/activity', fn () => view('activity.index'))->name('activity.index');

    // --- Notification Management Module ---
    Route::get('/notifications', function () {

    $notifications = Notification::where('UserID', Auth::id())
        ->latest()
        ->get();

    return view('student.notifications.index', compact('notifications'));
    })->name('notifications.index');

    // --- Profile / Account ---
    Route::get('/profile', fn () => view('profile.show'))->name('profile.show');

    // --- Exclusions ---
    Route::post('/topics/{topic}/posts/{post}/exclude', [ExclusionController::class, 'store'])->name('exclusions.store');
    Route::delete('/topics/{topic}/posts/{post}/exclude/{user}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');
    Route::get('/topics/{topic}/exclusions', [ExclusionController::class, 'index'])->name('exclusions.index');
});
Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
    ->name('notifications.read');
Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
    ->name('admin.dashboard');
