<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CollaborativeFilteringService;

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
$cf = new CollaborativeFilteringService();
$recommendedGroupIds = $cf->recommendGroups($user->UserID, 4);

if (!empty($recommendedGroupIds)) {
    $suggestedGroups = DB::table('groups')
        ->whereIn('GroupID', $recommendedGroupIds)
        ->get()
        ->sortBy(fn ($g) => array_search($g->GroupID, $recommendedGroupIds))
        ->values();
} else {
    $suggestedGroups = DB::table('groups')
        ->leftJoin('group_members', function($join) use ($user) {
            $join->on('groups.GroupID', '=', 'group_members.GroupID')
                 ->where('group_members.UserID', '=', $user->UserID);
        })
        ->select('groups.GroupID', 'groups.GroupName', 'groups.Description')
        ->whereNull('group_members.UserID')
        ->limit(4)
        ->get();
}

        // 3. Active Posts - most recent
$activePosts = DB::table('posts')
    ->leftJoin('topics', 'posts.TopicID', '=', 'topics.TopicID')
    ->leftJoin('replies', 'posts.PostID', '=', 'replies.PostID')
    ->select('posts.PostID', 'posts.content',
             'topics.Title as TopicTitle',
             'posts.DatePosted',
             DB::raw('COUNT(replies.ReplyID) as reply_count'))
    ->groupBy('posts.PostID', 'posts.content', 'topics.Title', 'posts.DatePosted')
    ->orderByDesc('reply_count')
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