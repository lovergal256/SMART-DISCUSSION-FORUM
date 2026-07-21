<?php

<<<<<<< HEAD
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\DiscussionApiController;
use App\Http\Controllers\Api\TopicApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\ReplyApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ExclusionApiController;



Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [AuthApiController::class, 'user']);
    Route::get('/groups', [GroupApiController::class, 'index']);
    Route::get('/groups/{groupId}/discussions', [DiscussionApiController::class, 'index']);
    Route::get('/groups/{id}/members', [\App\Http\Controllers\Api\GroupApiController::class, 'members']);
    Route::post('/groups/{id}/members/{userId}/blacklist', [\App\Http\Controllers\Api\GroupApiController::class, 'blacklistMember']);
    Route::get('/groups/{id}', [\App\Http\Controllers\Api\GroupApiController::class, 'show']);
    Route::get('/groups/{id}/members', [\App\Http\Controllers\Api\GroupApiController::class, 'members']);
    Route::patch('/groups/{id}/visibility', [\App\Http\Controllers\Api\GroupApiController::class, 'toggleVisibility']);
    Route::post('/groups/{id}/members', [\App\Http\Controllers\Api\GroupApiController::class, 'addMember']);
    Route::post('/groups/{id}/members/{userId}/promote', [\App\Http\Controllers\Api\GroupApiController::class, 'promote']);
    Route::delete('/groups/{id}/members/{userId}', [\App\Http\Controllers\Api\GroupApiController::class, 'removeMember']);
    Route::post('/groups/{id}/members/{userId}/blacklist', [\App\Http\Controllers\Api\GroupApiController::class, 'blacklistMember']);
    Route::post('/groups/{id}/members/{userId}/approve', [\App\Http\Controllers\Api\GroupApiController::class, 'approveMember']);
    Route::delete('/groups/{id}/members/{userId}/reject', [\App\Http\Controllers\Api\GroupApiController::class, 'rejectMember']);
    Route::delete('/groups/{id}/leave', [\App\Http\Controllers\Api\GroupApiController::class, 'leave']);
    Route::delete('/groups/{id}', [\App\Http\Controllers\Api\GroupApiController::class, 'destroy']);
    Route::get('/discussions/{discussionId}/topics', [TopicApiController::class, 'index']);
    Route::get('/topics/{topicId}/posts', [PostApiController::class, 'index']);
    Route::get('/posts/{postId}/replies', [ReplyApiController::class, 'index']);
    Route::post('/replies', [ReplyApiController::class, 'store']);
    Route::get('/dashboard', [DashboardApiController::class, 'index']);
    Route::get('/discussions/{discussionId}', [DiscussionApiController::class, 'show']);
    Route::get('/discussions', [DiscussionApiController::class, 'all']);
    Route::get('/quizzes', [\App\Http\Controllers\Api\QuizApiController::class, 'index']);
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Api\QuizApiController::class, 'show']);
    Route::post('/quizzes/{quiz}/attempt', [\App\Http\Controllers\Api\QuizApiController::class, 'storeAttempt']);
    Route::get('/recommendations', [\App\Http\Controllers\Api\RecommendationApiController::class, 'index']);
    Route::get('/performance', [\App\Http\Controllers\Api\PerformanceApiController::class, 'index']);
    Route::get('/warnings', [\App\Http\Controllers\Api\WarningApiController::class, 'index']);
    Route::get('/activity', [\App\Http\Controllers\Api\ActivityApiController::class, 'index']);
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationApiController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead']);
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'show']);
    Route::post('/profile/update', [\App\Http\Controllers\Api\ProfileApiController::class, 'update']);
    Route::post('/profile/change-password', [\App\Http\Controllers\Api\ProfileApiController::class, 'changePassword']);
    Route::delete('/profile/delete', [\App\Http\Controllers\Api\ProfileApiController::class, 'destroy']);
    Route::post('/groups', [GroupApiController::class, 'store']);
    Route::post('/groups/{id}/members', [GroupApiController::class, 'addMember']);
    Route::post('/groups/{id}/members/{userId}/approve', [GroupApiController::class, 'approveMember']);
    Route::post('/groups/{id}/members/{userId}/reject', [GroupApiController::class, 'rejectMember']);
    Route::post('/groups/{id}/leave', [GroupApiController::class, 'leave']);
    Route::delete('/groups/{id}', [GroupApiController::class, 'destroy']);
    Route::get('/groups/{groupId}/exclusions', [ExclusionApiController::class, 'index']);
    Route::post('/groups/{groupId}/exclusions', [ExclusionApiController::class, 'store']);
    Route::delete('/groups/{groupId}/exclusions/{exclusionId}', [ExclusionApiController::class, 'destroy']);
    Route::post('/discussions/{discussionId}/topics', [TopicApiController::class, 'store']);
    Route::put('/topics/{topicId}', [TopicApiController::class, 'update']);
    Route::delete('/topics/{topicId}', [TopicApiController::class, 'destroy']);
    Route::post('/topics/{topicId}/posts', [PostApiController::class, 'store']);
    Route::put('/replies/{replyId}', [ReplyApiController::class, 'update']);



=======
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\LecturerDashboardApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthApiController::class, 'login']);

Route::get('/lecturer/dashboard', [LecturerDashboardApiController::class, 'index']);
Route::get('/quizzes/{quizId}/review', [QuizApiController::class, 'showForLecturer']);
Route::post('/quizzes/{quizId}/release-results', [QuizApiController::class, 'releaseResults']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Group endpoints
    Route::get('/groups', [GroupApiController::class, 'index']);
    Route::post('/groups', [GroupApiController::class, 'store']);
    Route::get('/groups/discover', [GroupApiController::class, 'discover']);
    Route::get('/groups/{id}', [GroupApiController::class, 'show']);
    
    // Quiz endpoints
    Route::post('/groups/{groupId}/quizzes', [QuizApiController::class, 'store']);
    Route::get('/groups/{groupId}/quizzes', [QuizApiController::class, 'index']);
Route::get('/quizzes/{quizId}', [QuizApiController::class, 'show']);
Route::get('/quizzes', [QuizApiController::class, 'indexAll']);
Route::post('/quizzes/{quizId}/attempts', [QuizApiController::class, 'storeAttempt']);
Route::get('/lecturer/quizzes', [QuizApiController::class, 'indexForLecturer']);
>>>>>>> origin/main
});