@extends('layouts.app')

@section('content')
    <h2>Manage Exclusions for: {{ $topic->Title }}</h2>
    <a href="{{ route('topics.show', $topic) }}" class="btn">← Back to Topic</a>
    <br><br>

    @if($exclusions->isEmpty())
        <div class="card">
            <p>No exclusions set for this topic.</p>
        </div>
    @else
        @foreach($exclusions as $exclusion)
            <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>{{ $exclusion->excludedUser->FullName ?? 'Unknown' }}</strong>
                    <small style="color:#888;"> excluded from Post ID: {{ $exclusion->ContentID }}</small>
                    <br>
                    <small style="color:#888;">Excluded on: {{ $exclusion->ExclusionDate }}</small>
                </div>
                <form action="{{ route('exclusions.destroy', [$topic, $exclusion->ContentID, $exclusion->ExcludedUserID]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-red" onclick="return confirm('Remove this exclusion?')">Remove</button>
                </form>
            </div>
        @endforeach
    @endif
@endsection