@extends('layouts.app')

@section('title', 'Groups')

@section('content')
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Groups</h2>
        <a href="{{ route('groups.create') }}" class="btn">+ New Group</a>
    </div>

    {{-- Search form --}}
    <div class="card" style="padding:12px 20px;">
        <form method="GET" action="{{ route('groups.index') }}" style="display:flex; gap:10px;">
            <input type="text"
                   name="search"
                   value="{{ $search ?? '' }}"
                   placeholder="Search groups..."
                   style="flex:1; margin:0;">
            <button type="submit" class="btn">Search</button>
            @if($search)
                <a href="{{ route('groups.index') }}" class="btn" style="background:#666;">Clear</a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    {{-- My Groups --}}
    <h3 style="margin:20px 0 10px; color:#0077b6;">My Groups</h3>
    @if($groups->isEmpty())
        <div class="card">
            <p>You are not a member of any groups yet.</p>
        </div>
    @else
        @foreach($groups as $group)
            <div class="card">
                <h3><a href="{{ route('groups.show', $group->GroupID) }}">{{ $group->GroupName }}</a></h3>
                <p>{{ $group->Description ?? 'No description' }}</p>
                <small style="color:#888;">{{ $group->members()->wherePivot('Status','approved')->count() }} members</small>
            </div>
        @endforeach
    @endif

    {{-- Discover Groups --}}
    @if($discoverGroups->isNotEmpty())
        <h3 style="margin:30px 0 10px; color:#0077b6;">Discover Groups</h3>
        @foreach($discoverGroups as $group)
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 style="margin:0;">{{ $group->GroupName }}</h3>
                        <p style="margin:4px 0;">{{ $group->Description ?? 'No description' }}</p>
                        <small style="color:#888;">{{ $group->members()->wherePivot('Status','approved')->count() }} members</small>
                    </div>
                    <form method="POST" action="{{ route('groups.requestJoin', $group->GroupID) }}">
                        @csrf
                        <button type="submit" class="btn">Request to Join</button>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection