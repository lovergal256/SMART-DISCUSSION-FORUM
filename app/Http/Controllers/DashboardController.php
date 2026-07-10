<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $discussionsCount = \App\Models\Discussion::count();
        $postsCount = \App\Models\Post::count();



        // ---------------------------------------------------------------
        // NOTE: everything below is placeholder data so the view renders
        // immediately. Swap each block for a real query against your
        // Discussion, Quiz, Group, Recommendation, and Activity models
        // once those tables/relationships exist.
        // ---------------------------------------------------------------

        $stats = [
            ['icon' => '💬', 'value' => (string) $discussionsCount,  'label' => 'Discussions Joined', 'change' => '3 this week',  'url' => route('discussions.index')],
            ['icon' => '👥', 'value' => '4',   'label' => 'Groups Joined',      'change' => '1 this week',  'url' => route('groups.index')],
            ['icon' => '📖', 'value' => '84%', 'label' => 'Quiz Average',       'change' => '6% this week', 'url' => route('performance.index')],
            ['icon' => '📈', 'value' => (string) $postsCount,  'label' => 'Posts Created',      'change' => '5 this week',  'url' => route('activity.index')],
            ['icon' => '⭐', 'value' => '120', 'label' => 'Points Earned',      'change' => '15 this week', 'url' => route('performance.index')],
        ];

        $discussions = \App\Models\Discussion::with('user')
           ->latest()
           ->take(5)
           ->get()
           ->map(function ($discussion) {
           $repliesCount = \App\Models\Reply::whereIn('PostID',
            \App\Models\Post::whereIn('TopicID',
                $discussion->topics()->pluck('TopicID')
            )->pluck('PostID')
         )->count();

        return [
            'id' => $discussion->DiscussionID,
            'category' => 'General',
            'title' => $discussion->Title,
            'author' => $discussion->user->FullName ?? 'Unknown',
            'posted_at' => $discussion->created_at->diffForHumans(),
            'replies' => $repliesCount,
        ];
    })
    ->toArray();

        $quizzes = [
            ['id' => 1, 'title' => 'Database Systems Quiz',  'subtitle' => 'Chapters 1 - 4',         'due' => 'Tomorrow, 11:59 PM'],
            ['id' => 2, 'title' => 'Web Development Quiz',   'subtitle' => 'HTML, CSS, JavaScript',  'due' => '12 July 2026, 11:59 PM'],
            ['id' => 3, 'title' => 'AI Fundamentals Quiz',   'subtitle' => 'Basic Concepts',         'due' => '15 July 2026, 11:59 PM'],
        ];

        $recommendations = [
            ['icon' => '👥', 'title' => 'Join the Machine Learning Group',            'subtitle' => 'Connect with students interested in ML',    'url' => route('groups.index')],
            ['icon' => '📄', 'title' => 'Read: Database Indexing Techniques',         'subtitle' => 'Popular article in Database category',      'url' => route('discussions.index')],
            ['icon' => '🗂', 'title' => 'Participate in Cloud Computing discussion',  'subtitle' => 'Trending discussion in your groups',         'url' => route('discussions.index')],
            ['icon' => '🎯', 'title' => 'Take the Laravel Quiz Challenge',            'subtitle' => 'Improve your quiz performance',              'url' => route('quizzes.index')],
        ];

        $groups = [
            ['id' => 1, 'name' => 'Database Systems Group', 'members' => 24, 'new_posts' => 5, 'status' => 'Active'],
            ['id' => 2, 'name' => 'Web Development Group',  'members' => 18, 'new_posts' => 2, 'status' => 'Active'],
            ['id' => 3, 'name' => 'AI & Machine Learning',  'members' => 15, 'new_posts' => 3, 'status' => 'Active'],
            ['id' => 4, 'name' => 'Cyber Security Group',   'members' => 20, 'new_posts' => 1, 'status' => 'Active'],
        ];

        $repliesCount = \App\Models\Reply::where('UserID', $user->UserID)->count();

        $activity = [
         ['icon' => '📝', 'label' => 'Posts Created',       'value' => (string) $postsCount,   'change' => '40%'],
         ['icon' => '💬', 'label' => 'Replies Posted',      'value' => (string) $repliesCount, 'change' => '25%'],
         ['icon' => '👥', 'label' => 'Discussions Joined',  'value' => (string) $discussionsCount, 'change' => '50%'],
         ['icon' => '📋', 'label' => 'Quizzes Taken',       'value' => '2',      'change' => '100%'],
         ['icon' => '🕒', 'label' => 'Time Spent',          'value' => '8h 45m', 'change' => '15%'],
];

        // Mon..Sun points for the sparkline, pre-plotted onto a 0-300 x 0-110 viewBox.
        $activityChartPoints = '10,80 55,55 100,65 145,15 190,50 235,68 280,25';

        $unreadNotifications = 3;
        $initials = $user->FullName ? collect(explode(' ', $user->FullName))->map(fn ($w) => $w[0])->take(2)->implode('') : 'ST';

        return view('dashboard', compact(
            'user',
            'stats',
            'discussions',
            'quizzes',
            'recommendations',
            'groups',
            'activity',
            'activityChartPoints',
            'unreadNotifications',
            'initials'
        ));
    }

public function adminDashboard()
{
    $user = Auth::user();
    $discussions = \App\Models\Discussion::latest()
    ->take(5)
    ->get();

    $stats = [
        [
            'label' => 'Users',
            'value' => User::count(),
            'icon'  => '👥',
            'change' => '0%',
            'url' => '#',
        ],
        [
            'label' => 'Discussions',
            'value' => Discussion::count(),
            'icon'  => '💬',
            'change' => '0%',
            'url' => '#',
        ],
        [
            'label' => 'Posts',
            'value' => Post::count(),
            'icon'  => '📝',
            'change' => '0%',
            'url' => '#',
        ],
    ];

    return view('admin.dashboard', compact('user', 'stats'));
}
}
