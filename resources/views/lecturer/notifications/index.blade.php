@extends('layouts.lecturer_app')

@section('content')

<div class="container">
    <div class="page-head">
        <h1>🔔 Notifications</h1>
        <p>Stay updated with your latest notifications.</p>
    </div>

    @forelse($notifications as $notification)

        <div class="card mb-2">
            <div class="card-body">

                <h5>{{ $notification->Type }}</h5>
                <p>{{ $notification->Message }}</p>

@if($notification->Status == 'Unread')

<form method="POST"
      action="{{ route('notifications.read', $notification->NotificationID) }}">
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
