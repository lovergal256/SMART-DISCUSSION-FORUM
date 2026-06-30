<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // Show form to create a post under a topic
    public function create(Topic $topic)
    {
        return view('posts.create', compact('topic'));
    }

    // Save new post to database
    public function store(Request $request, Topic $topic)
    {
        $request->validate([
            'body' => 'required|min:5',
        ]);

        Post::create([
            'body'     => $request->body,
            'TopicId' => $topic->id,
            'UserId'  => Auth::id(),
        ]);

        return redirect()->route('topics.show', $topic->id)
                         ->with('success', 'Post added successfully!');
    }

    // Show a single post with its replies
    public function show(Topic $topic, Post $post)
    {
        $replies = $post->replies()->latest()->paginate(10);
        return view('posts.show', compact('topic', 'post', 'replies'));
    }
}



