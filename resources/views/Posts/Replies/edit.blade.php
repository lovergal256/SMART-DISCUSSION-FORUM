@extends('layouts.app')

@section('content')
    <h2>Edit Reply</h2>
    <div class="card">
        <form action="{{ route('topics.posts.replies.update', [$topic, $post, $reply]) }}" method="POST">
            @csrf
            @method('PUT')
            <label>Your Reply</label>
            <textarea name="body" rows="4">{{ $reply->Body }}</textarea>
            @error('body')<p style="color:red">{{ $message }}</p>@enderror
            <button type="submit" class="btn">💾 Save Changes</button>
            <a href="{{ route('topics.posts.show', [$topic, $post]) }}" class="btn btn-red">Cancel</a>
        </form>
    </div>
@endsection