@extends($layout)

@section('title', 'My Activity — Smart Discussion Forum')

@section('content')

<style>
    .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: bold; margin-left: 12px; vertical-align: middle; }
    .badge-high { background: #d0f0dc; color: #1a7a45; }
    .badge-moderate { background: #fff3cd; color: #8a6d1a; }
    .badge-low { background: #f8d7da; color: #a3333d; }
    body.dark-mode .badge-high { background: #163e28; color: #6fd99a; }
    body.dark-mode .badge-moderate { background: #3e341a; color: #e0c060; }
    body.dark-mode .badge-low { background: #3e1a1d; color: #f5b7bb; }

    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; margin-top: 16px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 16px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    body.dark-mode .stat-card { background: #1e1e1e; }
    .stat-card .stat-value { font-size: 24px; font-weight: bold; color: #023e8a; }
    body.dark-mode .stat-card .stat-value { color: #4da3ff; }
    .stat-card .stat-label { font-size: 13px; color: #5a6b7a; margin-top: 4px; }
    body.dark-mode .stat-card .stat-label { color: #9db4c4; }

    .bar-chart { display: flex; align-items: flex-end; gap: 10px; height: 140px; margin-top: 16px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
    .bar-fill { width: 100%; background: linear-gradient(180deg, #0077b6, #023e8a); border-radius: 4px 4px 0 0; min-height: 4px; transition: height 0.3s; }
    body.dark-mode .bar-fill { background: linear-gradient(180deg, #4da3ff, #1a5fa3); }
    .bar-label { font-size: 12px; color: #5a6b7a; margin-top: 6px; }
    body.dark-mode .bar-label { color: #9db4c4; }
    .bar-count { font-size: 11px; color: #023e8a; font-weight: bold; }
    body.dark-mode .bar-count { color: #4da3ff; }

    .dist-row { margin-bottom: 14px; }
    .dist-row:last-child { margin-bottom: 0; }
    .dist-row-top { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px; }
    .dist-track { background: #e6edf3; border-radius: 6px; height: 8px; overflow: hidden; }
    .dist-fill { background: linear-gradient(90deg, #0077b6, #023e8a); height: 100%; border-radius: 6px; }
    body.dark-mode .dist-track { background: #2a2a2a; }
    body.dark-mode .dist-fill { background: linear-gradient(90deg, #4da3ff, #1a5fa3); }

    .feed-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eef2f6; }
    .feed-item:last-child { border-bottom: none; }
    .feed-icon { width: 34px; height: 34px; border-radius: 50%; background: #eaf4fb; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    body.dark-mode .feed-item { border-bottom: 1px solid #2a2a2a; }
    body.dark-mode .feed-icon { background: #1e2a33; }
    .feed-when { margin-left: auto; text-align: right; font-size: 12px; color: #5a6b7a; white-space: nowrap; }
    body.dark-mode .feed-when { color: #9db4c4; }
</style>

<div class="page-head">
    <h1>
        📋 My Activity
        <span class="badge {{ $activityLevel['class'] }}">{{ $activityLevel['label'] }}</span>
    </h1>
    <p>Monitor your engagement and participation across the discussion forum.</p>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $discussionsCount }}</div>
        <div class="stat-label">Discussions</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $postsCount }}</div>
        <div class="stat-label">Posts</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $repliesCount }}</div>
        <div class="stat-label">Replies</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $quizzesCount }}</div>
        <div class="stat-label">Quizzes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $groupsCount }}</div>
        <div class="stat-label">Groups</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $warningsCount }}</div>
        <div class="stat-label">Warnings</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:15px">
            {{ $lastActive ? $lastActive->diffForHumans() : 'Never' }}
        </div>
        <div class="stat-label">Last Active</div>
    </div>
</div>

<div class="row-3" style="margin-top:24px">

    {{-- Weekly Activity (line chart) --}}
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><span class="ic">📈</span> Weekly Activity</div>
    </div>

    @php
        $lw = collect($weeklyData)->values();
        $lwCount = $lw->count();
        $lwW = 300; $lwH = 140; $lwPad = 16;
        $lwMax = max(1, $weeklyMax);
        $lwStepX = $lwCount > 1 ? ($lwW - $lwPad * 2) / ($lwCount - 1) : 0;
        $lwCoords = $lw->map(function ($d, $i) use ($lwStepX, $lwPad, $lwH, $lwMax) {
            $x = $lwPad + $i * $lwStepX;
            $y = $lwH - $lwPad - (($d['total'] / $lwMax) * ($lwH - $lwPad * 2));
            return round($x, 1) . ',' . round($y, 1);
        });
        $lwLine = $lwCoords->implode(' ');
        $lwArea = $lwPad . ',' . ($lwH - $lwPad) . ' ' . $lwLine . ' ' . ($lwW - $lwPad) . ',' . ($lwH - $lwPad);
    @endphp

    <svg viewBox="0 0 {{ $lwW }} {{ $lwH }}" style="width:100%; height:140px;">
        <defs>
            <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#0077b6" stop-opacity="0.35" />
                <stop offset="100%" stop-color="#0077b6" stop-opacity="0" />
            </linearGradient>
        </defs>
        <polygon points="{{ $lwArea }}" fill="url(#areaGrad)" />
        <polyline points="{{ $lwLine }}" fill="none" stroke="#0077b6" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />
        @foreach($lwCoords as $c)
            @php [$cx, $cy] = explode(',', $c); @endphp
            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3" fill="#0077b6" />
        @endforeach
    </svg>
    <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:12px; color:#5a6b7a;">
        @foreach($lw as $day)
            <span>{{ $day['label'] }}</span>
        @endforeach
    </div>
</div>

    {{-- Activity Distribution --}}
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><span class="ic">📊</span> Activity Distribution</div>
    </div>

    @php
        $colors = [
            'Discussions' => '#0077b6',
            'Posts'       => '#f4a261',
            'Replies'     => '#2a9d8f',
            'Quizzes'     => '#e76f51',
            'Groups'      => '#8338ec',
        ];
        $cumulative = 0;
        $stops = [];
        foreach ($distribution as $label => $data) {
            $start = $cumulative;
            $cumulative += $data['percent'];
            $stops[] = "{$colors[$label]} {$start}% {$cumulative}%";
        }
        $gradientCss = implode(', ', $stops);
    @endphp

    <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
        <div style="position:relative; width:150px; height:150px; flex-shrink:0;">
            <div style="width:150px; height:150px; border-radius:50%; background: conic-gradient({{ $gradientCss }});"></div>
            <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:80px; height:80px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:13px; color:#023e8a;">
                {{ array_sum(array_column($distribution->toArray(), 'count')) }} total
            </div>
        </div>

        <div style="flex:1; min-width:140px;">
            @foreach($distribution as $label => $data)
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; font-size:13px;">
                    <span style="width:10px; height:10px; border-radius:50%; background:{{ $colors[$label] }}; display:inline-block; flex-shrink:0;"></span>
                    <span style="flex:1;">{{ $label }}</span>
                    <span style="font-weight:bold;">{{ $data['count'] }} ({{ $data['percent'] }}%)</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

</div>

{{-- Recent Activities --}}
<div class="panel" style="margin-top:24px">
    <div class="panel-head">
        <div class="panel-title"><span class="ic">🕒</span> Recent Activities</div>
    </div>

    @php
        $icons = ['topic' => '📝', 'post' => '📝', 'reply' => '💬', 'quiz' => '🧩', 'group' => '👥'];
        $labels = ['topic' => 'Started discussion', 'post' => 'Posted in', 'reply' => 'Replied to', 'quiz' => 'Attempted', 'group' => 'Joined'];
    @endphp

    @if($recentActivities->count() > 0)
        @foreach($recentActivities as $item)
            <div class="feed-item">
                <div class="feed-icon">{{ $icons[$item->type] ?? '•' }}</div>
                <div class="disc-body">
                    <div class="disc-title">{{ $labels[$item->type] ?? ucfirst($item->type) }} "{{ $item->detail }}"</div>
                </div>
                <div class="feed-when">
                    {{ $item->date_label }}<br>{{ $item->time_label }}
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <p>🕒 No activity yet. Start a discussion or take a quiz to see it here!</p>
        </div>
    @endif
</div>

@endsection