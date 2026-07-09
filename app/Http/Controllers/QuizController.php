<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Answer;
use App\Models\Group;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? $user->RoleID ?? ''));

        $quizzes = Quiz::with('group')
            ->withCount('questions')
            ->orderByDesc('StartTime')
            ->get();

        $attemptedQuizIds = Attempt::query()
            ->where('UserID', $user->UserID)
            ->pluck('QuizID')
            ->all();

        return view('quizzes.index', [
            'quizzes' => $quizzes,
            'attemptedQuizIds' => $attemptedQuizIds,
            'role' => $role,
            'now' => Carbon::now(),
        ]);
    }

    public function create()
    {
        $groups = Group::all();

        return view('quizzes.create', [
            'groups' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'group_id' => 'required|exists:groups,GroupID',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1|max:300',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string',
            'questions.*.option_b' => 'required|string',
            'questions.*.option_c' => 'nullable|string',
            'questions.*.option_d' => 'nullable|string',
            'questions.*.correct_option' => 'required|in:A,B,C,D',
            'questions.*.marks' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($validated, $user): void {
            $quiz = Quiz::query()->create([
                'Title' => $validated['title'],
                'StartTime' => Carbon::parse($validated['start_time']),
                'Duration' => (int) $validated['duration'],
                'GroupID' => $validated['group_id'],
                'LecturerID' => $user->UserID,
            ]);

            foreach ($validated['questions'] as $questionData) {
                Question::query()->create([
                    'QuizID' => $quiz->QuizID,
                    'QuestionText' => $questionData['question_text'],
                    'OptionA' => $questionData['option_a'],
                    'OptionB' => $questionData['option_b'],
                    'OptionC' => $questionData['option_c'] ?? null,
                    'OptionD' => $questionData['option_d'] ?? null,
                    'CorrectOption' => $questionData['correct_option'],
                    'Marks' => (int) $questionData['marks'],
                ]);
            }
        });

        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz created successfully.');
    }

    public function show(Quiz $quiz)
    {
        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? $user->RoleID ?? ''));
        $now = Carbon::now();

        $startTime = Carbon::parse($quiz->StartTime);
        $endTime = (clone $startTime)->addMinutes((int) $quiz->Duration);

        $isAttempted = Attempt::query()
            ->where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->exists();

        $attempt = Attempt::query()
            ->where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->first();

        $isActive = $now->between($startTime, $endTime);

        $questions = Question::where('QuizID', $quiz->QuizID)->get();

        $attemptCount = Attempt::where('QuizID', $quiz->QuizID)->count();
        $averageScore = Attempt::where('QuizID', $quiz->QuizID)->avg('Score');

        return view('quizzes.show', [
            'quiz' => $quiz,
            'questions' => $questions,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'isAttempted' => $isAttempted,
            'isActive' => $isActive,
            'attempt' => $attempt,
            'attemptCount' => $attemptCount,
            'averageScore' => $averageScore,
            'role' => $role,
            'now' => $now,
        ]);
    }

    public function storeAttempt(Request $request, Quiz $quiz)
    {
        $user = Auth::user();

        $request->validate([
            'answers' => 'required|array',
        ]);

        $now = Carbon::now();
        $startTime = Carbon::parse($quiz->StartTime);
        $endTime = (clone $startTime)->addMinutes((int) $quiz->Duration);

        if (! $now->between($startTime, $endTime)) {
            return redirect()->route('quizzes.show', $quiz->QuizID)
                ->withErrors(['quiz' => 'This quiz is not currently active.']);
        }

        $alreadyAttempted = Attempt::query()
            ->where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->exists();

        if ($alreadyAttempted) {
            return redirect()->route('quizzes.show', $quiz->QuizID)
                ->withErrors(['quiz' => 'You have already attempted this quiz.']);
        }

        $questions = Question::where('QuizID', $quiz->QuizID)->get();
        $answers = $request->input('answers', []);

        DB::transaction(function () use ($user, $quiz, $questions, $answers, $now): void {
            $attempt = Attempt::query()->create([
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

                Answer::query()->create([
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

            QuizScore::query()->create([
                'UserID' => $user->UserID,
                'QuizID' => $quiz->QuizID,
                'Score' => $scorePercentage,
                'DateRecorded' => $now,
            ]);
        });

        return redirect()->route('quizzes.show', $quiz->QuizID)
            ->with('success', 'Quiz submitted successfully.');
    }

    private function isLecturer(User $user): bool
    {
        return $user->role_name === 'lecturer';
    }

    private function isStudent(User $user): bool
    {
        return $user->role_name === 'student';
    }
}
