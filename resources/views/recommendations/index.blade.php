@extends(auth()->user()->RoleID === 2 ? 'layouts.lecturer_app' : 'layouts.app')

@section('title', 'Recommendations — Smart Discussion Forum')

@section('content')

    {{-- Page Header --}}
    <div class="page-head">
        <h1>🎯 Recommendations For You</h1>
        <p>Personalized suggestions based on forum activity.</p>
    </div>

    <div class="row-3">

        {{-- Trending Topics --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">🔥</span> Trending Topics</div>
            </div>

            @if($trendingTopics->count() > 0)
                @foreach($trendingTopics as $topic)
                    <div class="disc-item">
                        <div class="disc-body">
                            <a class="disc-title" href="{{ route('discussions.show', $topic->TopicID) }}">
                                {{ $topic->Title }}
                            </a>
                            <div class="disc-meta">{{ $topic->post_count }} posts · {{ $topic->Status }}</div>
                        </div>
                        <div class="disc-replies">
                            <span class="live-dot"></span>{{ $topic->post_count }} posts
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <p>💬 No trending topics yet. Start a discussion!</p>
                </div>
            @endif
        </div>

        {{-- Most Active Posts --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><span class="ic">💬</span> Most Active Posts</div>
            </div>

            @if($activePosts->count() > 0)
                @foreach($activePosts as $post)
                    <div class="disc-item">
                        <div class="disc-body">
                            <a class="disc-title" href="{{ route('discussions.show', $post->PostID) }}">
                                {{ Str::limit($post->content, 80) }}
                            </a>
                            <div class="disc-meta">📁 {{ $post->TopicTitle }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <p>📝 No active posts yet.</p>
                </div>
            @endif
        </div>

       

    </div>

    {{-- Suggested Groups --}}
    <div class="panel" style="margin-top:24px">
        <div class="panel-head">
            <div class="panel-title"><span class="ic">👥</span> Suggested Groups</div>
        </div>

        @if($suggestedGroups->count() > 0)
            <div class="row-3">
                @foreach($suggestedGroups as $group)
                    <div class="group-item">
                        <div class="group-avatar">👥</div>
                        <div>
                            <a class="group-name" href="{{ route('groups.show', $group->GroupID) }}">
                                {{ $group->GroupName }}
                            </a>
                            <div class="group-meta">{{ Str::limit($group->Description, 60) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <p>👥 No group suggestions yet.</p>
            </div>
        @endif
    </div>

@endsection