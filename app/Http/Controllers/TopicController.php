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
        $topics = Topic::with('user')->get();
        return view('Topics.index', compact('topics'));
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
    // Show edit form
public function edit(Topic $topic)
{
    return view('Topics.edit', compact('topic'));
}

// Update topic in database
public function update(Request $request, Topic $topic)
{
    $request->validate([
        'title' => 'required|min:5|max:255',
        'body'  => 'required|min:10',
    ]);

    $topic->update([
        'Title'       => $request->title,
        'Description' => $request->body,
    ]);

    return redirect()->route('topics.show', $topic)
        ->with('success', 'Topic updated successfully!');
}

// Delete topic
public function destroy(Topic $topic)
{
   // if (auth()->id() != $topic->UserID) {
   //     abort(403, 'Unauthorized');
    //}
    $topic->delete();
    return redirect()->route('topics.index')
        ->with('success', 'Topic deleted successfully!');
}

    // Show a single topic with its posts
    public function show(Topic $topic)
    {
       $excludedPostIDs = \App\Models\ExclusionList::where('ExcludedUserID', '1')
        ->where('ContentType', 'post')
        ->pluck('ContentID');

       $posts = $topic->posts()
        ->with('user')
        ->whereNotIn('PostID', $excludedPostIDs)
        ->paginate(10);

       return view('topics.show', compact('topic', 'posts'));
    }
   
}
