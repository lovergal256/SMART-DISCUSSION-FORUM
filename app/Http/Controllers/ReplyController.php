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
        ]);

        return redirect()->route('topics.posts.show', [$topic->TopicID, $post->PostID])
                         ->with('success', 'Reply added successfully!');
    }

    // Delete a reply
    public function destroy(Topic $topic, Post $post, Reply $reply)
    {
        // Only the reply owner can delete it
        if ($reply->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $reply->delete();

        return redirect()->route('topics.posts.show', [$topic->id, $post->id])
                         ->with('success', 'Reply deleted successfully!');
    }
}