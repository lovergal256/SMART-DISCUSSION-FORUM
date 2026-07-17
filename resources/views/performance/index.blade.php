@extends($layout)

@section('title', 'My Performance — Smart Discussion Forum')

@section('content')

    {{-- Page Header --}}
    <div class="page-head">
        <h1>📊 My Performance</h1>
        <p>Track your discussion activity and quiz results.</p>
    </div>

    {{-- Overall Performance --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><span class="ic">🏆</span> Overall Performance</div>
        </div>
        <div class="row-3">
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Overall Score</div>
                    <div class="disc-meta">{{ $overallMarks }}%</div>
                </div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Grade</div>
                    <div class="disc-meta">{{ $grade }}</div>
                </div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Status</div>
                    <div class="disc-meta">{{ $status }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row-3" style="margin-top:24px">

        {{-- Discussion Participation --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">💬</span> Discussion Participation</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Topics Created</div>
                </div>
                <div class="disc-replies">{{ $topicsCreated }}</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Posts Created</div>
                </div>
                <div class="disc-replies">{{ $postsCreated }}</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Replies Made</div>
                </div>
                <div class="disc-replies">{{ $repliesMade }}</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Participation Score</div>
                </div>
                <div class="disc-replies">{{ $participationScore }} / 50</div>
            </div>
        </div>

        {{-- Quiz Performance --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📖</span> Quiz Performance</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Quizzes Attempted</div>
                </div>
                <div class="disc-replies">{{ $quizzesAttempted }}</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Average Quiz Score</div>
                </div>
                <div class="disc-replies">{{ $averageQuizScore }}%</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Highest Score</div>
                </div>
                <div class="disc-replies">{{ $highestScore }}%</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Quiz Marks</div>
                </div>
                <div class="disc-replies">{{ $quizMarks }} / 50</div>
            </div>
        </div>

        {{-- Overall Marks --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📈</span> Overall Marks</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Participation Marks</div>
                </div>
                <div class="disc-replies">{{ $participationScore }} / 50</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Quiz Marks</div>
                </div>
                <div class="disc-replies">{{ $quizMarks }} / 50</div>
            </div>
            <div class="disc-item">
                <div class="disc-body">
                    <div class="disc-title">Overall Performance</div>
                </div>
                <div class="disc-replies">{{ $overallMarks }} / 100</div>
            </div>
        </div>

    </div>

    {{-- Recent Quiz Results --}}
    <div class="panel" style="margin-top:24px">
        <div class="panel-head">
            <div class="panel-title"><span class="ic">📝</span> Recent Quiz Results</div>
        </div>

        @if($recentQuizzes->count() > 0)
            @foreach($recentQuizzes as $result)
                <div class="disc-item">
                    <div class="disc-body">
                        <div class="disc-title">{{ $result->quiz->Title ?? 'Untitled Quiz' }}</div>
                        <div class="disc-meta">
                            {{ \Carbon\Carbon::parse($result->DateRecorded)->format('M d, Y') }}
                        </div>
                    </div>
                    <div class="disc-replies">{{ round($result->Score) }}%</div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <p>📝 No quiz results yet. Take a quiz to see your performance!</p>
            </div>
        @endif
    </div>

@endsection