<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Trending Topics - most posts
        $trendingTopics = DB::table('topics')
            ->leftJoin('posts', 'topics.TopicID', '=', 'posts.TopicID')
            ->select('topics.TopicID', 'topics.Title', 'topics.Status',
                     DB::raw('COUNT(posts.PostID) as post_count'))
            ->where('topics.Status', 'open')
            ->groupBy('topics.TopicID', 'topics.Title', 'topics.Status')
            ->orderByDesc('post_count')
            ->limit(5)
            ->get();

        // 2. Suggested Groups - groups user is NOT in
        $suggestedGroups = DB::table('groups')
            ->leftJoin('group_admins', function($join) use ($user) {
                $join->on('groups.GroupID', '=', 'group_admins.GroupID')
                     ->where('group_admins.UserID', '=', $user->UserID);
            })
            ->select('groups.GroupID', DB::raw('`groups`.`Group Name` as GroupName'), 'groups.Description')
            ->whereNull('group_admins.UserID')
            ->limit(4)
            ->get();

        // 3. Active Posts - most recent
$activePosts = DB::table('posts')
    ->leftJoin('topics', 'posts.TopicID', '=', 'topics.TopicID')
    ->select('posts.PostID', 'posts.content',
             'topics.Title as TopicTitle',
             'posts.DatePosted')
    ->orderByDesc('posts.DatePosted')
    ->limit(4)
    ->get();

        // 4. Upcoming Quizzes
        $upcomingQuizzes = DB::table('quizzes')
            ->select('QuizID', 'Title', 'StartTime', 'Duration')
            ->where('StartTime', '>=', now())
            ->orderBy('StartTime')
            ->limit(3)
            ->get();

        return view('recommendations.index', compact(
            'trendingTopics',
            'suggestedGroups',
            'activePosts',
            'upcomingQuizzes'
        ));
    }
}