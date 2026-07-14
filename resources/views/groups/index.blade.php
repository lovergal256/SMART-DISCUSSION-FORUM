@extends('layouts.app')

@section('title', 'Groups')

@section('content')
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>All Groups</h2>
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

    @if($groups->isEmpty())
        <div class="card">
            @if($search)
                <p>No groups found matching "{{ $search }}".</p>
            @else
                <p>No groups yet.</p>
            @endif
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

  