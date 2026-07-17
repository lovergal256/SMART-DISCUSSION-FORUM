@extends($layout)

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

    @if($role !== 'lecturer' && ! $isAttempted && $isActive)
        <div id="quiz-timer-badge">
            <span id="quiz-countdown">--:--</span>
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

            @if($role !== 'lecturer' && ! $isAttempted && now()->lt($startTime))
                <div class="meta-item">
                    <strong>Opens in:</strong>
                    <span id="quiz-start-countdown">--:--</span>
                    <span style="color:var(--ink-soft); font-size:12px;">(this page will refresh automatically)</span>
                </div>
            @endif
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
            <form method="POST" action="{{ route('quizzes.attempts.store', $quiz->QuizID) }}" id="quiz-form">
                @csrf
                @foreach($questions as $index => $question)
                    <section class="question-card">
                        <h3>Question {{ $index + 1 }} ({{ $question->Marks }} marks)</h3>
                        <p>{{ $question->QuestionText }}</p>

                        <label class="option-item">
                            <input type="radio" name="answers[{{ $question->QuestionID }}]" value="A">
                            <span>A. {{ $question->OptionA }}</span>
                        </label>
                        <label class="option-item">
                            <input type="radio" name="answers[{{ $question->QuestionID }}]" value="B">
                            <span>B. {{ $question->OptionB }}</span>
                        </label>
                        @if($question->OptionC)
                            <label class="option-item">
                                <input type="radio" name="answers[{{ $question->QuestionID }}]" value="C">
                                <span>C. {{ $question->OptionC }}</span>
                            </label>
                        @endif
                        @if($question->OptionD)
                            <label class="option-item">
                                <input type="radio" name="answers[{{ $question->QuestionID }}]" value="D">
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
        .option-item {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 13px;
    color: var(--ink);
    width: auto;
}
.option-item input[type="radio"] {
    width: auto !important;
    flex-shrink: 0;
    margin: 0;
    padding: 0;
}
.option-item span {
    text-align: left;
}
        .meta-item { margin-bottom: 8px; font-size: 13px; color: var(--ink-soft); }
        .correct-badge { margin-top: 8px; color: var(--success); font-size: 12px; font-weight: 700; }
        .empty-state { color: var(--ink-soft); }

        #quiz-timer-badge {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1000;
            background: #fff;
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 8px 16px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }
        #quiz-countdown {
            font-size: 28px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: #1a7a45; /* green by default */
            transition: color 0.2s;
        }
        #quiz-countdown.danger {
            color: #d9302a; /* red under 10 seconds */
        }
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Server-provided timestamps (ms since epoch), so the browser's own clock
        // doesn't need to be trusted for anything other than ticking the display.
        var endTimeMs = {{ $endTime->timestamp * 1000 }};
        var startTimeMs = {{ $startTime->timestamp * 1000 }};
        var serverNowMs = {{ now()->timestamp * 1000 }};
        var clientNowMs = Date.now();
        var clockOffset = serverNowMs - clientNowMs;

        function serverNow() {
            return Date.now() + clockOffset;
        }

        function formatDuration(ms) {
            if (ms < 0) ms = 0;
            var totalSeconds = Math.floor(ms / 1000);
            var minutes = Math.floor(totalSeconds / 60);
            var seconds = totalSeconds % 60;
            return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }

        // --- Case 1: quiz hasn't started yet — refresh automatically the moment it opens ---
        var startEl = document.getElementById('quiz-start-countdown');
        if (startEl) {
            var startTimer = setInterval(function () {
                var remaining = startTimeMs - serverNow();
                if (remaining <= 0) {
                    clearInterval(startTimer);
                    window.location.reload();
                    return;
                }
                startEl.textContent = formatDuration(remaining);
            }, 1000);
        }

        // --- Case 2: quiz is active — count down, color-code, and auto-submit at zero ---
        var countdownEl = document.getElementById('quiz-countdown');
        var quizForm = document.getElementById('quiz-form');
        if (countdownEl) {
            var submitted = false;

            var endTimer = setInterval(function () {
                var remaining = endTimeMs - serverNow();

                if (remaining <= 0) {
                    clearInterval(endTimer);
                    countdownEl.textContent = '00:00';
                    countdownEl.classList.add('danger');
                    if (!submitted && quizForm) {
                        submitted = true;
                        quizForm.submit();
                    }
                    return;
                }

                countdownEl.textContent = formatDuration(remaining);

                if (remaining <= 10000) {
                    countdownEl.classList.add('danger');
                } else {
                    countdownEl.classList.remove('danger');
                }
            }, 1000);

            if (quizForm) {
                quizForm.addEventListener('submit', function () {
                    submitted = true;
                });
            }
        }
    });
</script>
@endpush