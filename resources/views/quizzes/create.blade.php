@extends('layouts.app')

@section('title', 'Create Quiz - Smart Discussion Forum')

@section('content')
    <div class="page-head">
        <h1>Create Quiz</h1>
        <p>Lecturers can create quizzes and define questions in one step.</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('quizzes.store') }}" class="panel">
        @csrf

        <div class="field">
            <label>Quiz Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
        </div>

        <div class="field">
            <label>Group</label>
            <select name="group_id" required>
                <option value="">Select group</option>
                @foreach($groups as $group)
                    <option value="{{ $group->GroupID }}" @selected((string) old('group_id') === (string) $group->GroupID)>
                        {{ $group->GroupName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field-grid">
            <div class="field">
                <label>Start Time</label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required>
            </div>
            <div class="field">
                <label>Duration (minutes)</label>
                <input type="number" name="duration" min="1" max="300" value="{{ old('duration', 30) }}" required>
            </div>
        </div>

        <h3 class="section-title">Questions</h3>
        <div id="questions-wrapper">
            @php
                $existingQuestions = old('questions', [
                    ['question_text' => '', 'option_a' => '', 'option_b' => '', 'option_c' => '', 'option_d' => '', 'correct_option' => 'A', 'marks' => 1],
                ]);
            @endphp

            @foreach($existingQuestions as $index => $question)
                <div class="question-block">
                    <h4>Question {{ $index + 1 }}</h4>
                    <textarea name="questions[{{ $index }}][question_text]" placeholder="Question text" required>{{ $question['question_text'] ?? '' }}</textarea>
                    <input type="text" name="questions[{{ $index }}][option_a]" placeholder="Option A" value="{{ $question['option_a'] ?? '' }}" required>
                    <input type="text" name="questions[{{ $index }}][option_b]" placeholder="Option B" value="{{ $question['option_b'] ?? '' }}" required>
                    <input type="text" name="questions[{{ $index }}][option_c]" placeholder="Option C (optional)" value="{{ $question['option_c'] ?? '' }}">
                    <input type="text" name="questions[{{ $index }}][option_d]" placeholder="Option D (optional)" value="{{ $question['option_d'] ?? '' }}">
                    <div class="field-grid">
                        <div class="field">
                            <label>Correct option</label>
                            <select name="questions[{{ $index }}][correct_option]" required>
                                @foreach(['A', 'B', 'C', 'D'] as $option)
                                    <option value="{{ $option }}" @selected(($question['correct_option'] ?? 'A') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Marks</label>
                            <input type="number" name="questions[{{ $index }}][marks]" min="1" value="{{ $question['marks'] ?? 1 }}" required>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="actions">
            <button type="button" id="add-question" class="view-all">+ Add another question</button>
            <button type="submit" class="take-quiz-link">Create Quiz</button>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .field { margin-bottom: 14px; }
        .field input, .field select, .field textarea {
            width: 100%; padding: 10px; border: 1px solid var(--line);
            border-radius: 8px; font-size: 13px; background: #fff;
        }
        .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .section-title { margin: 16px 0 12px; font-size: 16px; }
        .question-block { border: 1px solid var(--line); border-radius: 12px; padding: 14px; margin-bottom: 12px; }
        .question-block h4 { margin-bottom: 10px; }
        .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var wrapper = document.getElementById('questions-wrapper');
            var addButton = document.getElementById('add-question');
            if (!wrapper || !addButton) return;

            addButton.addEventListener('click', function () {
                var index = wrapper.querySelectorAll('.question-block').length;
                var html = ''
                    + '<div class="question-block">'
                    + '<h4>Question ' + (index + 1) + '</h4>'
                    + '<textarea name="questions[' + index + '][question_text]" placeholder="Question text" required></textarea>'
                    + '<input type="text" name="questions[' + index + '][option_a]" placeholder="Option A" required>'
                    + '<input type="text" name="questions[' + index + '][option_b]" placeholder="Option B" required>'
                    + '<input type="text" name="questions[' + index + '][option_c]" placeholder="Option C (optional)">'
                    + '<input type="text" name="questions[' + index + '][option_d]" placeholder="Option D (optional)">'
                    + '<div class="field-grid">'
                    + '<div class="field"><label>Correct option</label>'
                    + '<select name="questions[' + index + '][correct_option]" required>'
                    + '<option value="A">A</option><option value="B">B</option>'
                    + '<option value="C">C</option><option value="D">D</option>'
                    + '</select></div>'
                    + '<div class="field"><label>Marks</label>'
                    + '<input type="number" name="questions[' + index + '][marks]" min="1" value="1" required></div>'
                    + '</div></div>';
                wrapper.insertAdjacentHTML('beforeend', html);
            });
        });
    </script>
@endpush