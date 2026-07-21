<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use App\Models\Group;
use App\Models\Quiz;
use App\Models\Lecturer;
use App\Models\GroupMember;
use App\Models\QuizScore;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $postsCreated = Post::where('UserID', $user->UserID)->count();
        $topicsCreated = Topic::where('UserID', $user->UserID)->count();
        $groupsCreated = Group::where('CreatedBy', $user->UserID)->count();

        $groupsJoined = GroupMember::where('UserID', $user->UserID)
            ->where('Status', 'approved')
            ->count();

        $quizzesAttempted = \App\Models\QuizScore::where('UserID', $user->UserID)->count();

        return view('activity.index', compact(
            'postsCreated',
            'topicsCreated',
            'groupsCreated',
            'quizzesAttempted',
            'groupsJoined'
        ));
    }
}