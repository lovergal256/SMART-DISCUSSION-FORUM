@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-head">
        <h1>🔔 Notifications</h1>
        <p>Stay updated with your latest notifications.</p>
    </div>

    @forelse($notifications as $notification)

        <div class="card mb-2">
            <div class="card-body">

                <h5>
    @switch($notification->Type)
        @case('group_join_request')
            Join Group Request
            @break
        @default
            {{ ucwords(str_replace('_', ' ', $notification->Type)) }}
    @endswitch
</h5>

<p>
    @if($notification->Type === 'group_join_request' && preg_match('/^(.*requested to join )(.*)(\.)$/', $notification->Message, $m))
        {{ $m[1] }}<strong>{{ $m[2] }}</strong>{{ $m[3] }}
    @else
        {{ $notification->Message }}
    @endif
</p>
                <small>
                    {{ $notification->created_at->diffForHumans() }}
                </small>

                @if($notification->Status == 'Unread')

                <form method="POST"
                      action="{{ route('student.notifications.read',$notification->NotificationID) }}">

                    @csrf

                    <button class="btn btn-primary btn-sm">
                        Mark as Read
                    </button>

                </form>

                @else

                    <span class="badge bg-success">
                        Read
                    </span>

                @endif

            </div>
        </div>

    @empty

        <p>No notifications found.</p>

    @endforelse

</div>

@endsection
