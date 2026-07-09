<div class="topbar">
    <span class="hamburger">☰</span>

    <form action="{{ route('discussions.index') }}" method="GET" class="search" style="flex:1; max-width:460px;">
       <button type="submit" style="all:unset; cursor:pointer;">🔍</button>
       <input type="text" name="search" placeholder="Search discussions, groups, quizzes..."
           style="all:unset; flex:1;" value="{{ request('search') }}">
     </form>

    <div class="top-actions">
        <a class="icon-link" href="{{ route('notifications.index') }}">
            🔔
            @if($unreadNotifications ?? 0)
                <span class="badge">{{ $unreadNotifications }}</span>
            @endif
        </a>

        <a class="profile-link" href="{{ route('profile.show') }}">
            <div class="avatar-img">{{ $initials ?? 'ST' }}</div>
            <div>
                <div class="profile-name">{{ $user->name ?? 'Student Name' }}</div>
                <div class="profile-role">Student</div>
            </div>
            <span class="chevron">▾</span>
        </a>
    </div>
</div>