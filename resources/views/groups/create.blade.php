@extends('layouts.app')

@section('title', 'Create Group')

@section('content')
<div class="container">
    <h2>Create New Group</h2>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('groups.store') }}">
            @csrf
            <label>Group Name</label>
            <input type="text" name="GroupName" value="{{ old('GroupName') }}" required>

            <label>Description</label>
            <textarea name="Description" rows="3">{{ old('Description') }}</textarea>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <a href="{{ route('groups.index') }}">← Back to Groups</a>
                <button type="submit" class="btn">Create Group</button>
            </div>
        </form>
    </div>
</div>
@endsection