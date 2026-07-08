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
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    Route::get('/discussions', function () {
        $topics = Topic::with(['user', 'group'])
            ->withCount('posts')
            ->latest('TopicID')
            ->paginate(10);

        return view('discussions.index', compact('topics'));
    })->name('discussions.index');

    Route::get('/discussions/search', function (Request $request) {
        $query = $request->input('q');
        $topics = Topic::with(['user', 'group'])
            ->withCount('posts')
            ->when($query, fn ($q) => $q->where('Title', 'like', "%{$query}%"))
            ->latest('TopicID')
            ->paginate(10);

        return view('discussions.index', compact('topics', 'query'));
    })->name('discussions.search');

    Route::get('/discussions/{id}', function ($id) {
        $topic = Topic::with(['user', 'group'])->findOrFail($id);
        $posts = Post::with(['user', 'replies.user'])
            ->where('TopicID', $id)
            ->latest('DatePosted')
            ->get();

        return view('discussions.show', compact('topic', 'posts'));
    })->name('discussions.show');

    // --- Group Management Module ---
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}', fn ($id) => view('groups.show', compact('id')))->name('groups.show');

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
    Route::get('/notifications', fn () => view('notifications.index'))->name('notifications.index');

    // --- Profile / Account ---
    Route::get('/profile', fn () => view('profile.show'))->name('profile.show');

    // --- Exclusions ---
    Route::post('/topics/{topic}/posts/{post}/exclude', [ExclusionController::class, 'store'])->name('exclusions.store');
    Route::delete('/topics/{topic}/posts/{post}/exclude/{user}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');
    Route::get('/topics/{topic}/exclusions', [ExclusionController::class, 'index'])->name('exclusions.index');
});
