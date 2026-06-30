@extends('layouts.app')

@section('content')
    <h2>{{ $topic->title }}</h2>
    <div class="card">
        <p>{{ $post->body }}</p>
        <small>Posted by {{ $post->user->name ?? 'Unknown' }}</small>
    </div>

    <h3>Replies</h3>

    @forelse($replies as $reply)
        <div class="card">
            <p>{{ $reply->body }}</p>
            <small>By {{ $reply->user->name ?? 'Unknown' }}</small>

            @if(auth()->id() == $reply->user_id)
                <form action="{{ route('topics.posts.replies.destroy', [$topic->id, $post->id, $reply->id]) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-red">Delete</button>
                </form>
            @endif
        </div>
    @empty
        <div class="card">
            <p>No replies yet. Be the first to reply!</p>
        </div>
    @endforelse

    {{ $replies->links() }}

    <h3>Add a Reply</h3>
    <div class="card">
        <form action="{{ route('topics.posts.replies.store', [$topic->id, $post->id]) }}" method="POST">
            @csrf
            <label>Your Reply</label>
            <textarea name="body" rows="4" placeholder="Write your reply...">{{ old('body') }}</textarea>
            @error('body')
                <p style="color:red">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn">Post Reply</button>
        </form>
    </div>
@endsection