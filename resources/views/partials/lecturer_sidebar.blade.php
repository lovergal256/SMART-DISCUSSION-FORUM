<aside class="sidebar">
    <div class="brand">
        <div class="logo">💬</div>
        <div class="name">SMART DISCUSSION<br>FORUM</div>
    </div>

    <nav>
        <a class="nav-link {{ request()->routeIs('lecturer.dashboard') ? 'active' : '' }}" href="{{ route('lecturer.dashboard') }}">
           <span class="nav-icon">🏠</span> Dashboard
        </a>
        <!-- <a class="nav-link {{ request()->routeIs('discussions.*') ? 'active' : '' }}" href="{{ route('discussions.create') }}">
            <span class="nav-icon">➕</span> Create Discussion
        </a> -->
        <a class="nav-link {{ request()->routeIs('groups.*') ? 'active' : '' }}" href="{{ route('groups.index') }}">
            <span class="nav-icon">👥</span> My Groups
        </a>
        <a class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}" href="{{ route('performance.index') }}">
            <span class="nav-icon">📊</span> Performance
        </a>
        <!-- <a class="nav-link {{ request()->routeIs('recommendations.*') ? 'active' : '' }}" href="{{ route('recommendations.index') }}">
            <span class="nav-icon">⭐</span> Recommendations
        </a> -->
        <!-- <a class="nav-link {{ request()->routeIs('warnings.*') ? 'active' : '' }}" href="{{ route('warnings.index') }}">
            <span class="nav-icon">⚠</span> Warnings
        </a> -->
        <a class="nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}" href="{{ route('activity.index') }}">
            <span class="nav-icon">📈</span> My Activity
        </a>

        <div class="nav-divider"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a class="nav-link" href="{{ route('logout') }}"
               onclick="event.preventDefault(); this.closest('form').submit();">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </form>
    </nav>
</aside>