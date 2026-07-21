<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\Group;
use Illuminate\Http\Request;

class DiscussionApiController extends Controller
{
    /**
     * List discussions belonging to a specific group,
     * only if the authenticated user is a member of that group.
     */
    public function index(Request $request, $groupId)
    {
        $isMember = $request->user()
            ->groups()
            ->where('groups.GroupID', $groupId)
            ->exists();

        if (! $isMember) {
            return response()->json([
                'message' => 'You are not a member of this group.',
            ], 403);
        }

        $discussions = Discussion::where('GroupID', $groupId)->get();

        return response()->json($discussions);
    }

    /**
     * Show a single discussion's full details, including its group name,
     * only if the authenticated user is a member of that discussion's group.
     */
    public function show(Request $request, $discussionId)
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
            return response()->json([
                'message' => 'You are not a member of this group.',
            ], 403);
        }

        $group = Group::find($discussion->GroupID);

        return response()->json([
            'DiscussionID' => $discussion->DiscussionID,
            'Title' => $discussion->Title,
            'Description' => $discussion->Description,
            'UserID' => $discussion->UserID,
            'GroupID' => $discussion->GroupID,
            'GroupName' => $group->GroupName ?? '',
        ]);
    }
    public function all(Request $request)
{
    $userGroupIds = \App\Models\GroupMember::where('UserID', $request->user()->UserID)
        ->pluck('GroupID');

    $query = Discussion::with(['group', 'topics'])
        ->whereIn('GroupID', $userGroupIds);

    if ($request->filled('search')) {
        $query->where('Title', 'like', '%' . $request->search . '%');
    }

    $discussions = $query->latest()->get()->map(function ($discussion) {
        return [
            'DiscussionID' => $discussion->DiscussionID,
            'Title' => $discussion->Title,
            'Description' => $discussion->Description,
            'UserID' => $discussion->UserID,
            'GroupID' => $discussion->GroupID,
            'GroupName' => optional($discussion->group)->GroupName ?? '',
            'AuthorName' => optional($discussion->user)->FullName ?? ('User #' . $discussion->UserID),
            'TopicCount' => $discussion->topics->count(),
        ];
    });

    return response()->json($discussions);
}
}