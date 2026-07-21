<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Discussion;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReplyApiController extends Controller
{
    public function index(Request $request, $postId)
    {
        $post = Post::find($postId);

        if (! $post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $topic = Topic::find($post->TopicID);
        $discussion = Discussion::find($topic->DiscussionID);

        $isMember = $request->user()
            ->groups()
            ->where('groups.GroupID', $discussion->GroupID)
            ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        $replies = Reply::where('PostID', $postId)->get();

        return response()->json($replies);
    }
    public function store(Request $request)
{
    $validated = $request->validate([
        'PostID' => 'required|exists:posts,PostID',
        'Body' => 'required|string',
        'ParentReplyID' => 'nullable|exists:replies,ReplyID',
    ]);

    $post = Post::find($validated['PostID']);
    $topic = Topic::find($post->TopicID);
    $discussion = Discussion::find($topic->DiscussionID);

    $isMember = $request->user()
        ->groups()
        ->where('groups.GroupID', $discussion->GroupID)
        ->exists();

    if (! $isMember) {
        return response()->json(['message' => 'You are not a member of this group.'], 403);
    }

    $reply = Reply::create([
        'ReplyID' => substr((string) Str::uuid()->getHex(), 0, 13),
        'PostID' => $validated['PostID'],
        'UserID' => $request->user()->UserID,
        'Body' => $validated['Body'],
        'ParentReplyID' => $validated['ParentReplyID'] ?? null,
    ]);

    return response()->json($reply, 201);
}
public function update(Request $request, $replyId)
{
    $reply = Reply::find($replyId);
    if (! $reply) {
        return response()->json(['message' => 'Reply not found.'], 404);
    }

    $post = Post::find($reply->PostID);
    $topic = Topic::find($post->TopicID);
    $discussion = Discussion::find($topic->DiscussionID);
    $isMember = $request->user()
        ->groups()
        ->where('groups.GroupID', $discussion->GroupID)
        ->exists();
    if (! $isMember) {
        return response()->json(['message' => 'You are not a member of this group.'], 403);
    }

    if ($reply->UserID != $request->user()->UserID) {
        return response()->json(['message' => 'You can only edit your own replies.'], 403);
    }

    $validated = $request->validate([
        'Body' => 'required|string',
    ]);

    $reply->Body = $validated['Body'];
    $reply->save();

    return response()->json($reply, 200);
}
}