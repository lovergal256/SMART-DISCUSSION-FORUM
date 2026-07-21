<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $discussionsCount = \App\Models\Discussion::where(function ($query) use ($user) {
            $query->where('UserID', $user->UserID)
                ->orWhereHas('topics', function ($q) use ($user) {
                    $q->where('UserID', $user->UserID);
                })
                ->orWhereHas('topics.posts', function ($q) use ($user) {
                    $q->where('UserID', $user->UserID);
                });
        })->count();

        $postsCount = \App\Models\Post::where('UserID', $user->UserID)->count();

        $groupsJoinedCount = \App\Models\Group::whereHas('members', function ($query) use ($user) {
            $query->where('group_members.UserID', $user->UserID)
                  ->where('group_members.Status', 'approved');
        })->count();

        $stats = [
            ['icon' => '💬', 'value' => (string) $discussionsCount, 'label' => 'Discussions Joined', 'change' => '3 this week'],
            ['icon' => '👥', 'value' => (string) $groupsJoinedCount,  'label' => 'Groups Joined',      'change' => '1 this week'],
            ['icon' => '📖', 'value' => '84%',                       'label' => 'Quiz Average',       'change' => '6% this week'],
            ['icon' => '📈', 'value' => (string) $postsCount,        'label' => 'Posts Created',      'change' => '5 this week'],
            ['icon' => '⭐', 'value' => '120',                       'label' => 'Points Earned',      'change' => '15 this week'],
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
            });

        $myGroupIds = \App\Models\Group::whereHas('members', function ($query) use ($user) {
            $query->where('group_members.UserID', $user->UserID)
                  ->where('group_members.Status', 'approved');
        })->pluck('GroupID');

        $quizzes = \App\Models\Quiz::with('group')
            ->whereIn('GroupID', $myGroupIds)
            ->where('StartTime', '>=', now())
            ->orderBy('StartTime')
            ->take(5)
            ->get()
            ->map(function ($quiz) {
                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'subtitle' => optional($quiz->group)->GroupName ?? 'Group',
                    'due' => \Carbon\Carbon::parse($quiz->StartTime)->format('d M Y, h:i A'),
                ];
            });

        $recommendations = [
            ['icon' => '👥', 'title' => 'Join the Machine Learning Group',           'subtitle' => 'Connect with students interested in ML'],
            ['icon' => '📄', 'title' => 'Read: Database Indexing Techniques',        'subtitle' => 'Popular article in Database category'],
            ['icon' => '🗂', 'title' => 'Participate in Cloud Computing discussion', 'subtitle' => 'Trending discussion in your groups'],
            ['icon' => '🎯', 'title' => 'Take the Laravel Quiz Challenge',           'subtitle' => 'Improve your quiz performance'],
        ];

        $groups = \App\Models\Group::whereHas('members', function ($query) use ($user) {
                $query->where('group_members.UserID', $user->UserID)
                      ->where('group_members.Status', 'approved');
            })
            ->withCount(['members as members_count' => function ($query) {
                $query->where('group_members.Status', 'approved');
            }])
            ->get()
            ->map(function ($group) {
                $newPosts = \App\Models\Discussion::where('GroupID', $group->GroupID)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();

                return [
                    'id' => $group->GroupID,
                    'name' => $group->GroupName,
                    'members' => $group->members_count,
                    'new_posts' => $newPosts,
                    'status' => 'Active',
                ];
            });

        $repliesCount = \App\Models\Reply::where('UserID', $user->UserID)->count();

        $activity = [
            ['icon' => '📝', 'label' => 'Posts Created',      'value' => (string) $postsCount,       'change' => '40%'],
            ['icon' => '💬', 'label' => 'Replies Posted',     'value' => (string) $repliesCount,     'change' => '25%'],
            ['icon' => '👥', 'label' => 'Discussions Joined', 'value' => (string) $discussionsCount, 'change' => '50%'],
            ['icon' => '📋', 'label' => 'Quizzes Taken',      'value' => '2',      'change' => '100%'],
            ['icon' => '🕒', 'label' => 'Time Spent',         'value' => '8h 45m', 'change' => '15%'],
        ];

        $activityChartPoints = [
    ['x' => 10,  'y' => 80],
    ['x' => 55,  'y' => 55],
    ['x' => 100, 'y' => 65],
    ['x' => 145, 'y' => 15],
    ['x' => 190, 'y' => 50],
    ['x' => 235, 'y' => 68],
    ['x' => 280, 'y' => 25],
];

        $unreadNotifications = \App\Models\Notification::where('UserID', $user->UserID)
            ->where('Status', 'Unread')
            ->count();

        $initials = $user->FullName
            ? collect(explode(' ', $user->FullName))->map(fn ($w) => $w[0])->take(2)->implode('')
            : 'ST';

        return response()->json([
            'user' => [
                'name' => $user->FullName,
                'role' => $user->Role ?? 'Student',
                'initials' => $initials,
            ],
            'stats' => $stats,
            'discussions' => $discussions,
            'quizzes' => $quizzes,
            'recommendations' => $recommendations,
            'groups' => $groups,
            'activity' => $activity,
            'activityChartPoints' => $activityChartPoints,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}