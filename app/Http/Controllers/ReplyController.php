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
    $isBlacklisted = \App\Models\Blacklist::where('UserID', Auth::id())
        ->where('EndDate', '>=', now()->toDateString())
        ->exists();

    if ($isBlacklisted) {
        return redirect()->back()->with('error', 'You are currently blocked from replying due to inactivity. This restriction will lift automatically.');
    }

    $groupId = $topic->discussion->GroupID ?? null;
    if ($groupId) {
        $isApprovedMember = \App\Models\GroupMember::where('GroupID', $groupId)
            ->where('UserID', Auth::user()->UserID)
            ->where('Status', 'approved')
            ->exists();

        if (!$isApprovedMember) {
            return redirect()->back()->with('error', 'You must be an approved member of this group to reply.');
        }
    }

    $request->validate([
        'body' => 'required|min:3',
    ]);

   Reply::create([
    'ReplyID' => uniqid(),
    'Body'    => $request->body,
    'PostID'  => $post->PostID,
    'UserID'  => Auth::user()->UserID,
    'ParentReplyID' => $request->parent_reply_id ?? null,
    ]);

    // Notify the post's author and anyone else who has replied to it,
    // excluding whoever is replying right now
    $notifyUserIds = collect([$post->UserID])
        ->merge($post->replies()->pluck('UserID'))
        ->unique()
        ->reject(fn ($id) => $id == Auth::id());

    foreach ($notifyUserIds as $notifyUserId) {
        \App\Models\Notification::create([
            'NotificationID' => uniqid(),
            'UserID' => $notifyUserId,
            'Message' => "{$request->user()->FullName} replied to a post in \"{$topic->Title}\".",
            'Type' => 'new_reply',
            'Status' => 'Unread',
        ]);
    }

    return redirect()->route('topics.posts.show', [$topic->TopicID, $post->PostID])
                     ->with('success', 'Reply added successfully!');
}

    // Delete a reply
       public function destroy(Topic $topic, Post $post, Reply $reply)
{
    if ($reply->UserID != Auth::user()->UserID) {
        return redirect()->back()->with('error', 'You can only delete your own replies.');
    }

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
    if ($reply->UserID != Auth::user()->UserID) {
        return redirect()->back()->with('error', 'You can only edit your own replies.');
    }

    $request->validate(['body' => 'required|min:2']);

    $reply->update(['Body' => $request->body]);

    return redirect()->route('topics.posts.show', [$topic, $post])
        ->with('success', 'Reply updated successfully!');
}
}