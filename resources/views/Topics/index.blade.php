@extends('layouts.app')

@section('content')
    <h1>All Topics</h1>
    <a href="{{ route('topics.create') }}" class="btn">+ New Topic</a>
    <br><br>

    @forelse($topics as $topic)
        <div class="card">
            <h3>
                <a href="{{ route('topics.show', $topic->id) }}">{{ $topic->title }}</a>
            </h3>
            <p>{{ Str::limit($topic->body, 100) }}</p>
            <small>Posted by {{ $topic->user->name ?? 'Unknown' }} </small>
        </div>
    @empty
        <div class="card">
            <p>No topics yet. Be the first to create one!</p>
        </div>
    @endforelse

    {{ $topics->links() }}
@endsection
