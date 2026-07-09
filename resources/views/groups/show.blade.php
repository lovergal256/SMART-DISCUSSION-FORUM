@extends('layouts.app')

@section('title', $group->GroupName)

@section('content')
<div class="container">
    <h2>{{ $group->GroupName }}</h2>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    {{-- Group Info --}}
    <div class="card">
        <h3>About this group</h3>
        <p>{{ $group->Description ?? 'No description provided.' }}</p>
    </div>

    {{-- Members --}}
    <div class="card">
        <h3>Members ({{ $members->count() }})</h3>
        @if($members->isEmpty())
            <p>No members yet.</p>
        @else
            @foreach($members as $member)
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                    <div>
                        <strong>{{ $member->FullName }}</strong>
                        <div style="font-size:0.85em; color:#666;">{{ $member->Email }}</div>
                    </div>
                    <span style="font-size:0.8em; padding:2px 8px; background:#d0e8f5; border-radius:4px;">
                        {{ $member->pivot->Role }}
                    </span>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Add Member --}}
    <div class="card">
        <h3>Add a Member</h3>
        <form method="POST" action="{{ route('groups.addMember', $group->GroupID) }}">
            @csrf
            <label>User ID</label>
            <input type="number" name="user_id" placeholder="Enter User ID">
            @error('user_id')
                <p style="color:red;">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn">Add Member</button>
        </form>
    </div>

    {{-- Exclusions --}}
    <div class="card">
        <h3>My Exclusions</h3>
        <p style="font-size:0.9em; color:#666; margin-bottom:10px;">Excluded members will not see your posts in this group.</p>

        <form method="POST" action="{{ route('exclusions.store', $group->GroupID) }}">
            @csrf
            <label>Exclude User by ID</label>
            <input type="number" name="excluded_user_id" placeholder="Enter User ID to exclude">
            @error('excluded_user_id')
                <p style="color:red;">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn btn-red">Exclude</button>
        </form>

        @php
            $myExclusions = $group->exclusions()->where('UserID', auth()->id())->with('excludedUser')->get();
        @endphp

        @if($myExclusions->isNotEmpty())
            <div style="margin-top:15px;">
                @foreach($myExclusions as $exclusion)
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
                        <div>
                            <strong>{{ $exclusion->excludedUser->FullName }}</strong>
                            <div style="font-size:0.85em; color:#666;">{{ $exclusion->excludedUser->Email }}</div>
                        </div>
                        <form method="POST" action="{{ route('exclusions.destroy', [$group->GroupID, $exclusion->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <a href="{{ route('groups.index') }}">← Back to Groups</a>
</div>
@endsection