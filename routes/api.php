<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\LecturerDashboardApiController;
use App\Http\Controllers\Api\AdminApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthApiController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/admin/register-lecturer', [AdminApiController::class, 'registerLecturer']);
    Route::get('/admin/groups', [AdminApiController::class, 'groups']);
    Route::get('/admin/discussions', [AdminApiController::class, 'discussions']);
    Route::get('/admin/dashboard', [AdminApiController::class, 'dashboard']);

    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Group endpoints
    Route::get('/groups', [GroupApiController::class, 'index']);
    Route::post('/groups', [GroupApiController::class, 'store']);
    Route::get('/groups/discover', [GroupApiController::class, 'discover']);
    Route::get('/groups/{id}', [GroupApiController::class, 'show']);

    // Lecturer dashboard
    Route::get('/lecturer/dashboard', [LecturerDashboardApiController::class, 'index']);
    Route::get('/lecturer/quizzes', [QuizApiController::class, 'indexForLecturer']);

    // Quiz endpoints — specific/static paths before the {quizId} wildcard ones
    Route::post('/groups/{groupId}/quizzes', [QuizApiController::class, 'store']);
    Route::get('/groups/{groupId}/quizzes', [QuizApiController::class, 'index']);
    Route::get('/quizzes', [QuizApiController::class, 'indexAll']);
    Route::get('/quizzes/{quizId}/review', [QuizApiController::class, 'showForLecturer']);
    Route::post('/quizzes/{quizId}/release-results', [QuizApiController::class, 'releaseResults']);
    Route::post('/quizzes/{quizId}/attempts', [QuizApiController::class, 'storeAttempt']);
    Route::get('/quizzes/{quizId}', [QuizApiController::class, 'show']);
});