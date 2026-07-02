@extends('layouts.app')

@section('content')
    <h1>{{ $topic->Title }}</h1>
    <div class="card">
      <p><strong>Topic Description </strong> <br>{{ $topic->Description }}</p>
        <small>Posted by {{ $topic->user->FullName ?? 'Unknown' }}</small>
      
        @if(auth()->check() && auth()->id() == $topic->UserID)
           <a href="{{ route('topics.edit', $topic) }}" class="btn">Edit</a>
         <form action="{{ route('topics.destroy', $topic) }}" method="POST" style="display:inline">
             @csrf
             @method('DELETE')
            <button type="submit" class="btn" onclick="return confirm('Delete this topic?')">Delete</button>
         </form>
        @endif
    </div>

    <h2>Posts</h2>
    <a href="{{ route('topics.posts.create', $topic->TopicID) }}" class="btn">+ Add Post</a>
    <br><br>

    @forelse($posts as $post)
    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
           <p style="color: #4a0080;">{{ $post->content }}</p>
            <small>Posted by {{ $post->user->FullName ?? 'Unknown' }}</small>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="{{ route('topics.posts.show', [$topic->TopicID, $post->PostID]) }}" class="btn">View Replies</a>
            @if(auth()->check() && auth()->id() == $post->UserID)
                <form action="{{ route('topics.posts.destroy', [$topic, $post]) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-red" onclick="return confirm('Delete this post?')">Delete</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="card">
        <p>No posts yet. Be the first to post!</p>
    </div>
@endforelse


@endsection
