<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Lecturer;
use App\Models\Quiz;
use App\Models\Group;
use App\Models\Discussion;
use App\Models\Post;
use App\Models\Notification;

class LecturerDashboardController extends Controller
{
   public function index()
    {
        $user = Auth::user();

       

        // Get lecturer profile
        $lecturer = Lecturer::where('UserID', $user->UserID)->first();
       
        // Statistics
        $quizzesCount = Quiz::where('LecturerID', $lecturer->LecturerID)->count();

        $groupsCount = Group::whereHas('quizzes', function($query) use ($lecturer){
            $query->where('LecturerID', $lecturer->LecturerID);
        })->count();


        $discussionsCount = Discussion::where('UserID', $user->UserID)->count();


        $studentsCount = \App\Models\GroupMember::whereIn(
            'GroupID',
            Group::whereHas('quizzes', function($query) use ($lecturer){
                $query->where('LecturerID', $lecturer->LecturerID);
            })->pluck('GroupID')
        )->count();


        $stats = [

            [
                'icon'=>'👥',
                'value'=>$groupsCount,
                'label'=>'Teaching Groups',
                'change'=>'Active',
                'url'=>route('groups.index')
            ],

            [
                'icon'=>'💬',
                'value'=>$discussionsCount,
                'label'=>'Discussion Topics',
                'change'=>'Created',
                'url'=>route('discussions.index')
            ],

            [
                'icon'=>'📝',
                'value'=>$quizzesCount,
                'label'=>'Quizzes',
                'change'=>'Created',
                'url'=>route('quizzes.index')
            ],

            [
                'icon'=>'🎓',
                'value'=>$studentsCount,
                'label'=>'Students',
                'change'=>'Assigned',
                'url'=>route('students.index')
            ],
        ];


        // Lecturer quizzes

        $quizzes = Quiz::where('LecturerID',$lecturer->LecturerID)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($quiz){

                return [
                    'id'=>$quiz->QuizID,
                    'title'=>$quiz->Title,
                    'subtitle'=>'Duration '.$quiz->Duration.' minutes',
                    'due'=>$quiz->StartTime
                ];

            })->toArray();



        // Lecturer groups

        $groups = Group::whereHas('quizzes',function($query) use ($lecturer){

            $query->where('LecturerID',$lecturer->LecturerID);

        })
        ->get()
        ->map(function($group){

            return [
                'id'=>$group->GroupID,
                'name'=>$group->GroupName,
                'members'=>$group->members()->count(),
                'new_posts'=>Discussion::where('GroupID',$group->GroupID)->count(),
                'status'=>'Active'
            ];

        })->toArray();

        // Recent discussions
        $discussions = Discussion::where('UserID', $user->UserID)
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($discussion) {
           return [
            'id' => $discussion->DiscussionID,
            'category' => $discussion->Category ?? 'General',
            'title' => $discussion->Title,
            'author' => auth()->user()->FullName,
            'posted_at' => $discussion->created_at?->diffForHumans() ?? 'Recently',
            'replies' => Post::where('TopicID', $discussion->DiscussionID)->count(),
        ];
    })
    ->toArray();

        $activity = [

            [
                'icon'=>'📝',
                'label'=>'Quizzes Created',
                'value'=>$quizzesCount,
                'change'=>'100%'
            ],

            [
                'icon'=>'💬',
                'label'=>'Topics Created',
                'value'=>$discussionsCount,
                'change'=>'50%'
            ],

            [
                'icon'=>'👥',
                'label'=>'Students',
                'value'=>$studentsCount,
                'change'=>'20%'
            ],

        ];


        $activityChartPoints =
        '10,80 55,50 100,60 145,30 190,45 235,25 280,20';



        $unreadNotifications = Notification::where('UserID',$user->UserID)
            ->where('Status','Unread')
            ->count();


        $initials = collect(explode(' ', $user->FullName))
            ->map(fn($word)=>$word[0])
            ->take(2)
            ->implode('');

        $recommendations = [
            [
                'icon' => '📚',
                'title' => 'Create a New Quiz',
                'subtitle' => 'Add another quiz for your students.',
                'url' => route('quizzes.create'),
                ],
            [
                'icon' => '👥',
                'title' => 'Manage Groups',
                'subtitle' => 'View and organize your teaching groups.',
                'url' => route('groups.index'),
                ],
            [
                'icon' => '💬',
                'title' => 'Start a Discussion',
                'subtitle' => 'Engage students with a new discussion topic.',
                'url' => route('discussions.index'),
                ],
            ];

    return view('lecturer.dashboard', compact(
    'user',
    'stats',
    'quizzes',
    'groups',
    'discussions',
    'recommendations',
    'activity',
    'activityChartPoints',
    'unreadNotifications',
    'initials'
));
    }
}
