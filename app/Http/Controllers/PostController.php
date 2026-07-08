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

        // Handle exclusions
       $newPost = Post::create([
         'PostID'     => uniqid(),
         'Content'    => $request->body,
         'TopicID'    => $topic->TopicID,
         'UserID'     => '1',
         'DatePosted' => now(),
]);
     if($request->visibility === 'exclude' && $request->excluded_users) {
        foreach($request->excluded_users as $excludedUserID) {
           \App\Models\ExclusionList::create([
            'UserID'         => '1',
            'ExcludedUserID' => $excludedUserID,
            'ContentType'    => 'post',
            'ContentID'      => $newPost->PostID,
            'ExclusionDate'  => now(),
        ]);
    }
}   elseif($request->visibility === 'only_share_with' && $request->share_with_users) {
    $allUsers = \App\Models\User::whereNotIn('UserID', $request->share_with_users)->get();
    foreach($allUsers as $user) {
        \App\Models\ExclusionList::create([
            'UserID'         => '1',
            'ExcludedUserID' => $user->UserID,
            'ContentType'    => 'post',
            'ContentID'      => $newPost->PostID,
            'ExclusionDate'  => now(),
        ]);
    }
}
    

        return redirect()->route('topics.show', $topic->TopicID)
                         ->with('success', 'Post added successfully!');
    }

    // Show a single post with its replies
    public function show(Topic $topic, Post $post)
    {
        $replies = $post->replies()->latest()->paginate(10);
        return view('posts.show', compact('topic', 'post', 'replies'));
    }
}



