@extends('layouts.app')

@section('title', 'Quiz Details - Smart Discussion Forum')

@section('content')
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="page-head">
        <h1>{{ $quiz->Title }}</h1>
        <p>{{ $quiz->group->GroupName ?? 'Group' }} · {{ $quiz->Duration }} minutes</p>
    </div>

    <div class="row-2 b">
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">🗓</span> Schedule</div>
            </div>
            <div class="meta-item"><strong>Starts:</strong> {{ $startTime->format('M d, Y · h:i A') }}</div>
            <div class="meta-item"><strong>Ends:</strong> {{ $endTime->format('M d, Y · h:i A') }}</div>
            <div class="meta-item"><strong>Duration:</strong> {{ $quiz->Duration }} minutes</div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📌</span> Summary</div>
            </div>
      @if($role === 'lecturer')
    <div class="meta-item"><strong>Attempts:</strong> {{ $attemptCount }}</div>
    <div class="meta-item">
        <strong>Average score:</strong>
        {{ is_null($averageScore) ? 'No attempts yet' : number_format($averageScore, 2) . '%' }}
    </div>
    <div class="meta-item">
        <strong>Results released:</strong> {{ $quiz->ResultsReleased ? 'Yes' : 'No' }}
    </div>
    @if(! $quiz->ResultsReleased)
        <form method="POST" action="{{ route('quizzes.release', $quiz->QuizID) }}">
            @csrf
            <button type="submit" class="take-quiz-link">Release Results</button>
        </form>
    @endif
@else
    <div class="meta-item"><strong>Attempts allowed:</strong> 1</div>
    <div class="meta-item">
        <strong>Your status:</strong>
        @if($attempt)
            @if($quiz->ResultsReleased)
                Attempted ({{ number_format($attempt->Score, 2) }}%)
            @else
                Attempted — results not yet released
            @endif
        @elseif($isActive)
            Available now
        @elseif(now()->lt($startTime))
            Not open yet
        @else
            Closed
        @endif
    </div>
@endif
            <a class="view-all" href="{{ route('quizzes.index') }}">← Back to quizzes</a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><span class="ic">📝</span> Questions</div>
        </div>

        @if($isAttempted || $role === 'lecturer')
            <p class="empty-state">
                @if($isAttempted)
                    You have already attempted this quiz.
                @else
                    Preview only — lecturers cannot attempt quizzes.
                @endif
            </p>
        @elseif(! $isActive)
            <div>
                <p>⏱ This quiz can only be attempted between <strong>{{ $startTime->format('M d, h:i A') }}</strong> and <strong>{{ $endTime->format('M d, h:i A') }}</strong>.</p>
            </div>
        @else
            <form method="POST" action="{{ route('quizzes.attempts.store', $quiz->QuizID) }}">
                @csrf
                @foreach($questions as $index => $question)
                    <section class="question-card">
                        <h3>Question {{ $index + 1 }} ({{ $question->Marks }} marks)</h3>
                        <p>{{ $question->QuestionText }}</p>

                        <label class="option-item">
                            <input type="radio" name="answers[{{ $question->QuestionID }}]" value="A" required>
                            <span>A. {{ $question->OptionA }}</span>
                        </label>
                        <label class="option-item">
                            <input type="radio" name="answers[{{ $question->QuestionID }}]" value="B" required>
                            <span>B. {{ $question->OptionB }}</span>
                        </label>
                        @if($question->OptionC)
                            <label class="option-item">
                                <input type="radio" name="answers[{{ $question->QuestionID }}]" value="C" required>
                                <span>C. {{ $question->OptionC }}</span>
                            </label>
                        @endif
                        @if($question->OptionD)
                            <label class="option-item">
                                <input type="radio" name="answers[{{ $question->QuestionID }}]" value="D" required>
                                <span>D. {{ $question->OptionD }}</span>
                            </label>
                        @endif
                    </section>
                @endforeach

                <button type="submit" class="take-quiz-link">Submit Quiz</button>
            </form>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .question-card { border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        .question-card h3 { margin-bottom: 8px; font-size: 14px; }
        .question-card p { margin-bottom: 12px; font-size: 13px; }
        .option-item { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; font-size: 13px; color: var(--ink); }
        .meta-item { margin-bottom: 8px; font-size: 13px; color: var(--ink-soft); }
        .correct-badge { margin-top: 8px; color: var(--success); font-size: 12px; font-weight: 700; }
        .empty-state { color: var(--ink-soft); }
    </style>
@endpush