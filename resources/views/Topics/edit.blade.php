@extends('layouts.app')

@section('content')
    <h1>Edit Topic</h1>
    <div class="card">
        <form action="{{ route('topics.update', $topic) }}" method="POST">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ $topic->Title }}">
            @error('title')<p style="color:red">{{ $message }}</p>@enderror

            <label>Description</label>
            <textarea name="body" rows="5">{{ $topic->Description }}</textarea>
            @error('body')<p style="color:red">{{ $message }}</p>@enderror

            <button type="submit" class="btn">💾 Save Changes</button>
            <a href="{{ route('topics.show', $topic) }}" class="btn btn-red">Cancel</a>
        </form>
    </div>
@endsection