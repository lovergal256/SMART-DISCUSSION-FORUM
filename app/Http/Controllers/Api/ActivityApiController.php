<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Group;
use App\Models\Quiz;
use App\Models\Lecturer;
use App\Models\GroupMember;
use App\Models\QuizScore;
use Illuminate\Http\Request;

class ActivityApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $postsCreated = Post::where('UserID', $user->UserID)->count();
        $topicsCreated = Topic::where('UserID', $user->UserID)->count();
        $groupsCreated = Group::where('CreatedBy', $user->UserID)->count();

        $groupsJoined = GroupMember::where('UserID', $user->UserID)
            ->where('Status', 'approved')
            ->count();

        $quizzesAttempted = \App\Models\QuizScore::where('UserID', $user->UserID)->count();

        return response()->json([
            'postsCreated' => $postsCreated,
            'topicsCreated' => $topicsCreated,
            'groupsCreated' => $groupsCreated,
            'quizzesAttempted' => $quizzesAttempted,
            'groupsJoined' => $groupsJoined,
        ]);
    }
}