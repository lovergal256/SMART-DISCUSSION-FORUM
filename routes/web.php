<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;
Route::get('/', function () {
    return redirect()->route('topics.index');
});
use App\Http\Controllers\TopicController;

Route::resource('topics', TopicController::class);
use App\Http\Controllers\PostController;

Route::resource('topics.posts', PostController::class)
     ->only(['create', 'store', 'show']);
     use App\Http\Controllers\ReplyController;

Route::resource('topics.posts.replies', ReplyController::class)
    ->only(['store', 'destroy', 'edit', 'update']);

Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/create', [GroupController::class, 'create']);
Route::post('/groups', [GroupController::class, 'store']);

use App\Http\Controllers\ExclusionController;

Route::post('/topics/{topic}/posts/{post}/exclude', [ExclusionController::class, 'store'])->name('exclusions.store');
Route::delete('/topics/{topic}/posts/{post}/exclude/{user}', [ExclusionController::class, 'destroy'])->name('exclusions.destroy');

Route::get('/topics/{topic}/exclusions', [ExclusionController::class, 'index'])->name('exclusions.index');