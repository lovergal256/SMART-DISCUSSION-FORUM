<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\QuizScore;
use App\Models\Reply;
use App\Models\Topic;
use Illuminate\Http\Request;

class PerformanceApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->UserID;

        $topicsCreated = Topic::where('UserID', $userId)->count();
        $postsCreated  = Post::where('UserID', $userId)->count();
        $repliesMade   = Reply::where('UserID', $userId)->count();

        $rawParticipation = ($topicsCreated * 3) + ($postsCreated * 1) + ($repliesMade * 0.2);
        $participationScore = min(50, round($rawParticipation));

        $quizzesAttempted = QuizScore::where('UserID', $userId)->count();
        $averageQuizScore = round(QuizScore::where('UserID', $userId)->avg('Score') ?? 0);
        $highestScore     = round(QuizScore::where('UserID', $userId)->max('Score') ?? 0);

        $quizMarks = round(($averageQuizScore / 100) * 50);

        $overallMarks = $participationScore + $quizMarks;

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

        $recentQuizzes = QuizScore::with('quiz')
            ->where('UserID', $userId)
            ->orderByDesc('DateRecorded')
            ->limit(5)
            ->get()
            ->map(function ($qs) {
                return [
                    'QuizID' => $qs->QuizID,
                    'QuizTitle' => optional($qs->quiz)->Title ?? 'Unknown Quiz',
                    'Score' => (float) $qs->Score,
                    'DateRecorded' => optional($qs->DateRecorded)?->toIso8601String() ?? (string) $qs->DateRecorded,
                ];
            });

        return response()->json([
            'topicsCreated' => $topicsCreated,
            'postsCreated' => $postsCreated,
            'repliesMade' => $repliesMade,
            'participationScore' => $participationScore,
            'quizzesAttempted' => $quizzesAttempted,
            'averageQuizScore' => $averageQuizScore,
            'highestScore' => $highestScore,
            'quizMarks' => $quizMarks,
            'overallMarks' => $overallMarks,
            'grade' => $grade,
            'status' => $status,
            'recentQuizzes' => $recentQuizzes,
        ]);
    }
}