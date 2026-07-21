<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizScore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $attemptedQuizIds = Attempt::where('UserID', $user->UserID)->pluck('QuizID')->all();

        $quizzes = Quiz::with('group')
            ->withCount('questions')
            ->orderByDesc('StartTime')
            ->get()
            ->map(function ($quiz) use ($now, $attemptedQuizIds) {
                $start = Carbon::parse($quiz->StartTime);
                $end = (clone $start)->addMinutes((int) $quiz->Duration);

                $status = 'upcoming';
                if ($now->between($start, $end)) {
                    $status = 'active';
                } elseif ($now->greaterThan($end)) {
                    $status = 'ended';
                }

                return [
                    'QuizID' => $quiz->QuizID,
                    'Title' => $quiz->Title,
                    'GroupName' => optional($quiz->group)->GroupName ?? '',
                    'StartTime' => $start->toIso8601String(),
                    'Duration' => (int) $quiz->Duration,
                    'QuestionsCount' => $quiz->questions_count,
                    'Status' => $status,
                    'IsAttempted' => in_array($quiz->QuizID, $attemptedQuizIds),
                ];
            });

        return response()->json($quizzes);
    }

    public function show(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $now = Carbon::now();

        $start = Carbon::parse($quiz->StartTime);
        $end = (clone $start)->addMinutes((int) $quiz->Duration);
        $isActive = $now->between($start, $end);

        $attempt = Attempt::where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->first();

        if ($attempt) {
            $answers = Answer::where('AttemptID', $attempt->AttemptID)->get();
            $questions = Question::where('QuizID', $quiz->QuizID)->get();

            return response()->json([
                'QuizID' => $quiz->QuizID,
                'Title' => $quiz->Title,
                'Attempted' => true,
                'Score' => $attempt->Score,
                'ResultsReleased' => (bool) $quiz->ResultsReleased,
                'Questions' => $quiz->ResultsReleased
                    ? $questions->map(function ($q) use ($answers) {
                        $ans = $answers->firstWhere('QuestionID', $q->QuestionID);
                        return [
                            'QuestionID' => $q->QuestionID,
                            'QuestionText' => $q->QuestionText,
                            'OptionA' => $q->OptionA,
                            'OptionB' => $q->OptionB,
                            'OptionC' => $q->OptionC,
                            'OptionD' => $q->OptionD,
                            'CorrectOption' => $q->CorrectOption,
                            'SelectedOption' => optional($ans)->SelectedOption,
                            'IsCorrect' => (bool) optional($ans)->IsCorrect,
                            'Marks' => (int) $q->Marks,
                        ];
                    })
                    : [],
            ]);
        }

        if (! $isActive) {
            return response()->json([
                'QuizID' => $quiz->QuizID,
                'Title' => $quiz->Title,
                'Attempted' => false,
                'Active' => false,
                'StartTime' => $start->toIso8601String(),
                'EndTime' => $end->toIso8601String(),
            ]);
        }

        $questions = Question::where('QuizID', $quiz->QuizID)->get()->map(function ($q) {
            return [
                'QuestionID' => $q->QuestionID,
                'QuestionText' => $q->QuestionText,
                'OptionA' => $q->OptionA,
                'OptionB' => $q->OptionB,
                'OptionC' => $q->OptionC,
                'OptionD' => $q->OptionD,
                'Marks' => (int) $q->Marks,
            ];
        });

        return response()->json([
            'QuizID' => $quiz->QuizID,
            'Title' => $quiz->Title,
            'Attempted' => false,
            'Active' => true,
            'EndTime' => $end->toIso8601String(),
            'Questions' => $questions,
        ]);
    }

    public function storeAttempt(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        $request->validate([
            'answers' => 'required|array',
        ]);

        $now = Carbon::now();
        $start = Carbon::parse($quiz->StartTime);
        $end = (clone $start)->addMinutes((int) $quiz->Duration);

        if (! $now->between($start, $end)) {
            return response()->json(['message' => 'This quiz is not currently active.'], 422);
        }

        $alreadyAttempted = Attempt::where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->exists();

        if ($alreadyAttempted) {
            return response()->json(['message' => 'You have already attempted this quiz.'], 422);
        }

        $questions = Question::where('QuizID', $quiz->QuizID)->get();
        $answers = $request->input('answers', []);

        $scorePercentage = DB::transaction(function () use ($user, $quiz, $questions, $answers, $now) {
            $attempt = Attempt::create([
                'UserID' => $user->UserID,
                'QuizID' => $quiz->QuizID,
                'StartTime' => $now,
                'EndTime' => $now,
                'Status' => 'completed',
                'Score' => 0,
                'AttemptDate' => $now,
            ]);

            $totalMarks = 0;
            $awardedMarks = 0;

            foreach ($questions as $question) {
                $selectedOption = $answers[$question->QuestionID] ?? null;
                $isCorrect = $selectedOption === $question->CorrectOption;
                $marksAwarded = $isCorrect ? (int) $question->Marks : 0;

                $totalMarks += (int) $question->Marks;
                $awardedMarks += $marksAwarded;

                Answer::create([
                    'AttemptID' => $attempt->AttemptID,
                    'QuestionID' => $question->QuestionID,
                    'SelectedOption' => $selectedOption,
                    'IsCorrect' => $isCorrect,
                    'MarksAwarded' => $marksAwarded,
                    'DateAnswered' => $now,
                ]);
            }

            $scorePercentage = $totalMarks > 0 ? round(($awardedMarks / $totalMarks) * 100, 2) : 0.0;
            $attempt->update(['Score' => $scorePercentage]);

            QuizScore::create([
                'UserID' => $user->UserID,
                'QuizID' => $quiz->QuizID,
                'Score' => $scorePercentage,
                'DateRecorded' => $now,
            ]);

            return $scorePercentage;
        });

        return response()->json([
            'message' => 'Quiz submitted successfully.',
            'Score' => $scorePercentage,
        ]);
    }
}