@extends('layouts.lecturer_app')

@section('content')

<div class="container">

    <div class="page-head">
        <h1>🔔 Lecturer Notifications</h1>
        <p>View notifications, student requests, and system updates.</p>
    </div>

    @forelse($notifications as $notification)

        <div class="card mb-3 shadow-sm">

            <div class="card-body">

                <h5>
                    @switch($notification->Type)

                        @case('group_join_request')
                            👥 Group Join Request
                            @break

                        @case('quiz_submission')
                            📝 Quiz Submission
                            @break

                        @case('new_discussion_reply')
                            💬 Discussion Reply
                            @break

                        @default
                            {{ ucwords(str_replace('_', ' ', $notification->Type)) }}

                    @endswitch
                </h5>

                <p>
                    @if($notification->Type == 'group_join_request'
                        && preg_match('/^(.*requested to join )(.*)(\.)$/', $notification->Message, $m))

                        {{ $m[1] }}
                        <strong>{{ $m[2] }}</strong>
                        {{ $m[3] }}

                    @else

                        {{ $notification->Message }}

                    @endif
                </p>

                <small class="text-muted">
                    {{ $notification->created_at->diffForHumans() }}
                </small>

                <hr>

                @if($notification->Status == 'Unread')

                    <form method="POST"
                          action="{{ route('lecturer.notifications.read', $notification->NotificationID) }}">
                        @csrf

                        <button class="btn btn-primary btn-sm">
                            ✓ Mark as Read
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

        <div class="alert alert-info">
            No notifications available.
        </div>

    @endforelse

</div>

@endsection
