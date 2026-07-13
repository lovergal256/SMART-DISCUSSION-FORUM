<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LecturerController extends Controller
{
    public function create()
    {
        return view('admin.lecturers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'FullName' => 'required|max:100',
            'Email' => 'required|email|unique:users,Email',
            'Department' => 'nullable|max:100',
            'DateEmployed' => 'nullable|date',
            'Password' => 'required|confirmed|min:8',
        ]);

        // Create user
        $user = User::create([
            'FullName' => $request->FullName,
            'Email' => $request->Email,
            'Password' => Hash::make($request->Password),
            'RoleID' => 2, // Lecturer role
            'DateJoined' => now(),
        ]);

        // Create lecturer profile
        Lecturer::create([
            'UserID' => $user->UserID,
            'Department' => $request->Department,
            'DateEmployed' => $request->DateEmployed,
            'Status' => 'Active',
        ]);

        return redirect()->route('login')
            ->with('success', 'Lecturer registered successfully.');
    }

    public function index()
{
    // Display all lecturers
}

}
