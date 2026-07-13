@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Lecturer Registration</h4>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('lecturer.register.store') }}">
                        @csrf

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>

                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required>

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                required>

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Employee Number -->
                        <div class="mb-3">
                            <label class="form-label">Employee Number</label>

                            <input
                                type="text"
                                name="employee_no"
                                class="form-control @error('employee_no') is-invalid @enderror"
                                value="{{ old('employee_no') }}"
                                required>

                            @error('employee_no')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Department -->
                        <div class="mb-3">
                            <label class="form-label">Department</label>

                            <input
                                type="text"
                                name="department"
                                class="form-control @error('department') is-invalid @enderror"
                                value="{{ old('department') }}"
                                required>

                            @error('department')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Password</label>

                            <input
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Register Lecturer
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>
</div>

@endsection
