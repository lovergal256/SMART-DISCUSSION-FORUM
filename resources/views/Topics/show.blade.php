@extends('layouts.app')

@section('content')
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>{{ $topic->Title }}</h1>
        <button onclick="shareTopic()" class="btn">🔗 Share Topic</button>
    </div>

      <div class="card">
      <p><strong>Topic Description </strong> <br>{{ $topic->Description }}</p>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
            <small>Posted by {{ $topic->user->FullName ?? 'Unknown' }}</small>

            @if($canManageTopic)
                <div>
                    <a href="{{ route('topics.edit', $topic) }}" class="btn">Edit</a>
                    <form action="{{ route('topics.destroy', $topic) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" onclick="return confirm('Delete this topic?')">Delete</button>
                    </form>
                </div>
            @endif
        </div>
    </div> 

      <h2>Posts</h2>
      <a href="{{ route('topics.posts.create', $topic->TopicID) }}" class="btn">+ Add Post</a>
      <br><br>

    @forelse($posts as $post)
        <div class="card" style="margin-left: 20px;">
          <p style="color: #4a0080;">{{ $post->content }}</p>
          <small>Posted by {{ $post->user->FullName ?? 'Unknown' }} · {{ $post->created_at?->diffForHumans() }}</small>
          <br><br>

        {{-- Reply form --}}
            <form action="{{ route('topics.posts.replies.store', [$topic->TopicID, $post->PostID]) }}" method="POST">
               @csrf
               <textarea name="body" rows="2" placeholder="Write a reply..."></textarea>
               <button type="submit" class="btn">↩ Reply</button>
            </form>

        {{-- Toggle replies button --}}
            <button onclick="toggleReplies('replies-{{ $post->PostID }}')" class="btn" style="margin-top:10px;">
             💬 View Replies ({{ $post->replies()->count() }})
            </button>

          {{-- Replies hidden by default --}}
          <div id="replies-{{ $post->PostID }}" style="display:none; margin-top:10px;">
                @php
                    $allReplies = $post->replies()->with('user')->get();
                @endphp
                @forelse($allReplies->where('ParentReplyID', null) as $reply)
                    @include('partials.reply-thread-simple', ['reply' => $reply, 'allReplies' => $allReplies, 'depth' => 1, 'topic' => $topic, 'post' => $post])
                @empty
                    <p style="margin-left:40px; color:#888;">No replies yet.</p>
                @endforelse
            </div>
        </div>
    @empty 

       <div class="card">
         <p>No posts yet. Be the first to post!</p>
       </div>
    @endforelse

    <script>
       function toggleReplies(id) {
       var div = document.getElementById(id);
       if(div.style.display === 'none') {
        div.style.display = 'block';
       } else {
        div.style.display = 'none';
       }
       }

       function shareTopic() {
    const shareData = {
        title: @json($topic->Title),
        text: 'Check out this topic: {{ $topic->Title }}',
        url: '{{ route('discussions.topics.show', [$topic->DiscussionID, $topic->TopicID]) }}'
    };

    if (navigator.share) {
        navigator.share(shareData).catch((err) => console.log('Share cancelled or failed:', err));
    } else if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(shareData.url).then(() => {
            alert('Link copied to clipboard!');
        }).catch((err) => {
            console.log('Clipboard failed:', err);
            fallbackCopy(shareData.url);
        });
    } else {
        fallbackCopy(shareData.url);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        document.execCommand('copy');
        alert('Link copied to clipboard!');
    } catch (err) {
        prompt('Copy this link:', text);
    }
    document.body.removeChild(textarea);
}
       
    </script>


@endsection