<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Group;
use App\Models\Lecturer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizScore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizApiController extends Controller
{
    /**
     * List quizzes for a group the user belongs to.
     * Used by both roles on the desktop app's group screen.
     */
    public function index($groupId)
    {
        $user = Auth::user();

        $group = Group::whereHas('members', function ($query) use ($user) {
            $query->where('group_members.UserID', $user->UserID)
                ->where('group_members.Status', 'approved');
        })->find($groupId);

        if (!$group) {
            return response()->json(['message' => 'You are not a member of that group.'], 403);
        }

        $attemptedQuizIds = Attempt::where('UserID', $user->UserID)->pluck('QuizID')->all();

        $quizzes = Quiz::where('GroupID', $group->GroupID)
            ->latest('StartTime')
            ->get()
            ->map(function ($quiz) use ($attemptedQuizIds) {
                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'duration' => $quiz->Duration,
                    'start_time' => Carbon::parse($quiz->StartTime)->toIso8601String(),
                    'attempted' => in_array($quiz->QuizID, $attemptedQuizIds, true),
                ];
            });

        return response()->json($quizzes);
    }

    /**
     * List quizzes across every group the user belongs to.
     * Powers the desktop app's standalone "My Quizzes" screen.
     */
    public function indexAll()
    {
        $user = Auth::user();

        $attemptedQuizIds = Attempt::where('UserID', $user->UserID)->pluck('QuizID')->all();

        $groupIds = Group::whereHas('members', function ($query) use ($user) {
            $query->where('group_members.UserID', $user->UserID)
                ->where('group_members.Status', 'approved');
        })->pluck('GroupID');

        $quizzes = Quiz::whereIn('GroupID', $groupIds)
            ->with('group')
            ->latest('StartTime')
            ->get()
            ->map(function ($quiz) use ($attemptedQuizIds) {
                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'duration' => $quiz->Duration,
                    'start_time' => Carbon::parse($quiz->StartTime)->toIso8601String(),
                    'attempted' => in_array($quiz->QuizID, $attemptedQuizIds, true),
                    'group_id' => $quiz->GroupID,
                    'group_name' => optional($quiz->group)->GroupName ?? 'Group',
                ];
            });

        return response()->json($quizzes);
    }

    /**
     * Quiz detail. Includes questions only when the student can actually
     * attempt it right now (active window, not already attempted).
     */
    public function show($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);

        $isMember = Group::where('GroupID', $quiz->GroupID)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('group_members.UserID', $user->UserID)
                    ->where('group_members.Status', 'approved');
            })->exists();

        if (!$isMember) {
            return response()->json(['message' => 'You are not a member of this quiz\'s group.'], 403);
        }

        $now = Carbon::now();
        $startTime = Carbon::parse($quiz->StartTime);
        $endTime = (clone $startTime)->addMinutes((int) $quiz->Duration);
        $isActive = $now->between($startTime, $endTime);

        $attempt = Attempt::where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->first();
        $isAttempted = $attempt !== null;

        $payload = [
            'id' => $quiz->QuizID,
            'title' => $quiz->Title,
            'group_name' => optional($quiz->group)->GroupName ?? 'Group',
            'duration' => $quiz->Duration,
            'start_time' => $startTime->toIso8601String(),
            'end_time' => $endTime->toIso8601String(),
            'server_time' => $now->toIso8601String(),
            'is_active' => $isActive,
            'is_attempted' => $isAttempted,
            'results_released' => (bool) $quiz->ResultsReleased,
            'score' => ($isAttempted && $quiz->ResultsReleased) ? (float) $attempt->Score : null,
        ];

        // Only send questions when the student can actually attempt right now —
        // mirrors the web view's @elseif(! $isActive) / @if($isAttempted) gating.
        if (!$isAttempted && $isActive) {
            $payload['questions'] = Question::where('QuizID', $quiz->QuizID)
                ->get()
                ->map(function ($q) {
                    return [
                        'id' => $q->QuestionID,
                        'text' => $q->QuestionText,
                        'option_a' => $q->OptionA,
                        'option_b' => $q->OptionB,
                        'option_c' => $q->OptionC,
                        'option_d' => $q->OptionD,
                        'marks' => $q->Marks,
                    ];
                });
        } else {
            $payload['questions'] = [];
        }

        return response()->json($payload);
    }

    /**
     * Per-question answer review for a completed, results-released attempt.
     * Restores the answer-review detail the student result screen needs
     * (correct option, what the student picked, whether it was right) —
     * show() intentionally stays lightweight and doesn't include this.
     */
    public function results($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);

        $attempt = Attempt::where('UserID', $user->UserID)
            ->where('QuizID', $quiz->QuizID)
            ->first();

        if (!$attempt) {
            return response()->json(['message' => 'You have not attempted this quiz.'], 404);
        }

        if (!$quiz->ResultsReleased) {
            return response()->json(['message' => 'Results have not been released yet.'], 403);
        }

        $answers = Answer::where('AttemptID', $attempt->AttemptID)->get();
        $questions = Question::where('QuizID', $quiz->QuizID)->get();

        $review = $questions->map(function ($q) use ($answers) {
            $ans = $answers->firstWhere('QuestionID', $q->QuestionID);

            return [
                'id' => $q->QuestionID,
                'text' => $q->QuestionText,
                'option_a' => $q->OptionA,
                'option_b' => $q->OptionB,
                'option_c' => $q->OptionC,
                'option_d' => $q->OptionD,
                'correct_option' => $q->CorrectOption,
                'selected_option' => optional($ans)->SelectedOption,
                'is_correct' => (bool) optional($ans)->IsCorrect,
                'marks' => (int) $q->Marks,
            ];
        });

        return response()->json([
            'id' => $quiz->QuizID,
            'title' => $quiz->Title,
            'score' => (float) $attempt->Score,
            'questions' => $review,
        ]);
    }

    /**
     * Submit an attempt. Mirrors the web QuizController@storeAttempt exactly,
     * including allowing an empty/partial answer set for auto-submit on timeout.
     */
    public function storeAttempt(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);

        $request->validate([
            'answers' => 'nullable|array',
        ]);

        $now = Carbon::now();
        $startTime = Carbon::parse($quiz->StartTime);
        $endTime = (clone $startTime)->addMinutes((int) $quiz->Duration);

        if (!$now->between($startTime, $endTime)) {
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

            return $scorePercentage;
        });

        return response()->json([
            'message' => 'Quiz submitted successfully.',
            'score' => $scorePercentage,
        ]);
    }

    /**
     * Full list of quizzes created by the logged-in lecturer, for the
     * desktop "All Quizzes" screen (dashboard only shows the latest 5).
     */
    public function indexForLecturer()
    {
        $user = Auth::user();
        $lecturer = Lecturer::where('UserID', $user->UserID)->first();

        $quizzes = Quiz::where('LecturerID', optional($lecturer)->LecturerID)
            ->with('group')
            ->latest('StartTime')
            ->get()
            ->map(function ($quiz) {
                return [
                    'id' => $quiz->QuizID,
                    'title' => $quiz->Title,
                    'group_name' => optional($quiz->group)->GroupName ?? 'Group',
                    'duration' => $quiz->Duration,
                    'due' => Carbon::parse($quiz->StartTime)->toIso8601String(),
                    'attempt_count' => Attempt::where('QuizID', $quiz->QuizID)->count(),
                    'results_released' => (bool) $quiz->ResultsReleased,
                ];
            });

        return response()->json(['quizzes' => $quizzes]);
    }

    /**
     * Lecturer-only quiz review: schedule, attempt summary, release-results state.
     * Mirrors the web's quizzes.show view for lecturers.
     */
    public function showForLecturer($quizId)
    {
        $user = Auth::user();
        $lecturer = Lecturer::where('UserID', $user->UserID)->first();
        $quiz = Quiz::with('group')->findOrFail($quizId);

        if (!$lecturer || $quiz->LecturerID !== $lecturer->LecturerID) {
            return response()->json(['message' => 'You can only review quizzes you created.'], 403);
        }

        $startTime = Carbon::parse($quiz->StartTime);
        $endTime = (clone $startTime)->addMinutes((int) $quiz->Duration);

        $attemptCount = Attempt::where('QuizID', $quiz->QuizID)->count();
        $averageScore = Attempt::where('QuizID', $quiz->QuizID)->avg('Score');

        return response()->json([
            'id' => $quiz->QuizID,
            'title' => $quiz->Title,
            'group_name' => optional($quiz->group)->GroupName ?? 'Group',
            'group_id' => $quiz->GroupID,
            'duration' => $quiz->Duration,
            'start_time' => $startTime->toIso8601String(),
            'end_time' => $endTime->toIso8601String(),
            'attempt_count' => $attemptCount,
            'average_score' => $averageScore !== null ? round((float) $averageScore, 2) : null,
            'results_released' => (bool) $quiz->ResultsReleased,
        ]);
    }

    /**
     * Lecturer releases results for a quiz they created.
     */
    public function releaseResults($quizId)
    {
        $user = Auth::user();
        $lecturer = Lecturer::where('UserID', $user->UserID)->first();
        $quiz = Quiz::findOrFail($quizId);

        if (!$lecturer || $quiz->LecturerID !== $lecturer->LecturerID) {
            return response()->json(['message' => 'You can only release results for quizzes you created.'], 403);
        }

        $quiz->update(['ResultsReleased' => true]);

        return response()->json(['message' => 'Results released to students.', 'results_released' => true]);
    }

    public function store(Request $request, $groupId)
    {
        $user = Auth::user();

        if ($user->RoleID != 2) {
            return response()->json(['message' => 'Only lecturers can create quizzes.'], 403);
        }

        $group = Group::whereHas('members', function ($query) use ($user) {
            $query->where('group_members.UserID', $user->UserID)
                ->where('group_members.Status', 'approved');
        })->find($groupId);

        if (!$group) {
            return response()->json(['message' => 'You are not a member of that group.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
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

        $quiz = DB::transaction(function () use ($validated, $user, $group) {
            $lecturer = Lecturer::firstOrCreate(
                ['UserID' => $user->UserID],
                ['Department' => 'General', 'DateEmployed' => now(), 'Status' => 'active']
            );

            $quiz = Quiz::query()->create([
                'Title' => $validated['title'],
                'StartTime' => Carbon::parse($validated['start_time']),
                'Duration' => (int) $validated['duration'],
                'GroupID' => $group->GroupID,
                'LecturerID' => $lecturer->LecturerID,
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

            return $quiz;
        });

        return response()->json([
            'message' => 'Quiz created successfully',
            'quiz' => [
                'id' => $quiz->QuizID,
                'title' => $quiz->Title,
            ],
        ], 201);
    }
}