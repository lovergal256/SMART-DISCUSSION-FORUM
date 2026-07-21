@extends('layouts.admin_app')

@section('title', 'Admin Dashboard — Smart Discussion Forum')

@section('content')
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="page-head">
        <h1>Platform Overview</h1>
        <p>Monitor activity and manage lecturer accounts across the system.</p>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div>
                <div class="stat-num">{{ $totalUsers }}</div>
                <div class="stat-lbl">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div>
                <div class="stat-num">{{ $totalLecturers }}</div>
                <div class="stat-lbl">Lecturers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div>
                <div class="stat-num">{{ $totalStudents }}</div>
                <div class="stat-lbl">Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-num">{{ $totalGroups }}</div>
                <div class="stat-lbl">Groups</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div>
                <div class="stat-num">{{ $totalDiscussions }}</div>
                <div class="stat-lbl">Discussions</div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><span class="ic">⚙️</span> Quick Actions</div>
        </div>
        <a class="btn" href="{{ route('admin.lecturers.create') }}">+ Register New Lecturer</a>
    </div>
@endsection