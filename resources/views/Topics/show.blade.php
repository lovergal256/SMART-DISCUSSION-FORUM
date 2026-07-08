@extends('layouts.app')

@section('content')
    <h1>{{ $topic->Title }}</h1>
    <div class="card">
      <p><strong>Topic Description </strong> <br>{{ $topic->Description }}</p>
        <small>Posted by {{ $topic->user->FullName ?? 'Unknown' }}</small>
      
         @if(true)
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
                @foreach($post->replies()->with('user')->get()->where('ParentReplyID', null) as $reply)
                   <div class="card" style="margin-left: 40px; margin-top:10px;">
                     <p>{{ $reply->Body }}</p>
                     <small style="color: #023e8a; font-weight:600;">By {{ $reply->user->FullName ?? 'Unknown' }}</small>
                     <small style="color:#888;">· {{ $reply->created_at?->diffForHumans() }}</small>

                    {{-- Nested replies --}}
                      @foreach($post->replies()->with('user')->get()->where('ParentReplyID', $reply->ReplyID) as $childReply)
                        <div class="card" style="margin-left: 60px; margin-top:10px;">
                            <p>{{ $childReply->Body }}</p>
                            <small style="color: #023e8a; font-weight:600;">By {{ $childReply->user->FullName ?? 'Unknown' }}</small>
                            <small style="color:#888;">· {{ $childReply->created_at?->diffForHumans() }}</small>
                        </div>
                      @endforeach
                    </div>
                @endforeach

                @if($post->replies()->count() === 0)
                  <p style="margin-left:40px; color:#888;">No replies yet.</p>
                @endif
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
    </script>


@endsection
