@extends('layouts.app')

@section('content')

    <a href="{{ route('discussions.index') }}" class="btn" style="margin-bottom:15px; display:inline-block;">
        ← Back to discussions
    </a>
    <a href="{{ route('discussions.exportPdf', $discussion->DiscussionID) }}" class="btn" style="margin-bottom:15px; margin-left:10px; display:inline-block;">
        📄 Export to PDF
    </a>
    <button onclick="shareDiscussion()" class="btn" style="margin-bottom:15px; margin-left:10px;">
      🔗 Share Discussion
    </button>
    <div class="card">
        <h1>{{ $discussion->Title }}</h1>
        <small>Started by {{ $discussion->user->FullName ?? 'Unknown' }} in <strong>{{ $discussion->group->GroupName ?? 'No Group' }}</strong></small>
        <p>{{ $discussion->Description }}</p>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin: 20px 0 10px;">
        <h2>Topics</h2>
        <a href="{{ route('topics.create', ['discussion' => $discussion->DiscussionID]) }}" class="btn">
            + Add Topic
        </a>
    </div>

    @forelse($topics as $topic)
        <a href="{{ route('discussions.topics.show', [$discussion->DiscussionID, $topic->TopicID]) }}" style="text-decoration:none; color:inherit;">
            <div class="card">
                <h3>{{ $topic->Title }} <small>({{ ucfirst($topic->Status) }})</small></h3>
                <p>{{ $topic->Description }}</p>
                <small>👤 {{ $topic->user->FullName ?? 'Unknown' }} · 💬 {{ $topic->posts()->count() }} {{ Str::plural('post', $topic->posts()->count()) }}</small>
            </div>
        </a>
    @empty
        <div class="card">
            <p>No topics in this discussion yet.</p>
        </div>
    @endforelse

    <script>
    function shareDiscussion() {
        const shareData = {
            title: @json($discussion->Title),
            text: 'Check out this discussion: {{ $discussion->Title }}',
            url: '{{ route('discussions.show', $discussion->DiscussionID) }}'
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