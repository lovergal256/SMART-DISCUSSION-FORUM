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
        @include('partials.reply-thread', ['reply' => $reply, 'allReplies' => $replies, 'topic' => $topic, 'post' => $post, 'depth' => 1])
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
            @if(session('error'))
    <div style="background:#fdd; border:1px solid #f99; color:#900; padding:10px; border-radius:4px; margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

            <textarea name="body" rows="4" placeholder="Write your reply...">{{ old('body') }}</textarea>
            @error('body')
                <p style="color:red">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn">Post Reply</button>
        </form>
    </div>
@endsection