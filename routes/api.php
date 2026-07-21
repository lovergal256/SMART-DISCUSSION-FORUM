<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\DiscussionApiController;
use App\Http\Controllers\Api\TopicApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\ReplyApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ExclusionApiController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\LecturerDashboardApiController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\RecommendationApiController;
use App\Http\Controllers\Api\PerformanceApiController;
use App\Http\Controllers\Api\WarningApiController;
use App\Http\Controllers\Api\ActivityApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProfileApiController;

Route::post('/login', [AuthApiController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthApiController::class, 'user']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::post('/admin/register-lecturer', [AdminApiController::class, 'registerLecturer']);
    Route::get('/admin/groups', [AdminApiController::class, 'groups']);
    Route::get('/admin/discussions', [AdminApiController::class, 'discussions']);
    Route::get('/admin/dashboard', [AdminApiController::class, 'dashboard']);

    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Groups
    Route::get('/groups', [GroupApiController::class, 'index']);
    Route::post('/groups', [GroupApiController::class, 'store']);
    Route::get('/groups/discover', [GroupApiController::class, 'discover']);
    Route::get('/groups/{id}', [GroupApiController::class, 'show']);
    Route::get('/groups/{id}/members', [GroupApiController::class, 'members']);
    Route::patch('/groups/{id}/visibility', [GroupApiController::class, 'toggleVisibility']);
    Route::post('/groups/{id}/members', [GroupApiController::class, 'addMember']);
    Route::post('/groups/{id}/members/{userId}/promote', [GroupApiController::class, 'promote']);
    Route::delete('/groups/{id}/members/{userId}', [GroupApiController::class, 'removeMember']);
    Route::post('/groups/{id}/members/{userId}/blacklist', [GroupApiController::class, 'blacklistMember']);
    Route::post('/groups/{id}/members/{userId}/approve', [GroupApiController::class, 'approveMember']);
    Route::delete('/groups/{id}/members/{userId}/reject', [GroupApiController::class, 'rejectMember']);
    Route::delete('/groups/{id}/leave', [GroupApiController::class, 'leave']);
    Route::delete('/groups/{id}', [GroupApiController::class, 'destroy']);

    // Discussions / Topics / Posts / Replies
    Route::get('/groups/{groupId}/discussions', [DiscussionApiController::class, 'index']);
    Route::get('/discussions/{discussionId}/topics', [TopicApiController::class, 'index']);
    Route::post('/discussions/{discussionId}/topics', [TopicApiController::class, 'store']);
    Route::put('/topics/{topicId}', [TopicApiController::class, 'update']);
    Route::delete('/topics/{topicId}', [TopicApiController::class, 'destroy']);
    Route::get('/topics/{topicId}/posts', [PostApiController::class, 'index']);
    Route::post('/topics/{topicId}/posts', [PostApiController::class, 'store']);
    Route::get('/posts/{postId}/replies', [ReplyApiController::class, 'index']);
    Route::post('/replies', [ReplyApiController::class, 'store']);
    Route::put('/replies/{replyId}', [ReplyApiController::class, 'update']);
    Route::get('/discussions/{discussionId}', [DiscussionApiController::class, 'show']);
    Route::get('/discussions', [DiscussionApiController::class, 'all']);

    // Lecturer dashboard
    Route::get('/lecturer/dashboard', [LecturerDashboardApiController::class, 'index']);
    Route::get('/lecturer/quizzes', [QuizApiController::class, 'indexForLecturer']);

    // Quizzes — specific/static paths before the {quizId} wildcard ones
    Route::post('/groups/{groupId}/quizzes', [QuizApiController::class, 'store']);
    Route::get('/groups/{groupId}/quizzes', [QuizApiController::class, 'index']);
    Route::get('/quizzes', [QuizApiController::class, 'indexAll']);
    Route::get('/quizzes/{quizId}/review', [QuizApiController::class, 'showForLecturer']);
    Route::post('/quizzes/{quizId}/release-results', [QuizApiController::class, 'releaseResults']);
    // Kept both attempt URLs (singular/plural) until confirmed which the client uses
    Route::post('/quizzes/{quizId}/attempt', [QuizApiController::class, 'storeAttempt']);
    Route::post('/quizzes/{quizId}/attempts', [QuizApiController::class, 'storeAttempt']);
    Route::get('/quizzes/{quizId}', [QuizApiController::class, 'show']);

    // Dashboard & insights
    Route::get('/dashboard', [DashboardApiController::class, 'index']);
    Route::get('/recommendations', [RecommendationApiController::class, 'index']);
    Route::get('/performance', [PerformanceApiController::class, 'index']);
    Route::get('/warnings', [WarningApiController::class, 'index']);
    Route::get('/activity', [ActivityApiController::class, 'index']);
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);

    // Profile
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::post('/profile/update', [ProfileApiController::class, 'update']);
    Route::post('/profile/change-password', [ProfileApiController::class, 'changePassword']);
    Route::delete('/profile/delete', [ProfileApiController::class, 'destroy']);

    // Exclusions
    Route::get('/groups/{groupId}/exclusions', [ExclusionApiController::class, 'index']);
    Route::post('/groups/{groupId}/exclusions', [ExclusionApiController::class, 'store']);
    Route::delete('/groups/{groupId}/exclusions/{exclusionId}', [ExclusionApiController::class, 'destroy']);
});