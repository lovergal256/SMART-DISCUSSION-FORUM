@extends('layouts.lecturer_app')

@section('title', 'Lecturer Dashboard — Smart Discussion Forum')

@section('content')

     <div class="page-head">
        <h1>Welcome back, {{ $user->FullName ?? 'there' }}! 👋</h1>
        <p>Let's continue your learning journey.</p>
    </div>


@endsection
