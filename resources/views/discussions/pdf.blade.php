<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { color: #666; font-size: 11px; margin-bottom: 15px; }
        .discussion-desc { margin-bottom: 20px; }
        .topic { margin-top: 20px; padding-top: 10px; border-top: 2px solid #333; }
        .topic h2 { font-size: 15px; margin-bottom: 2px; }
        .topic-meta { color: #666; font-size: 10px; margin-bottom: 8px; }
        .post { margin: 10px 0 10px 10px; padding: 8px; background: #f7f7f7; border-left: 3px solid #0077b6; }
        .post-meta { color: #555; font-size: 10px; margin-bottom: 4px; }
        .reply { margin: 6px 0 6px 25px; padding: 6px; background: #eef; border-left: 2px solid #999; }
        .reply-meta { color: #555; font-size: 9px; margin-bottom: 2px; }
        .none { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>{{ $discussion->Title }}</h1>
    <div class="meta">
        Started by {{ $discussion->user->FullName ?? 'Unknown' }}
        in {{ $discussion->group->GroupName ?? 'No Group' }}
    </div>
    <div class="discussion-desc">{{ $discussion->Description }}</div>

    @forelse($discussion->topics as $topic)
        <div class="topic">
            <h2>{{ $topic->Title }} ({{ ucfirst($topic->Status) }})</h2>
            <div class="topic-meta">By {{ $topic->user->FullName ?? 'Unknown' }}</div>
            <p>{{ $topic->Description }}</p>

            @forelse($topic->posts as $post)
                <div class="post">
                    <div class="post-meta">
                        {{ $post->user->FullName ?? 'Unknown' }} &middot; {{ $post->DatePosted }}
                    </div>
                    <div>{{ $post->Content }}</div>

                    @foreach($post->replies as $reply)
                        <div class="reply">
                            <div class="reply-meta">
                                {{ $reply->user->FullName ?? 'Unknown' }} &middot; {{ $reply->DateCreated }}
                            </div>
                            <div>{{ $reply->Body }}</div>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="none">No posts in this topic.</p>
            @endforelse
        </div>
    @empty
        <p class="none">No topics in this discussion.</p>
    @endforelse
</body>
</html>