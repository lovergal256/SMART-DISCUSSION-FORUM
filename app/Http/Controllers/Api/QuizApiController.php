<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Lecturer;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class QuizApiController extends Controller
{
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