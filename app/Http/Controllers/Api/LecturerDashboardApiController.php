<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\Group;
use App\Models\Lecturer;
use App\Models\Post;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LecturerDashboardApiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $lecturer = Lecturer::where('UserID', $user->UserID)->first();

        $quizzesCount = Quiz::where('LecturerID', optional($lecturer)->LecturerID)->count();

        $groupIds = Group::whereHas('quizzes', function ($query) use ($lecturer) {
            $query->where('LecturerID', optional($lecturer)->LecturerID);
        })->pluck('GroupID');

        $groupsCount = $groupIds->count();
        $discussionsCount = Discussion::where('UserID', $user->UserID)->count();
        $studentsCount = \App\Models\GroupMember::whereIn('GroupID', $groupIds)->count();

        $stats = [
            ['icon' => '👥', 'value' => $groupsCount, 'label' => 'Teaching Groups', 'change' => 'Active'],
            ['icon' => '💬', 'value' => $discussionsCount, 'label' => 'Discussion Topics', 'change' => 'Created'],
            ['icon' => '📝', 'value' => $quizzesCount, 'label' => 'Quizzes', 'change' => 'Created'],
            ['icon' => '🎓', 'value' => $studentsCount, 'label' => 'Students', 'change' => 'Assigned'],
        ];

        $quizzes = Quiz::where('LecturerID', optional($lecturer)->LecturerID)
            ->with('group')
            ->latest('StartTime')
            ->take(5)
            ->get()
            ->map(function ($quiz) {
                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'duration' => $quiz->Duration,
                    'due' => Carbon::parse($quiz->StartTime)->toIso8601String(),
                ];
            });

        $discussions = Discussion::where('UserID', $user->UserID)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($discussion) {
                return [
                    'id' => $discussion->DiscussionID,
                    'category' => $discussion->Category ?? 'General',
                    'title' => $discussion->Title,
                    'posted_at' => $discussion->created_at?->diffForHumans() ?? 'Recently',
                    'replies' => Post::where('TopicID', $discussion->DiscussionID)->count(),
                ];
            });

        $recommendations = [
            ['icon' => '📚', 'title' => 'Create a New Quiz', 'subtitle' => 'Add another quiz for your students.'],
            ['icon' => '👥', 'title' => 'Manage Groups', 'subtitle' => 'View and organize your teaching groups.'],
            ['icon' => '💬', 'title' => 'Start a Discussion', 'subtitle' => 'Engage students with a new discussion topic.'],
        ];

        return response()->json([
            'name' => $user->FullName,
            'stats' => $stats,
            'quizzes' => $quizzes,
            'discussions' => $discussions,
            'recommendations' => $recommendations,
        ]);
    }
}