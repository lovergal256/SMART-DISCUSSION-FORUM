<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExclusionController;

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

    Route::get('/discussions', fn () => view('discussions.index'))->name('discussions.index');
    Route::get('/discussions/search', fn () => view('discussions.index'))->name('discussions.search');
    Route::get('/discussions/{id}', fn ($id) => view('discussions.show', compact('id')))->name('discussions.show');

    // --- Group Management Module ---
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}', fn ($id) => view('groups.show', compact('id')))->name('groups.show');

    // --- Quiz Management Module ---
    Route::get('/quizzes', fn () => view('quizzes.index'))->name('quizzes.index');
    Route::get('/quizzes/{id}', fn ($id) => view('quizzes.show', compact('id')))->name('quizzes.show');

    // --- Performance Management Module ---
    Route::get('/performance', fn () => view('performance.index'))->name('performance.index');

    // --- Recommendation Management Module ---
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    // --- Blacklisting and Warning Module (student-facing view) ---
    Route::get('/warnings', fn () => view('warnings.index'))->name('warnings.index');

    // --- Statistics Management Module ---
    Route::get('/activity', fn () => view('activity.index'))->name('activity.index');

    // --- Notification Management Module ---
    Route::get('/notifications', fn () => view('notifications.index'))->name('notifications.index');

    // --- Profile / Account ---
    Route::get('/profile', fn () => view('profile.show'))->name('profile.show');

    // --- Exclusions ---
    Route::post('/topics/{topic}/posts/{post}/exclude', [ExclusionController::class, 'store'])->name('exclusions.store');
    Route::delete('/topics/{topic}/posts/{post}/exclude/{user}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');
    Route::get('/topics/{topic}/exclusions', [ExclusionController::class, 'index'])->name('exclusions.index');
});