@extends('layouts.app')

@section('content')
    <h2>{{ $topic->Title }}</h2>
    <div class="card">
        <p>{{ $post->content }}</p>
        <small>Posted by {{ $post->user->FullName ?? 'Unknown' }}</small>
    </div>

    <h3>Replies</h3>

    @forelse($replies as $reply)
        <div class="card">
            <p>{{ $reply->Body }}</p>
            <small style="color: #02367a; font-weight: 600;">By {{ $reply->user->FullName ?? 'Unknown' }}</small>

            @if(auth()->id() == $reply->UserID)
                <form action="{{ route('topics.posts.replies.destroy', [$topic, $post, $reply]) }}" method="POST" style="display:inline">
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
        <form action="{{ route('topics.posts.replies.store', [$topic->TopicID, $post->PostID]) }}" method="POST">
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