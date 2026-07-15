@php
    $currentUser = auth()->user();
    $currentInitials = $currentUser
        ? collect(explode(' ', $currentUser->FullName ?? ''))->map(fn($w) => $w[0] ?? '')->take(2)->implode('')
        : 'ST';
    $currentUnread = $currentUser
        ? \App\Models\Notification::where('UserID', $currentUser->UserID)->where('Status', 'Unread')->count()
        : 0;
@endphp
<div class="topbar">
    <span class="hamburger">☰</span>

    <form action="{{ route('discussions.index') }}" method="GET" class="search" style="flex:1; max-width:460px;">
       <button type="submit" style="all:unset; cursor:pointer;">🔍</button>
       <input type="text" name="search" placeholder="Search discussions, groups, quizzes..."
           style="all:unset; flex:1;" value="{{ request('search') }}">
     </form>

    <div class="top-actions">
        <a class="icon-link {{ request()->routeIs('lecturer.notifications.*') ? 'active' : '' }}"
   href="{{ route('lecturer.notifications.index') }}">
            🔔
            @if(($unreadNotifications ?? 0) > 0)
    <span class="badge bg-danger float-end">
        {{ $unreadNotifications }}
    </span>
@endif
        </a>

        <a class="profile-link" href="{{ route('profile.show') }}">
            <div class="avatar-img">{{ $currentInitials ?: 'ST' }}</div>
            <div>
                <div class="profile-name">{{ $currentUser->FullName ?? 'Lecturer Name' }}</div>
                <div class="profile-role">Lecturer</div>
            </div>
            <span class="chevron">▾</span>
        </a>
    </div>
</div>
