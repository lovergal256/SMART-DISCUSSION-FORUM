@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Profile</h1>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <p><strong>Full Name:</strong> {{ $user->FullName }}</p>
        <p><strong>Email:</strong> {{ $user->Email }}</p>
        <p><strong>Role:</strong> {{ ucfirst($user->roleName) }}</p>
        <p><strong>Role ID:</strong> {{ $user->RoleID }}</p>
        <p><strong>Date Joined:</strong> {{ \Carbon\Carbon::parse($user->DateJoined)->format('M d, Y') }}</p>
        <p><strong>Theme:</strong> {{ ucfirst($user->Theme) }}</p>
    </div>

    <div class="card">
        <h2>Edit Profile</h2>
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            <label for="FullName">Full Name</label>
            <input type="text" name="FullName" id="FullName" value="{{ old('FullName', $user->FullName) }}" required>

            <label for="Theme">Theme</label>
            <select name="Theme" id="Theme">
                <option value="light" {{ $user->Theme === 'light' ? 'selected' : '' }}>Light</option>
                <option value="dark" {{ $user->Theme === 'dark' ? 'selected' : '' }}>Dark</option>
            </select>

            <button type="submit" class="btn">Save Changes</button>
        </form>
    </div>
    <div class="card">
        <h2>Change Password</h2>
        <form action="{{ route('profile.changePassword') }}" method="POST">
            @csrf
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" id="current_password" required>

            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" required>

            <label for="new_password_confirmation">Confirm New Password</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" required>

            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>

    <div class="card" style="border-left-color:#c0392b;">
        <h2 style="color:#c0392b;">Delete Account</h2>
        <p>This action is permanent and cannot be undone.</p>
        <form action="{{ route('profile.delete') }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <label for="delete_confirm_password">Enter your password to confirm</label>
            <input type="password" name="delete_confirm_password" id="delete_confirm_password" required>

            <button type="submit" class="btn btn-red">Delete My Account</button>
        </form>
    </div>
</div>
@endsection