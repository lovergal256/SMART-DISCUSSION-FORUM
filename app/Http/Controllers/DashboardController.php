<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Topic;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Quiz;
use App\Models\QuizScore;
use App\Services\CollaborativeFilteringService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
$userId = $user->UserID;

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

        // --- Shared week boundaries (used by Quiz Average, My Activity, Sparkline) ---
        $weekStart     = Carbon::now()->subDays(6)->startOfDay();
        $prevWeekStart = Carbon::now()->subDays(13)->startOfDay();
        $prevWeekEnd   = Carbon::now()->subDays(7)->endOfDay();

        // --- Quiz Average (real) ---
        $avgQuizScore = round(QuizScore::where('UserID', $userId)->avg('Score') ?? 0);
        $avgThisWeek = QuizScore::where('UserID', $userId)->whereBetween('DateRecorded', [$weekStart, now()])->avg('Score');
        $avgLastWeek = QuizScore::where('UserID', $userId)->whereBetween('DateRecorded', [$prevWeekStart, $prevWeekEnd])->avg('Score');
        $quizAvgChange = round(($avgThisWeek ?? 0) - ($avgLastWeek ?? 0));

        $stats = [
            ['icon' => '💬', 'value' => (string) $discussionsCount,  'label' => 'Discussions Joined', 'change' => '3 this week',  'url' => route('discussions.index')],
            ['icon' => '👥', 'value' => (string) $groupsJoinedCount,   'label' => 'Groups Joined',      'change' => '1 this week',  'url' => route('groups.index')],
            ['icon' => '📖', 'value' => $avgQuizScore . '%', 'label' => 'Quiz Average', 'change' => abs($quizAvgChange) . '% this week', 'url' => route('performance.index')],
            ['icon' => '📈', 'value' => (string) $postsCount,  'label' => 'Posts Created',      'change' => '5 this week',  'url' => route('activity.index')],
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

$myGroupIds = \App\Models\Group::whereHas('members', function ($query) use ($user) {
            $query->where('group_members.UserID', $user->UserID)
                ->where('group_members.Status', 'approved');
        })->pluck('GroupID');

        $quizzes = Quiz::with('group')
            ->whereIn('GroupID', $myGroupIds)
            ->where('StartTime', '>=', now())
            ->orderBy('StartTime')
            ->take(5)
            ->get()
            ->map(function ($quiz) {
                $start = Carbon::parse($quiz->StartTime);
                $due = $start->isToday()
                    ? 'Today, ' . $start->format('g:i A')
                    : ($start->isTomorrow()
                        ? 'Tomorrow, ' . $start->format('g:i A')
                        : $start->format('d F Y, g:i A'));

                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'subtitle' => optional($quiz->group)->GroupName ?? 'Group',
                    'due' => $due,
                ];
            })

                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'subtitle' => $quiz->group->GroupName ?? 'General',
                    'due' => $due,
                ];
            })
            ->toArray();

        // --- Recommended For You (real, mirrors Recommendations page logic) ---
        $recommendations = [];

        $cf = new CollaborativeFilteringService();
        $recommendedGroupIds = $cf->recommendGroups($userId, 1);

        if (!empty($recommendedGroupIds)) {
            $group = DB::table('groups')->where('GroupID', $recommendedGroupIds[0])->first();
        } else {
            $group = DB::table('groups')
                ->leftJoin('group_members', function ($join) use ($userId) {
                    $join->on('groups.GroupID', '=', 'group_members.GroupID')
                         ->where('group_members.UserID', '=', $userId);
                })
                ->select('groups.GroupID', 'groups.GroupName')
                ->whereNull('group_members.UserID')
                ->first();
        }

        if ($group) {
            $recommendations[] = [
                'icon' => '👥',
                'title' => 'Join the ' . $group->GroupName . ' Group',
                'subtitle' => 'Suggested based on your group activity',
                'url' => route('groups.show', $group->GroupID),
            ];
        }

        $trendingTopic = DB::table('topics')
            ->leftJoin('posts', 'topics.TopicID', '=', 'posts.TopicID')
            ->select('topics.TopicID', 'topics.Title', DB::raw('COUNT(posts.PostID) as post_count'))
            ->where('topics.Status', 'open')
            ->groupBy('topics.TopicID', 'topics.Title')
            ->orderByDesc('post_count')
            ->first();

        if ($trendingTopic) {
            $recommendations[] = [
                'icon' => '🗂',
                'title' => 'Participate in: ' . $trendingTopic->Title,
                'subtitle' => 'Trending discussion on the forum',
                'url' => route('discussions.show', $trendingTopic->TopicID),
            ];
        }

        if (!empty($quizzes)) {
            $nextQuiz = $quizzes[0];
            $recommendations[] = [
                'icon' => '🎯',
                'title' => 'Take: ' . $nextQuiz['title'],
                'subtitle' => 'Quiz due ' . $nextQuiz['due'],
                'url' => route('quizzes.show', $nextQuiz['id']),
            ];
        }

        // --- My Groups ---
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
            })
            ->toArray();

        // --- My Activity (This Week) - real weekly stats ---
        $postsThisWeek  = Post::where('UserID', $userId)->whereBetween('DatePosted', [$weekStart, now()])->count();
        $postsLastWeek  = Post::where('UserID', $userId)->whereBetween('DatePosted', [$prevWeekStart, $prevWeekEnd])->count();

        $repliesThisWeek = Reply::where('UserID', $userId)->whereBetween('created_at', [$weekStart, now()])->count();
        $repliesLastWeek = Reply::where('UserID', $userId)->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])->count();

        $topicsThisWeek = Topic::where('UserID', $userId)->whereBetween('created_at', [$weekStart, now()])->count();
        $topicsLastWeek = Topic::where('UserID', $userId)->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])->count();

        $quizzesThisWeek = QuizScore::where('UserID', $userId)->whereBetween('DateRecorded', [$weekStart, now()])->count();
        $quizzesLastWeek = QuizScore::where('UserID', $userId)->whereBetween('DateRecorded', [$prevWeekStart, $prevWeekEnd])->count();

        $pctChange = function ($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return abs(round((($current - $previous) / $previous) * 100));
        };

        $activity = [
            ['icon' => '📝', 'label' => 'Posts Created',      'value' => (string) $postsThisWeek,   'change' => $pctChange($postsThisWeek, $postsLastWeek) . '%'],
            ['icon' => '💬', 'label' => 'Replies Posted',     'value' => (string) $repliesThisWeek, 'change' => $pctChange($repliesThisWeek, $repliesLastWeek) . '%'],
            ['icon' => '👥', 'label' => 'Discussions Joined', 'value' => (string) $topicsThisWeek,  'change' => $pctChange($topicsThisWeek, $topicsLastWeek) . '%'],
            ['icon' => '📋', 'label' => 'Quizzes Taken',      'value' => (string) $quizzesThisWeek, 'change' => $pctChange($quizzesThisWeek, $quizzesLastWeek) . '%'],
        ];

        // --- Sparkline chart points (7-day rolling total, matches viewBox 0 0 300 110) ---
        $dailyTotals = [];
