<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $trendingTopics = DB::table('topics')
            ->leftJoin('posts', 'topics.TopicID', '=', 'posts.TopicID')
            ->select('topics.TopicID', 'topics.Title', 'topics.Status',
                     DB::raw('COUNT(posts.PostID) as post_count'))
            ->where('topics.Status', 'open')
            ->groupBy('topics.TopicID', 'topics.Title', 'topics.Status')
            ->orderByDesc('post_count')
            ->limit(5)
            ->get();

        $suggestedGroups = DB::table('groups')
            ->leftJoin('group_admins', function ($join) use ($user) {
                $join->on('groups.GroupID', '=', 'group_admins.GroupID')
                     ->where('group_admins.UserID', '=', $user->UserID);
            })
            ->select('groups.GroupID', 'groups.GroupName', 'groups.Description')
            ->whereNull('group_admins.UserID')
            ->limit(4)
            ->get();

        $activePosts = DB::table('posts')
            ->leftJoin('topics', 'posts.TopicID', '=', 'topics.TopicID')
            ->select('posts.PostID', 'posts.content', 'topics.Title as TopicTitle', 'posts.DatePosted')
            ->orderByDesc('posts.DatePosted')
            ->limit(4)
            ->get();

        $upcomingQuizzes = DB::table('quizzes')
            ->select('QuizID', 'Title', 'StartTime', 'Duration')
            ->where('StartTime', '>=', now())
            ->orderBy('StartTime')
            ->limit(3)
            ->get();

        return response()->json([
            'trendingTopics' => $trendingTopics,
            'suggestedGroups' => $suggestedGroups,
            'activePosts' => $activePosts,
            'upcomingQuizzes' => $upcomingQuizzes,
        ]);
    }
}