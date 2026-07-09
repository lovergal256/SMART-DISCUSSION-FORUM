<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $email = $request->input('email', $request->input('Email'));
        $password = $request->input('password', $request->input('Password'));

        $request->validate([
            'Email' => 'nullable|email',
            'email' => 'nullable|email',
            'Password' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        if (empty($email) || empty($password)) {
            return back()->withErrors(['Email' => 'Email and password are required.']);
        }

       $user = User::query()->where('Email', $email)->first();

        if (! $user || ! Hash::check($password, $user->Password)) {
            return back()->withErrors(['Email' => 'Invalid email or password.']);
        }

        Auth::login($user);

        // Redirect based on role
        return match($user->RoleID) {
          2 => redirect()->route('lecturer.dashboard'),
          default => redirect()->route('student.dashboard'),
};
    }

    // Show student register page
    public function showRegister()
    {
        return view('auth.register');
    }

    // Handle student registration
    public function register(Request $request)
    {
        $fullName = $request->input('name', $request->input('FullName'));
        $email = $request->input('email', $request->input('Email'));
        $password = $request->input('password', $request->input('Password'));
        $passwordConfirmation = $request->input('password_confirmation', $request->input('Password_confirmation'));

        $request->validate([
            'FullName' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:100',
            'Email' => 'nullable|email',
            'email' => 'nullable|email',
            'Password' => 'nullable|min:6',
            'password' => 'nullable|min:6|confirmed',
            'terms' => 'accepted',
        ]);

        if (empty($fullName) || empty($email) || empty($password) || $password !== $passwordConfirmation) {
            return back()->withErrors(['Email' => 'Please complete all required registration fields.'])->withInput();
        }

        $request->merge([
            'email' => $email,
        ]);

        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::query()->create([
            'FullName' => $fullName,
            'Email' => $email,
            'Password' => Hash::make($password),
            'RoleID' => 1,
            'DateJoined' => now(),
]);

        Auth::login($user);

        return redirect()->route('student.dashboard');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}