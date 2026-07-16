<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Post;
use App\Models\Reply;
use App\Models\QuizScore;
use App\Models\GroupMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->UserID;

        // --- Top Stat Cards (all-time totals) ---
        $discussionsCount = Topic::where('UserID', $userId)->count();
        $postsCount       = Post::where('UserID', $userId)->count();
        $repliesCount     = Reply::where('UserID', $userId)->count();
        $quizzesCount     = QuizScore::where('UserID', $userId)->count();
        $groupsCount      = GroupMember::where('UserID', $userId)->count();
        $warningsCount    = $user->warnings()->count();
        $lastActive       = $user->LastActiveDate ? Carbon::parse($user->LastActiveDate) : null;

        // --- Weekly Activity (rolling last 7 days) ---
        $weeklyData = [];
        $weeklyTotal = 0; // for badge

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->startOfDay();
            $dayEnd = (clone $day)->endOfDay();

            $topicsDay  = Topic::where('UserID', $userId)->whereBetween('created_at', [$day, $dayEnd])->count();
            $postsDay   = Post::where('UserID', $userId)->whereBetween('DatePosted', [$day, $dayEnd])->count();
            $repliesDay = Reply::where('UserID', $userId)->whereBetween('created_at', [$day, $dayEnd])->count();
            $quizzesDay = QuizScore::where('UserID', $userId)->whereBetween('DateRecorded', [$day, $dayEnd])->count();
            $groupsDay  = GroupMember::where('UserID', $userId)->whereBetween('JoinedAt', [$day, $dayEnd])->count();

            $totalDay = $topicsDay + $postsDay + $repliesDay + $quizzesDay + $groupsDay;

            $weeklyData[] = [
                'label' => $day->format('D'), // Mon, Tue, ...
                'total' => $totalDay,
            ];

            // Weighted weekly score for badge calculation
            $weeklyTotal += ($topicsDay * 3) + ($postsDay * 1) + ($repliesDay * 0.2) + ($quizzesDay * 2) + ($groupsDay * 1);
        }

        $weeklyMax = max(1, collect($weeklyData)->max('total')); // avoid divide-by-zero

        // --- Activity Level Badge ---
        $activityLevel = match(true) {
            $weeklyTotal >= 10 => ['label' => 'Highly Active', 'class' => 'badge-high'],
            $weeklyTotal >= 3  => ['label' => 'Moderate', 'class' => 'badge-moderate'],
            default            => ['label' => 'Inactive', 'class' => 'badge-low'],
        };

        // --- Activity Distribution (all-time totals as percentages) ---
        $distributionRaw = [
            'Discussions' => $discussionsCount,
            'Posts'       => $postsCount,
            'Replies'     => $repliesCount,
            'Quizzes'     => $quizzesCount,
            'Groups'      => $groupsCount,
        ];
        $distributionTotal = max(1, array_sum($distributionRaw));
        $distribution = collect($distributionRaw)->map(function ($count) use ($distributionTotal) {
            return [
                'count' => $count,
                'percent' => round(($count / $distributionTotal) * 100),
            ];
        });

        // --- Recent Activities (merged feed) ---
        $topicsFeed = DB::table('topics')
            ->where('UserID', $userId)
            ->select('Title as detail', DB::raw("'topic' as type"), 'created_at as activity_date')
            ->get();

        $postsFeed = DB::table('posts')
            ->join('topics', 'posts.TopicID', '=', 'topics.TopicID')
            ->where('posts.UserID', $userId)
            ->select('topics.Title as detail', DB::raw("'post' as type"), 'posts.DatePosted as activity_date')
            ->get();

        $repliesFeed = DB::table('replies')
            ->join('posts', 'replies.PostID', '=', 'posts.PostID')
            ->join('topics', 'posts.TopicID', '=', 'topics.TopicID')
            ->where('replies.UserID', $userId)
            ->select('topics.Title as detail', DB::raw("'reply' as type"), 'replies.created_at as activity_date')
            ->get();

        $quizzesFeed = DB::table('quiz_scores')
            ->join('quizzes', 'quiz_scores.QuizID', '=', 'quizzes.QuizID')
            ->where('quiz_scores.UserID', $userId)
            ->select('quizzes.Title as detail', DB::raw("'quiz' as type"), 'quiz_scores.DateRecorded as activity_date')
            ->get();

        $groupsFeed = DB::table('group_members')
            ->join('groups', 'group_members.GroupID', '=', 'groups.GroupID')
            ->where('group_members.UserID', $userId)
            ->select('groups.GroupName as detail', DB::raw("'group' as type"), 'group_members.JoinedAt as activity_date')
            ->get();

        $recentActivities = $topicsFeed
            ->concat($postsFeed)
            ->concat($repliesFeed)
            ->concat($quizzesFeed)
            ->concat($groupsFeed)
            ->sortByDesc('activity_date')
            ->take(10)
            ->values()
            ->map(function ($item) {
                $date = Carbon::parse($item->activity_date);
                $item->date_label = $this->relativeDayLabel($date);
                $item->time_label = $date->format('g:i A');
                return $item;
            });

        return view('activity.index', compact(
            'discussionsCount', 'postsCount', 'repliesCount', 'quizzesCount',
            'groupsCount', 'warningsCount', 'lastActive',
            'weeklyData', 'weeklyMax', 'activityLevel',
            'distribution', 'recentActivities'
        ));
    }

    private function relativeDayLabel(Carbon $date): string
{
    $days = (int) floor($date->diffInDays(Carbon::now()));

    if ($date->isToday()) return 'Today';
    if ($date->isYesterday()) return 'Yesterday';
    if ($days < 7) return $days . ' Day' . ($days === 1 ? '' : 's') . ' Ago';
    if ($days < 14) return 'Last Week';
    return $date->format('M d, Y');
}
}