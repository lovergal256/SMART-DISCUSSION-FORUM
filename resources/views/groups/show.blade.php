@extends($layout)

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
    <small style="color:#888;">Visibility: <strong>{{ ucfirst($group->Visibility) }}</strong></small>
    @if($isAdmin)
        <form method="POST" action="{{ route('groups.toggleVisibility', $group->GroupID) }}" style="margin-top:10px;">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn" style="font-size:0.85em;">
                {{ $group->Visibility === 'public' ? 'Make Private' : 'Make Public' }}
            </button>
        </form>
    @endif
   <div style="display:flex; justify-content:flex-end; align-items:center; gap:10px; margin-top:15px;">

    @can('manage-quizzes')
        <a href="{{ route('quizzes.create', ['group' => $group->GroupID]) }}" class="btn">+ Create Quiz</a>
    @endcan

    @if($isMember)
        <a href="{{ route('discussions.create', ['group' => $group->GroupID]) }}" class="btn">+ Start a Discussion</a>
    @elseif($hasPendingRequest)
        <span style="font-size:0.9em; color:#666;">Your request to join is pending admin approval.</span>
    @else
        <form method="POST" action="{{ route('groups.requestJoin', $group->GroupID) }}">
            @csrf
            <button type="submit" class="btn">Request to Join</button>
        </form>
    @endif
</div>
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
            <strong>
    {{ $member->FullName }}
    {{ $member->UserID == $group->CreatedBy ? ' (Creator)' : '' }}
    {{ $member->UserID == auth()->id() ? ' (me)' : '' }}
</strong>
            <div style="font-size:0.85em; color:#666;">{{ $member->Email }}</div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:0.8em; padding:2px 8px; background:#d0e8f5; border-radius:4px;">
                {{ $member->pivot->Role }}
            </span>
            @php
                $authRole = $members->firstWhere('UserID', auth()->id())?->pivot->Role;
            @endphp
            @if($authRole === 'admin' && auth()->user()->UserID !== $member->UserID)
                @if($member->pivot->Role !== 'admin')
                    <form method="POST" action="{{ route('groups.promote', [$group->GroupID, $member->UserID]) }}">
                        @csrf
                        <button type="submit"
                                style="font-size:0.8em; padding:2px 8px; background:#0077b6; color:white; border:none; border-radius:4px; cursor:pointer;">
                            Promote
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('groups.removeMember', [$group->GroupID, $member->UserID]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Remove {{ $member->FullName }} from this group?')"
                            style="font-size:0.8em; color:red; background:none; border:none; cursor:pointer;">
                        Remove
                    </button>
                </form>
                <form method="POST" action="{{ route('groups.members.blacklist', [$group->GroupID, $member->UserID]) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Blacklist {{ $member->FullName }} for one month? They will be unable to use the platform.')"
                            style="font-size:0.8em; color:#b91c1c; background:#fee2e2; border:none; border-radius:4px; padding:2px 8px; cursor:pointer;">
                        Blacklist
                    </button>
                </form>
            @endif
        </div>
    </div>
@endforeach
        @endif
    </div>

    {{-- Add Member --}}
    @if($isAdmin)
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
    @endif 

    {{-- Pending Requests (admin only) --}}
@if($isAdmin)
    <div class="card">
        <h3>Pending Requests ({{ $pendingRequests->count() }})</h3>
        @if($pendingRequests->isEmpty())
            <p>No pending requests.</p>
        @else
            @foreach($pendingRequests as $request)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #eee;">
                    <div>
                        <strong>{{ $request->FullName }}</strong>
                        <div style="font-size:0.85em; color:#666;">{{ $request->Email }}</div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <form method="POST" action="{{ route('groups.approve', [$group->GroupID, $request->UserID]) }}">
                            @csrf
                            <button type="submit" class="btn">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('groups.reject', [$group->GroupID, $request->UserID]) }}">
                            @csrf
                            <button type="submit" class="btn btn-red">Reject</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endif

{{-- Discussions --}}
<div class="card">
    <h3>Discussions ({{ $discussions->count() }})</h3>
    @if($discussions->isEmpty())
        <p>No discussions started yet.</p>
    @else
        @foreach($discussions as $discussion)
            <div style="padding:8px 0; border-bottom:1px solid #eee;">
                <a href="{{ route('discussions.show', $discussion) }}">
                    {{ $discussion->Title }}
                </a>
            </div>
        @endforeach
    @endif
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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
    <a href="{{ route('groups.index') }}">← Back to Groups</a>

    <div style="display:flex; gap:10px;">
        @if($isMember)
            <form action="{{ route('groups.leave', $group->GroupID) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this group?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-red">Exit Group</button>
            </form>
        @endif

        @if($isAdmin)
            <form action="{{ route('groups.destroy', $group->GroupID) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this group? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-red">Delete Group</button>
            </form>
        @endif
    </div>
</div>
</div>
@endsection