@extends('layouts.app')

@section('content')

    <a href="{{ route('discussions.index') }}" class="btn" style="margin-bottom:15px; display:inline-block;">
        ← Back to discussions
    </a>

    <div class="card">
        <h1>{{ $discussion->Title }}</h1>
        <small>Started by {{ $discussion->user->FullName ?? 'Unknown' }}</small>
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

@endsection