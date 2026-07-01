<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        $request->validate([
            'Email'    => 'required|email',
            'Password' => 'required',
        ]);

        $user = User::where('Email', $request->Email)->first();

        if (!$user || !Hash::check($request->Password, $user->Password)) {
            return back()->withErrors(['Email' => 'Invalid email or password.']);
        }

        Auth::login($user);

        // Redirect based on role
        return match((int) $user->RoleID) {
            3 => redirect()->route('admin.dashboard'),
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
        $request->validate([
            'FullName' => 'required|string|max:100',
            'Email'    => 'required|email|unique:users,Email',
            'Password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'FullName'       => $request->FullName,
            'Email'          => $request->Email,
            'Password'       => Hash::make($request->Password),
            'DateJoined'     => now(),
            'LastActiveDate' => now(),
            'RoleID'         => 1,
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