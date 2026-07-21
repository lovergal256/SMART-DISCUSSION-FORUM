@extends(auth()->user()->RoleID === 2 ? 'layouts.lecturer_app' : 'layouts.app')

@section('title', 'My Activity — Smart Discussion Forum')

@section('content')

    <div class="page-head">
        <h1>📈 My Activity</h1>
        <p>A summary of your activity on the forum.</p>
    </div>

    <div class="row-3">
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📝</span> Posts Created</div>
            </div>
            <div style="font-size:2em; font-weight:bold; padding:10px 0;">{{ $postsCreated }}</div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">💬</span> Topics Created</div>
            </div>
            <div style="font-size:2em; font-weight:bold; padding:10px 0;">{{ $topicsCreated }}</div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">👥</span> Groups Created</div>
            </div>
            <div style="font-size:2em; font-weight:bold; padding:10px 0;">{{ $groupsCreated }}</div>
        </div>
    </div>

    <div class="row-3" style="margin-top:24px;">
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">📋</span> Quizzes Created</div>
            </div>
            <div style="font-size:2em; font-weight:bold; padding:10px 0;">{{ $quizzesCreated }}</div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">🤝</span> Groups Joined</div>
            </div>
            <div style="font-size:2em; font-weight:bold; padding:10px 0;">{{ $groupsJoined }}</div>
        </div>
    </div>

@endsection