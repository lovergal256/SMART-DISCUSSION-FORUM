<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicApiController extends Controller
{
    public function index(Request $request, $discussionId)
    {
        $discussion = Discussion::find($discussionId);

        if (! $discussion) {
            return response()->json(['message' => 'Discussion not found.'], 404);
        }

        $isMember = $request->user()
            ->groups()
            ->where('groups.GroupID', $discussion->GroupID)
            ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        $topics = Topic::where('DiscussionID', $discussionId)->get();

        return response()->json($topics);
    }
    public function store(Request $request, $discussionId)
{
    $discussion = \App\Models\Discussion::find($discussionId);

    if (! $discussion) {
        return response()->json(['message' => 'Discussion not found.'], 404);
    }

    $isMember = $request->user()
        ->groups()
        ->where('groups.GroupID', $discussion->GroupID)
        ->exists();

    if (! $isMember) {
        return response()->json(['message' => 'You are not a member of this group.'], 403);
    }

    $validated = $request->validate([
        'Title' => 'required|string|max:255',
        'Description' => 'nullable|string',
    ]);

    $topic = new \App\Models\Topic();
    $topic->DiscussionID = $discussionId;
    $topic->UserID = $request->user()->UserID;
    $topic->Title = $validated['Title'];
    $topic->Description = $validated['Description'] ?? '';
    $topic->Status = 'open';
    $topic->save();

    return response()->json($topic, 201);
}
public function update(Request $request, $topicId)
{
    $topic = \App\Models\Topic::find($topicId);
    if (! $topic) {
        return response()->json(['message' => 'Topic not found.'], 404);
    }

    $discussion = \App\Models\Discussion::find($topic->DiscussionID);
    $isMember = $request->user()
        ->groups()
        ->where('groups.GroupID', $discussion->GroupID)
        ->exists();
    if (! $isMember) {
        return response()->json(['message' => 'You are not a member of this group.'], 403);
    }

    $validated = $request->validate([
        'Title' => 'required|string|max:255',
        'Description' => 'nullable|string',
    ]);

    $topic->Title = $validated['Title'];
    $topic->Description = $validated['Description'] ?? '';
    $topic->save();

    return response()->json($topic, 200);
}

public function destroy(Request $request, $topicId)
{
    $topic = \App\Models\Topic::find($topicId);
    if (! $topic) {
        return response()->json(['message' => 'Topic not found.'], 404);
    }

    $discussion = \App\Models\Discussion::find($topic->DiscussionID);
    $isMember = $request->user()
        ->groups()
        ->where('groups.GroupID', $discussion->GroupID)
        ->exists();
    if (! $isMember) {
        return response()->json(['message' => 'You are not a member of this group.'], 403);
    }

    $topic->delete();

    return response()->json(['message' => 'Topic deleted.'], 200);
}
}