<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\TopicController;

Route::resource('topics', TopicController::class);
use App\Http\Controllers\PostController;

Route::resource('topics.posts', PostController::class)
     ->only(['create', 'store', 'show']);
     use App\Http\Controllers\ReplyController;

Route::resource('topics.posts.replies', ReplyController::class)
     ->only(['store', 'destroy']);

Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/create', [GroupController::class, 'create']);
Route::post('/groups', [GroupController::class, 'store']);