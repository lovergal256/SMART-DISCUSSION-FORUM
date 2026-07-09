@extends('layouts.app')

@section('content')
    <h1>Create New Topic</h1>

    <div class="card">
        <form action="{{ route('topics.store') }}" method="POST">
            @csrf
            <input type="hidden" name="discussion_id" value="{{ $discussionId }}">

            <label>Topic Title</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="Enter topic title">
            @error('title')
                <p style="color:red">{{ $message }}</p>
            @enderror

            <label>Topic Description</label>
            <textarea name="body" rows="6" placeholder="Describe your topic...">{{ old('body') }}</textarea>
            @error('body')
                <p style="color:red">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn">Post Topic</button>
            <a href="{{ route('discussions.show', $discussionId) }}" class="btn btn-red">Cancel</a>
        </form>
    </div>
@endsection