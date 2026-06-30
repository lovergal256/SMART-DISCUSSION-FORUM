@extends('layouts.app')

@section('content')
    <h1>{{ $topic->title }}</h1>
    <div class="card">
        <p>{{ $topic->body }}</p>
        <small>Posted by {{ $topic->user->name ?? 'Unknown' }}</small>
    </div>

    <h2>Posts</h2>
    <a href="{{ route('topics.posts.create', $topic->id) }}" class="btn">+ Add Post</a>
    <br><br>

    @forelse($posts as $post)
        <div class="card">
            <p>{{ $post->body }}</p>
            <small>Posted by {{ $post->user->name ?? 'Unknown' }}</small>
            <br>
            <a href="{{ route('topics.posts.show', [$topic->id, $post->id]) }}" class="btn">View Replies</a>
        </div>
    @empty
        <div class="card">
            <p>No posts yet. Be the first to post!</p>
        </div>
    @endforelse

    {{ $posts->links() }}
@endsection
