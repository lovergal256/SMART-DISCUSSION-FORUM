<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ---------------------------------------------------------------
        // NOTE: everything below is placeholder data so the view renders
        // immediately. Swap each block for a real query against your
        // Discussion, Quiz, Group, Recommendation, and Activity models
        // once those tables/relationships exist.
        // ---------------------------------------------------------------

        $stats = [
            ['icon' => '💬', 'value' => '15',  'label' => 'Discussions Joined', 'change' => '3 this week',  'url' => route('discussions.index')],
            ['icon' => '👥', 'value' => '4',   'label' => 'Groups Joined',      'change' => '1 this week',  'url' => route('groups.index')],
            ['icon' => '📖', 'value' => '84%', 'label' => 'Quiz Average',       'change' => '6% this week', 'url' => route('performance.index')],
            ['icon' => '📈', 'value' => '38',  'label' => 'Posts Created',      'change' => '5 this week',  'url' => route('activity.index')],
            ['icon' => '⭐', 'value' => '120', 'label' => 'Points Earned',      'change' => '15 this week', 'url' => route('performance.index')],
        ];

        $discussions = [
            ['id' => 1, 'category' => 'Database', 'title' => 'Normalization in Relational Databases', 'author' => 'John Doe',        'posted_at' => '2 hours ago', 'replies' => 12],
            ['id' => 2, 'category' => 'AI',       'title' => 'AI Ethics and Society',                  'author' => 'Mercy Nabukeera', 'posted_at' => '5 hours ago', 'replies' => 8],
            ['id' => 3, 'category' => 'Web Dev',  'title' => 'Best Practices in Laravel Development',  'author' => 'Brian Kato',      'posted_at' => '1 day ago',   'replies' => 15],
            ['id' => 4, 'category' => 'Security', 'title' => 'Cyber Security Basics',                  'author' => 'Ivan Ssekanyzi',  'posted_at' => '1 day ago',   'replies' => 6],
            ['id' => 5, 'category' => 'Cloud',    'title' => 'Introduction to Cloud Computing',        'author' => 'David Mugisha',   'posted_at' => '2 days ago',  'replies' => 9],
        ];

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

        $activity = [
            ['icon' => '📝', 'label' => 'Posts Created',       'value' => '14',     'change' => '40%'],
            ['icon' => '💬', 'label' => 'Replies Posted',      'value' => '26',     'change' => '25%'],
            ['icon' => '👥', 'label' => 'Discussions Joined',  'value' => '3',      'change' => '50%'],
            ['icon' => '📋', 'label' => 'Quizzes Taken',       'value' => '2',      'change' => '100%'],
            ['icon' => '🕒', 'label' => 'Time Spent',          'value' => '8h 45m', 'change' => '15%'],
        ];

        // Mon..Sun points for the sparkline, pre-plotted onto a 0-300 x 0-110 viewBox.
        $activityChartPoints = '10,80 55,55 100,65 145,15 190,50 235,68 280,25';

        $unreadNotifications = 3;
        $initials = $user->name ? collect(explode(' ', $user->name))->map(fn ($w) => $w[0])->take(2)->implode('') : 'ST';

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
}
