@extends('layouts.app')
@section('content')
    <a href="{{ route('discussions.show', $topic->discussion->DiscussionID) }}" class="btn" style="margin-bottom:15px; display:inline-block;">
        ← All Topics
    </a>
    <h2>{{ $topic->Title }}</h2>
    <div class="card">
        <p>{{ $post->content }}</p>
        <small>Posted by {{ $post->user->FullName ?? 'Unknown' }}</small>
    </div>

    <h3>Replies</h3>

   @forelse($replies->where('ParentReplyID', null) as $reply)
    <div class="card" style="margin-left: 40px;">
        <p>{{ $reply->Body }}</p>
        <small style="color: #023e8a; font-weight: 600;">By {{ $reply->user->FullName ?? 'Unknown' }}</small>
        <small style="color: #888;">· {{ $reply->created_at?->diffForHumans() }}</small>

        @if(true)
            <a href="{{ route('topics.posts.replies.edit', [$topic, $post, $reply]) }}" class="btn">✏️ Edit</a>
            <form action="{{ route('topics.posts.replies.destroy', [$topic, $post, $reply]) }}" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-red" onclick="return confirm('Delete this reply?')">Delete</button>
            </form>
        @endif

        {{-- Reply to this reply --}}
        <form action="{{ route('topics.posts.replies.store', [$topic, $post]) }}" method="POST" style="margin-top:10px">
            @csrf
            <input type="hidden" name="parent_reply_id" value="{{ $reply->ReplyID }}">
            <textarea name="body" rows="2" placeholder="Reply to this..."></textarea>
            <button type="submit" class="btn">↩ Reply</button>
        </form>

        {{-- Nested replies indented --}}
     @foreach($replies->where('ParentReplyID', $reply->ReplyID) as $childReply)
       <div class="card" style="margin-left: 60px; margin-top: 10px;">
            <p>{{ $childReply->Body }}</p>
            <small style="color: #023e8a; font-weight: 600;">By {{ $childReply->user->FullName ?? 'Unknown' }}</small>
            <small style="color: #888;">· {{ $childReply->created_at?->diffForHumans() }}</small>
            <br><br>
          @if(true)
            <a href="{{ route('topics.posts.replies.edit', [$topic, $post, $childReply]) }}" class="btn">✏️ Edit</a>
            <form action="{{ route('topics.posts.replies.destroy', [$topic, $post, $childReply]) }}" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-red" onclick="return confirm('Delete this reply?')">Delete</button>
            </form>
          @endif
       </div>
     @endforeach
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