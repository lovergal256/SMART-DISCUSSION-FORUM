@extends('layouts.app')

@section('content')
    <h1>Add Post to: {{ $topic->title }}</h1>

    <div class="card">
        <form action="{{ route('topics.posts.store', $topic->TopicID) }}" method="POST">
            @csrf

            <label>Your Post</label>
            <textarea name="body" rows="6" placeholder="Write your post...">{{ old('body') }}</textarea>
            @error('body')
                <p style="color:red">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn">Submit Post</button>
            <a href="{{ route('topics.show', $topic->TopicID) }}" class="btn btn-red">Cancel</a>
        </form>
    </div>
@endsection
