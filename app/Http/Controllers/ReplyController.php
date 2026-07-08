<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    // Save a new reply to a post
    public function store(Request $request, Topic $topic, Post $post)
    {
        $request->validate([
            'body' => 'required|min:3',
        ]);

        Reply::create([
        'ReplyID' => uniqid(),
        'Body'    => $request->body,
        'PostID'  => $post->PostID,
        'UserID'  => '1',
        'ParentReplyID' => $request->parent_reply_id ?? null,
        ]);

        return redirect()->route('topics.posts.show', [$topic->TopicID, $post->PostID])
                         ->with('success', 'Reply added successfully!');
    }

    // Delete a reply
    public function destroy(Topic $topic, Post $post, Reply $reply)
    {
        // Only the reply owner can delete it
       // if ($reply->UserID !== Auth::id()) {
       //     return redirect()->back()->with('error', 'Unauthorized action.');
        //}
     Reply::where('ParentReplyID', $reply->ReplyID)->update(['ParentReplyID' => null]);

        $reply->delete();

        return redirect()->route('topics.posts.show', [$topic, $post])
                         ->with('success', 'Reply deleted successfully!');
    }
     // Show edit form
    public function edit(Topic $topic, Post $post, Reply $reply)
    {
     return view('Posts.replies.edit', compact('topic', 'post', 'reply'));
    }

     // Update reply
    public function update(Request $request, Topic $topic, Post $post, Reply $reply)
    {
     $request->validate(['body' => 'required|min:2']);
    
     $reply->update(['Body' => $request->body]);
    
     return redirect()->route('topics.posts.show', [$topic, $post])
        ->with('success', 'Reply updated successfully!');
}  
}