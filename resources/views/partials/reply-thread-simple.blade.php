<div class="card" style="margin-left: {{ min($depth * 20, 100) }}px; margin-top:10px;">
    <p>{{ $reply->Body }}</p>
    <small style="color: #023e8a; font-weight:600;">By {{ $reply->user->FullName ?? 'Unknown' }}</small>
    <small style="color:#888;"> · {{ $reply->created_at?->diffForHumans() }}</small>

@if($reply->UserID === auth()->id())
    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
        <a href="{{ route('topics.posts.replies.edit', [$topic, $post, $reply]) }}" class="btn">✏️ Edit</a>
        <form action="{{ route('topics.posts.replies.destroy', [$topic, $post, $reply]) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-red" onclick="return confirm('Delete this reply?')">Delete</button>
        </form>
    </div>
@endif

    @php
        $children = $allReplies->where('ParentReplyID', $reply->ReplyID);
    @endphp

    @foreach($children as $child)
        @include('partials.reply-thread-simple', ['reply' => $child, 'allReplies' => $allReplies, 'depth' => $depth + 1, 'topic' => $topic, 'post' => $post])
    @endforeach
</div>

