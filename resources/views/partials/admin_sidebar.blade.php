<aside class="sidebar">
    <div class="brand">
        <div class="logo">💬</div>
        <div class="name">SMART DISCUSSION<br>FORUM</div>
    </div>
    <nav>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
           <span class="nav-icon">🏠</span> Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('admin.lecturers.*') ? 'active' : '' }}" href="{{ route('admin.lecturers.create') }}">
            <span class="nav-icon">➕</span> Register Lecturer
        </a>
        <a class="nav-link {{ request()->routeIs('groups.*') ? 'active' : '' }}" href="{{ route('groups.index') }}">
            <span class="nav-icon">👥</span> View Groups
        </a>
        <a class="nav-link {{ request()->routeIs('discussions.*') ? 'active' : '' }}" href="{{ route('discussions.index') }}">
            <span class="nav-icon">💬</span> View Discussions
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a class="nav-link" href="{{ route('logout') }}"
               onclick="event.preventDefault(); this.closest('form').submit();">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </form>
    </nav>
</aside>