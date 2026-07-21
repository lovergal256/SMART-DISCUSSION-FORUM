@extends('layouts.admin_app')

@section('title', 'Register Lecturer — Smart Discussion Forum')

@section('content')
    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="page-head">
        <h1>Register New Lecturer</h1>
        <p>Create a lecturer account. Lecturers cannot self-register — only admins can create these accounts.</p>
    </div>

    <div class="panel">
        <form method="POST" action="{{ route('admin.lecturers.store') }}">
            @csrf

            <label for="FullName">Full Name</label>
            <input type="text" id="FullName" name="FullName" value="{{ old('FullName') }}" required>

            <label for="Email">Email</label>
            <input type="email" id="Email" name="Email" value="{{ old('Email') }}" required>

            <label for="Password">Password</label>
            <input type="password" id="Password" name="Password" required>

            <label for="Password_confirmation">Confirm Password</label>
            <input type="password" id="Password_confirmation" name="Password_confirmation" required>

            <button type="submit" class="btn">Create Lecturer Account</button>
            <a class="view-all" href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>
        </form>
    </div>
@endsection