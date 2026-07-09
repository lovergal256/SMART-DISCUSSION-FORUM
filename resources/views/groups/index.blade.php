@extends('layouts.app')

@section('title', 'Groups')

@section('content')
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>All Groups</h2>
        <a href="{{ route('groups.create') }}" class="btn">+ New Group</a>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($groups->isEmpty())
        <div class="card">
            <p>No groups yet.</p>
        </div>
    @else
        @foreach($groups as $group)
            <div class="card">
                <h3><a href="{{ route('groups.show', $group->GroupID) }}">{{ $group->GroupName }}</a></h3>
                <p>{{ $group->Description ?? 'No description' }}</p>
            </div>
        @endforeach
    @endif
</div>
@endsection