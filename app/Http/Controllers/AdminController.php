<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use App\Models\Discussion;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalLecturers = User::where('RoleID', 2)->count();
        $totalStudents = User::where('RoleID', 1)->count();
        $totalGroups = Group::count();
        $totalDiscussions = Discussion::count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalLecturers',
            'totalStudents',
            'totalGroups',
            'totalDiscussions'
        ));
    }

    public function createLecturer()
    {
        return view('admin.create-lecturer');
    }

    public function storeLecturer(Request $request)
    {
        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'Email' => 'required|email|unique:users,Email',
            'Password' => 'required|string|min:6|confirmed',
        ]);

$lecturer = User::create([
    'FullName' => $validated['FullName'],
    'Email' => $validated['Email'],
    'Password' => Hash::make($validated['Password']),
    'DateJoined' => now(),
    'Theme' => 'light',
    'RoleID' => 2,
]);

\App\Models\Lecturer::create([
    'UserID' => $lecturer->UserID,
    'Department' => 'General',
    'DateEmployed' => now(),
    'Status' => 'active',
]);

return redirect()->route('admin.dashboard')
    ->with('success', 'Lecturer account created for ' . $lecturer->FullName . '.');
    }
}