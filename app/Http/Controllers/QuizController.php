<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Choice;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(401);
        }

        if ($this->isLecturer($user)) {
            $quizzes = Quiz::query()
                ->withCount('attempts')
                ->withCount('questions')
                ->where('lecturer_id', $user->id)
                ->orderByDesc('available_from')
                ->get();

            return view('quizzes.index', [
                'role' => 'lecturer',
                'quizzes' => $quizzes,
            ]);
        }

        if (! $this->isStudent($user)) {
            abort(403, 'Only students and lecturers can access quizzes.');
        }

        $now = now();
        $quizzes = Quiz::query()
            ->withCount('questions')
            ->where('is_published', true)
            ->whereNotNull('available_from')
            ->where(function ($query) use ($now): void {
                $query->where('available_until', '>=', $now)
                    ->orWhere(function ($query) use ($now): void {
                        $query->whereNull('available_until')
                            ->where('available_from', '<=', $now);
                    });
            })
            ->orderBy('available_from')
            ->get()
            ->map(function (Quiz $quiz): Quiz {
                $quiz->computed_end_time = $this->quizEndTime($quiz);

                return $quiz;
            });

        $attemptedQuizIds = Attempt::query()
            ->where('user_id', $user->id)
            ->pluck('quiz_id')
            ->all();

        return view('quizzes.index', [
            'role' => 'student',
            'quizzes' => $quizzes,
            'attemptedQuizIds' => $attemptedQuizIds,
            'now' => $now,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $this->isLecturer($user)) {
            abort(403, 'Only lecturers can create quizzes.');
        }

        $groups = DB::table('groups')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('quizzes.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $this->isLecturer($user)) {
            abort(403, 'Only lecturers can create quizzes.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'group_id' => 'nullable|integer|exists:groups,id',
            'description' => 'nullable|string',
            'available_from' => 'required|date',
            'duration' => 'required|integer|min:1|max:300',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string|max:255',
            'questions.*.option_b' => 'required|string|max:255',
            'questions.*.option_c' => 'required|string|max:255',
            'questions.*.option_d' => 'required|string|max:255',
            'questions.*.correct_option' => 'required|in:A,B,C,D',
            'questions.*.marks' => 'required|integer|min:1|max:100',
        ]);

        DB::transaction(function () use ($validated, $user): void {
            $availableFrom = Carbon::parse($validated['available_from']);
            $availableUntil = (clone $availableFrom)->addMinutes((int) $validated['duration']);

            $quiz = Quiz::query()->create([
                'lecturer_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'available_from' => $availableFrom,
                'available_until' => $availableUntil,
                'duration_minutes' => (int) $validated['duration'],
                'is_published' => true,
                'results_released' => true,
            ]);

            foreach ($validated['questions'] as $index => $questionData) {
                $question = Question::query()->create([
                    'quiz_id' => $quiz->id,
                    'type' => 'multiple_choice',
                    'question_text' => $questionData['question_text'],
                    'points' => (int) $questionData['marks'],
                    'order' => $index + 1,
                ]);

                $choices = [
                    'A' => $questionData['option_a'],
                    'B' => $questionData['option_b'],
                    'C' => $questionData['option_c'],
                    'D' => $questionData['option_d'],
                ];

                foreach ($choices as $option => $choiceText) {
                    Choice::query()->create([
                        'question_id' => $question->id,
                        'choice_text' => $choiceText,
                        'is_correct' => $option === $questionData['correct_option'],
                    ]);
                }
            }
        });

        return redirect()->route('quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function show(int $id)
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(401);
        }

        $quiz = Quiz::query()
            ->with(['questions.choices'])
            ->findOrFail($id);

        $startTime = Carbon::parse($quiz->available_from);
        $endTime = $this->quizEndTime($quiz);
        $isActiveWindow = now()->betweenIncluded($startTime, $endTime);

        if ($this->isLecturer($user)) {
            if ((int) $quiz->lecturer_id !== (int) $user->id) {
                abort(403, 'You can only view quizzes you created.');
            }

            $attemptCount = Attempt::query()
                ->where('quiz_id', $quiz->id)
                ->count();

            $averageScore = Attempt::query()
                ->where('quiz_id', $quiz->id)
                ->avg('score');

            return view('quizzes.show', [
                'role' => 'lecturer',
                'quiz' => $quiz,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'attemptCount' => $attemptCount,
                'averageScore' => $averageScore,
            ]);
        }

        if (! $this->isStudent($user)) {
            abort(403);
        }

        $attempt = Attempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->first();

        return view('quizzes.show', [
            'role' => 'student',
            'quiz' => $quiz,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'isActiveWindow' => $isActiveWindow,
            'attempt' => $attempt,
        ]);
    }

    public function submitAttempt(Request $request, int $id)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $this->isStudent($user)) {
            abort(403, 'Only students can attempt quizzes.');
        }

        $quiz = Quiz::query()->with(['questions.choices'])->findOrFail($id);

        $startTime = Carbon::parse($quiz->available_from);
        $endTime = $this->quizEndTime($quiz);
        if (! now()->betweenIncluded($startTime, $endTime)) {
            return redirect()->route('quizzes.show', $quiz->id)
                ->withErrors(['quiz' => 'This quiz is only available during the scheduled time window.']);
        }

        $hasAttempted = Attempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($hasAttempted) {
            return redirect()->route('quizzes.show', $quiz->id)
                ->withErrors(['quiz' => 'You can only attempt this quiz once.']);
        }

        $request->validate([
            'answers' => 'required|array',
        ]);

        $questions = $quiz->questions;
        $answers = $request->input('answers', []);
        $totalMarks = 0;
        $awardedMarks = 0;

        foreach ($questions as $question) {
            $selectedChoiceId = (int) ($answers[$question->id] ?? 0);
            $selectedChoice = $question->choices->firstWhere('id', $selectedChoiceId);

            if (! $selectedChoice instanceof Choice) {
                return redirect()->route('quizzes.show', $quiz->id)
                    ->withErrors(['quiz' => 'Please answer all questions before submitting.']);
            }

            $questionPoints = (int) $question->points;
            $totalMarks += $questionPoints;
            if ($selectedChoice->is_correct) {
                $awardedMarks += $questionPoints;
            }
        }

        $scorePercentage = $totalMarks > 0 ? round(($awardedMarks / $totalMarks) * 100, 2) : 0.0;
        $attemptTime = now();

        DB::transaction(function () use ($user, $quiz, $questions, $answers, $attemptTime, $scorePercentage): void {
            $attempt = Attempt::query()->create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'started_at' => $attemptTime,
                'submitted_at' => $attemptTime,
                'total_points' => $scorePercentage,
                'score' => $scorePercentage,
            ]);

            foreach ($questions as $question) {
                $selectedChoiceId = (int) $answers[$question->id];
                $selectedChoice = $question->choices->firstWhere('id', $selectedChoiceId);
                $isCorrect = $selectedChoice instanceof Choice && $selectedChoice->is_correct;
                $pointsAwarded = $isCorrect ? (int) $question->points : 0;

                Answer::query()->create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'choice_id' => $selectedChoice?->id,
                    'answer_text' => null,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                ]);
            }

            if (! is_null($quiz->results_released) && ! $quiz->results_released) {
                $attempt->update([
                    'score' => 0,
                    'total_points' => 0,
                ]);
            }
        });

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', 'Quiz submitted successfully.');
    }

    private function isLecturer(User $user): bool
    {
        return strtolower((string) $user->role) === 'lecturer';
    }

    private function isStudent(User $user): bool
    {
        return strtolower((string) $user->role) === 'student';
    }

    private function quizEndTime(Quiz $quiz): Carbon
    {
        if (! is_null($quiz->available_until)) {
            return Carbon::parse($quiz->available_until);
        }

        return Carbon::parse($quiz->available_from)->addMinutes((int) $quiz->duration_minutes);
    }
}
