@extends('layouts.app')

@section('content')
    <h1>All Topics</h1>
    <a href="{{ route('topics.create') }}" class="btn">+ New Topic</a>
    <br><br>

   @forelse($topics as $topic)
    <div class="card">
        <h3>
            <a href="{{ route('topics.show', $topic) }}">{{ $topic->Title }}</a>
        </h3>
        <p>{{ Str::limit($topic->Description, 100) }}</p>
        <small>Posted by {{ $topic->user->FullName ?? 'Unknown' }}</small>
        <br>
        <small style="color: #0077b6; font-weight: 600;">
            📝 {{ $topic->posts()->count() }} posts · 💬 {{ $topic->repliesCount() }} replies
        </small>
    </div>
@empty
    <div class="card">
        <p>No topics yet. Be the first to create one!</p>
    </div>
@endforelse
    
@endsection
