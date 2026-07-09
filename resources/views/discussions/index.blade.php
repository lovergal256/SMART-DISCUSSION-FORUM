@extends('layouts.app')

@section('content')
    <h1>Discussions</h1>

    <div class="card">
        <form method="GET" action="{{ route('discussions.index') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search discussions...">
            <button type="submit" class="btn">Search</button>
        </form>
    </div>

    @forelse($discussions as $discussion)
        <a href="{{ route('discussions.show', $discussion->DiscussionID) }}" style="text-decoration:none; color:inherit;">
            <div class="card">
                <h3>{{ $discussion->Title }}</h3>
                <p>{{ $discussion->Description }}</p>
                <small>👤 {{ $discussion->user->FullName ?? 'Unknown' }} · 🗂️ {{ $discussion->topics()->count() }} {{ Str::plural('topic', $discussion->topics()->count()) }}</small>
            </div>
        </a>
    @empty
        <div class="card">
            <p>No discussions found.</p>
        </div>
    @endforelse
@endsection