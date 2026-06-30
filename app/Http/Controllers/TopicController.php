<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
    // Show all topics
    public function index()
    {
        $topics = Topic::paginate(10);
        return view('topics.index', compact('topics'));
    }

    // Show form to create a topic
    public function create()
    {
        return view('topics.create');
    }

    // Save new topic to database
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:255',
            'body'  => 'required|min:10',
        ]);

        Topic::create([
            'Title'   => $request->title,
            'Description'    => $request->body,
            'UserID' => 1,
            'GroupID'=>1,
        ]);

        return redirect()->route('topics.index')
                         ->with('success', 'Topic created successfully!');
    }

    // Show a single topic with its posts
    public function show(Topic $topic)
    {
        $posts = $topic->posts()->paginate(10);
        return view('topics.show', compact('topic', 'posts'));
    }
}
