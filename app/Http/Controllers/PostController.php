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
        $isBlacklisted = \App\Models\Blacklist::where('UserID', Auth::id())
            ->where('EndDate', '>=', now()->toDateString())
            ->exists();

        if ($isBlacklisted) {
            return redirect()->back()->with('error', 'You are currently blocked from posting due to inactivity. This restriction will lift automatically.');
        }

        $request->validate([
            'body' => 'required|min:5',
        ]);
        // Handle exclusions
       $newPost = Post::create([
         'PostID'     => uniqid(),
         'Content'    => $request->body,
         'TopicID'    => $topic->TopicID,
         'UserID'     => Auth::id(),
         'DatePosted' => now(),
]);
     if($request->visibility === 'exclude' && $request->excluded_users) {
        foreach($request->excluded_users as $excludedUserID) {
           \App\Models\ExclusionList::create([
            'UserID'         => Auth::id(),
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
            'UserID'         => Auth::id(),
            'ExcludedUserID' => $user->UserID,
            'ContentType'    => 'post',
            'ContentID'      => $newPost->PostID,
            'ExclusionDate'  => now(),
        ]);
    }
}

    // Notify approved group members about the new post (excluding the poster
    // and anyone this post was excluded from via visibility settings)
    $groupId = $topic->discussion->GroupID ?? null;

    if ($groupId) {
        $excludedUserIds = \App\Models\ExclusionList::where('ContentType', 'post')
            ->where('ContentID', $newPost->PostID)
            ->pluck('ExcludedUserID')
            ->toArray();

        $memberIds = \App\Models\GroupMember::where('GroupID', $groupId)
            ->where('Status', 'approved')
            ->where('UserID', '!=', Auth::id())
            ->whereNotIn('UserID', $excludedUserIds)
            ->pluck('UserID');

        foreach ($memberIds as $memberId) {
            \App\Models\Notification::create([
                'NotificationID' => uniqid(),
                'UserID' => $memberId,
                'Message' => "{$request->user()->FullName} posted in \"{$topic->Title}\".",
                'Type' => 'new_post',
                'Status' => 'Unread',
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



