@extends('layouts.app')

@section('content')
    <h1>Create New Discussion</h1>

    <div class="card">
        <form action="{{ route('discussions.store') }}" method="POST">
            @csrf

        @if($group)
                <input type="hidden" name="GroupID" value="{{ $group->GroupID }}">
                <p>Posting in: <strong>{{ $group->GroupName }}</strong></p>
            @else
                <label>Group</label>
                <select name="GroupID" required>
                    <option value="">Select a group...</option>
                    @foreach(\App\Models\Group::whereHas('members', function ($q) {
                        $q->where('group_members.UserID', auth()->id())
                          ->where('group_members.Status', 'approved');
                    })->get() as $userGroup)
                        <option value="{{ $userGroup->GroupID }}" {{ old('GroupID') == $userGroup->GroupID ? 'selected' : '' }}>
                            {{ $userGroup->GroupName }}
                        </option>
                    @endforeach
                </select>
                @error('GroupID')
                    <p style="color:red">{{ $message }}</p>
                @enderror
            @endif

            <label>Title</label>
            <input type="text" name="Title" value="{{ old('Title') }}" placeholder="Enter discussion title">
            @error('Title')
                <p style="color:red">{{ $message }}</p>
            @enderror

            <label>Description</label>
            <textarea name="Description" rows="6" placeholder="Describe your discussion...">{{ old('Description') }}</textarea>
            @error('Description')
                <p style="color:red">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn">Create Discussion</button>
            <a href="{{ $group ? route('groups.show', $group->GroupID) : route('discussions.index') }}" class="btn btn-red">Cancel</a>
        </form>
    </div>
@endsection