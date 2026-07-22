<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Discussion;
use App\Models\Post;
use Illuminate\Http\Request;
class PostApiController extends Controller
{
    public function index(Request $request, $topicId)
    {
        $topic = Topic::find($topicId);
        if (! $topic) {
            return response()->json(['message' => 'Topic not found.'], 404);
        }
        $discussion = Discussion::find($topic->DiscussionID);
        $isMember = $request->user()
            ->groups()
            ->where('groups.GroupID', $discussion->GroupID)
            ->exists();
        if (! $isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }
        $posts = Post::where('TopicID', $topicId)->get();
        return response()->json($posts);
    }

    public function store(Request $request, $topicId)
    {
        $topic = Topic::find($topicId);
        if (! $topic) {
            return response()->json(['message' => 'Topic not found.'], 404);
        }
        $discussion = Discussion::find($topic->DiscussionID);
        $isMember = $request->user()
            ->groups()
            ->where('groups.GroupID', $discussion->GroupID)
            ->exists();
        if (! $isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $post = new Post();
        $post->PostID = uniqid();
        $post->TopicID = $topicId;
        $post->UserID = $request->user()->UserID;
        $post->content = $validated['content'];
        $post->DatePosted = now();
        $post->save();

        return response()->json($post, 201);
    }
}
