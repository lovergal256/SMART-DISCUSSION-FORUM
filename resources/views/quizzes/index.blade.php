@extends($layout)

@section('title', 'My Quizzes - Smart Discussion Forum')

@section('content')
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($role === 'lecturer')
        <div class="page-head">
            <h1>Quiz Management</h1>
            <p>Create and manage quizzes for your students.</p>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📝</span> Your Quizzes</div>
            </div>

            @forelse($quizzes as $quiz)
                @php
                    $start = \Carbon\Carbon::parse($quiz->StartTime);
                @endphp
                <div class="quiz-card">
                    <div class="quiz-title">{{ $quiz->Title }}</div>
                    <div class="quiz-sub">
                        {{ $quiz->group->GroupName ?? 'Group' }} · {{ $quiz->questions_count }} questions
                    </div>
                    <div class="quiz-foot">
                        <div class="quiz-due">
                            {{ $start->format('M d, Y · h:i A') }} · {{ $quiz->Duration }} mins
                        </div>
                        <a class="take-quiz-link" href="{{ route('quizzes.show', $quiz->QuizID) }}">View Details</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>📭 You have not created any quizzes yet.</p>
                </div>
            @endforelse
        </div>
    @else
        <div class="page-head">
            <h1>My Quizzes</h1>
            <p>You can attempt each quiz once, only between its start time and end time.</p>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📚</span> Available Quizzes</div>
            </div>

            @forelse($quizzes as $quiz)
                @php
                    $start = \Carbon\Carbon::parse($quiz->StartTime);
                    $end = (clone $start)->addMinutes((int) $quiz->Duration);
                    $isActive = $now->between($start, $end);
                    $isAttempted = in_array($quiz->QuizID, $attemptedQuizIds, true);
                @endphp

                <div class="quiz-card">
                    <div class="quiz-title">{{ $quiz->Title }}</div>
                    <div class="quiz-sub">
                        {{ $quiz->group->GroupName ?? 'Group' }} · {{ $quiz->questions_count }} questions
                    </div>
                    <div class="quiz-foot">
                        <div class="quiz-due">
                            🕒 {{ $start->format('M d, Y · h:i A') }} to {{ $end->format('h:i A') }}
                        </div>
                        <a class="take-quiz-link" href="{{ route('quizzes.show', $quiz->QuizID) }}">Open Quiz</a>
                    </div>
                    <div class="quiz-progress">
                        @if($isAttempted)
                            Already attempted
                        @elseif($isActive)
                            Available now
                        @elseif($now->lt($start))
                            Not open yet
                        @else
                            Closed
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>🎯 No quizzes are available yet.</p>
                </div>
            @endforelse
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .quiz-progress { margin-top: 10px; font-size: 12px; color: var(--ink-soft); }
        .empty-state { padding: 18px 0; color: var(--ink-soft); }
        .stat-grid { grid-template-columns: repeat(3, 1fr); }
    </style>
@endpush