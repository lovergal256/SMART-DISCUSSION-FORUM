<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\QuizScore;
use App\Models\Reply;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->UserID;

        // --- Discussion Participation ---
        $topicsCreated = Topic::where('UserID', $userId)->count();
        $postsCreated  = Post::where('UserID', $userId)->count();
        $repliesMade   = Reply::where('UserID', $userId)->count();

        // Weighted score: topics worth most (initiative), replies worth least
        $rawParticipation = ($topicsCreated * 3) + ($postsCreated * 1) + ($repliesMade * 0.2);
        $participationScore = min(50, round($rawParticipation));

        // --- Quiz Performance ---
        $quizzesAttempted = QuizScore::where('UserID', $userId)->count();
        $averageQuizScore = round(QuizScore::where('UserID', $userId)->avg('Score') ?? 0);
        $highestScore     = round(QuizScore::where('UserID', $userId)->max('Score') ?? 0);

        $quizMarks = round(($averageQuizScore / 100) * 50);

        // --- Overall ---
        $overallMarks = $participationScore + $quizMarks; // out of 100

        $grade = match(true) {
            $overallMarks >= 90 => 'A+',
            $overallMarks >= 80 => 'A',
            $overallMarks >= 70 => 'B',
            $overallMarks >= 60 => 'C',
            default => 'D',
        };

        $status = match(true) {
            $overallMarks >= 80 => 'Excellent',
            $overallMarks >= 60 => 'Good',
            $overallMarks >= 40 => 'Fair',
            default => 'Needs Improvement',
        };

        // --- Recent Quiz Results ---
        $recentQuizzes = QuizScore::with('quiz')
            ->where('UserID', $userId)
            ->orderByDesc('DateRecorded')
            ->limit(5)
            ->get();

        return view('performance.index', compact(
            'topicsCreated', 'postsCreated', 'repliesMade', 'participationScore',
            'quizzesAttempted', 'averageQuizScore', 'highestScore', 'quizMarks',
            'overallMarks', 'grade', 'status', 'recentQuizzes'
        ));
    }
}