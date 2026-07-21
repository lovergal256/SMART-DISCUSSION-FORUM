@extends('layouts.lecturer_app')

@section('title', 'Dashboard — Smart Discussion Forum')

@section('content')

    <div class="page-head">
        <h1>Welcome back, {{ $user->FullName ?? 'there' }}! 👋</h1>
        <p>Let's continue our journey.</p>
    </div>

    {{-- STAT CARDS --}}
    <div class="stat-grid">
        @foreach($stats as $stat)
            <a class="stat-card" href="{{ $stat['url'] }}">
                <div class="stat-icon">{{ $stat['icon'] }}</div>
                <div>
                    <div class="stat-num">{{ $stat['value'] }}</div>
                    <div class="stat-lbl">{{ $stat['label'] }}</div>
                    <div class="stat-change">↑ {{ $stat['change'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="row-3">

        {{-- RECENT DISCUSSIONS --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">💬</span> Recent Discussions</div>
                <a class="view-all" href="{{ route('discussions.index') }}">View all →</a>
            </div>

            @foreach($discussions as $d)
                <div class="disc-item">
                    <span class="cat-tag">{{ $d['category'] }}</span>
                    <div class="disc-body">
                        <a class="disc-title" href="{{ route('discussions.show', $d['id']) }}">{{ $d['title'] }}</a>
                        <div class="disc-meta">{{ $d['author'] }} · {{ $d['posted_at'] }}</div>
                    </div>
                    <div class="disc-replies"><span class="live-dot"></span>{{ $d['replies'] }} replies</div>
                </div>
            @endforeach
        </div>

        {{-- UPCOMING QUIZZES --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">🗓</span> Posted Quizzes</div>
                <a class="view-all" href="{{ route('quizzes.index') }}">View all →</a>
            </div>

            @foreach($quizzes as $quiz)
                <div class="quiz-card">
                    <div class="quiz-title">{{ $quiz['title'] }}</div>
                    <div class="quiz-sub">{{ $quiz['subtitle'] }}</div>
                    <div class="quiz-foot">
                        <div class="quiz-due">📅 Due {{ $quiz['due'] }}</div>
                        <a class="take-quiz-link" href="{{ route('quizzes.show', $quiz['id']) }}">View Quiz</a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- RECOMMENDED --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">⭐</span> Recommended For You</div>
                <a class="view-all" href="{{ route('recommendations.index') }}">View all →</a>
            </div>

            @foreach($recommendations as $rec)
                <div class="rec-item">
                    <div class="rec-icon">{{ $rec['icon'] }}</div>
                    <div>
                        <a class="rec-title" href="{{ $rec['url'] }}">{{ $rec['title'] }}</a>
                        <div class="rec-sub">{{ $rec['subtitle'] }}</div>
                    </div>
                    <span class="rec-arrow">›</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row-2 b">

        {{-- MY GROUPS --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">👥</span> My Groups</div>
                <a class="view-all" href="{{ route('groups.index') }}">View all →</a>
            </div>

            @foreach($groups as $group)
                <div class="group-item">
                    <div class="group-avatar">👥</div>
                    <div>
                        <a class="group-name" href="{{ route('groups.show', $group['id']) }}">{{ $group['name'] }}</a>
                        <div class="group-meta">{{ $group['members'] }} members · {{ $group['new_posts'] }} new posts</div>
                    </div>
                    <span class="status-pill">{{ $group['status'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- MY ACTIVITY --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📈</span> My Activity (This Week)</div>
                <a class="view-all" href="{{ route('activity.index') }}">View full report →</a>
            </div>

            @foreach($activity as $row)
                <div class="activity-row">
                    <div class="activity-lbl">{{ $row['icon'] }} {{ $row['label'] }}</div>
                    <div>
                        <span class="activity-val">{{ $row['value'] }}</span>
                        <span class="activity-delta">↑ {{ $row['change'] }}</span>
                    </div>
                </div>
            @endforeach

            <div class="chart-wrap">
                <svg viewBox="0 0 300 110" width="100%" height="110" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="areaFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="var(--chart-fill-top)"/>
                            <stop offset="100%" stop-color="var(--chart-fill-bottom)"/>
                        </linearGradient>
                    </defs>
                    <polyline points="{{ $activityChartPoints }}" fill="none" stroke="var(--chart-line)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <polygon points="{{ $activityChartPoints }} 280,105 10,105" fill="url(#areaFill)"/>
                </svg>
                <div class="chart-labels">
                    <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">© {{ date('Y') }} Smart Discussion Forum. All rights reserved.</div>

@endsection