for ($i = 6; $i >= 0; $i--) {
    $day = Carbon::now()->subDays($i)->startOfDay();
    $dayEnd = (clone $day)->endOfDay();

    $t = Topic::where('UserID', $userId)->whereBetween('created_at', [$day, $dayEnd])->count();
    $p = Post::where('UserID', $userId)->whereBetween('DatePosted', [$day, $dayEnd])->count();
    $r = Reply::where('UserID', $userId)->whereBetween('created_at', [$day, $dayEnd])->count();
    $q = QuizScore::where('UserID', $userId)->whereBetween('DateRecorded', [$day, $dayEnd])->count();
    $g = \App\Models\GroupMember::where('UserID', $userId)->whereBetween('JoinedAt', [$day, $dayEnd])->count();

    $dailyTotals[] = $t + $p + $r + $q + $g;
}

        $maxTotal = max(1, max($dailyTotals));
        $yTop = 15; $yBase = 105; $xStart = 10; $xEnd = 280;
        $stepX = (count($dailyTotals) > 1) ? ($xEnd - $xStart) / (count($dailyTotals) - 1) : 0;

        $points = [];
        foreach ($dailyTotals as $i => $total) {
            $x = round($xStart + ($i * $stepX), 1);
            $y = round($yBase - (($total / $maxTotal) * ($yBase - $yTop)), 1);
            $points[] = "{$x},{$y}";
        }
        $activityChartPoints = implode(' ', $points);

        $unreadNotifications = \App\Models\Notification::where('UserID', $user->UserID)
        ->where('Status', 'Unread')
            ->count();
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
}