<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminApiController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'totalUsers' => User::count(),
            'totalLecturers' => User::where('RoleID', 2)->count(),
            'totalStudents' => User::where('RoleID', 1)->count(),
            'totalGroups' => Group::count(),
            'totalDiscussions' => Discussion::count(),
        ]);
    }

    public function registerLecturer(Request $request)
{
    $validated = $request->validate([
        'FullName' => 'required|string|max:255',
        'Email' => 'required|email|unique:users,Email',
        'Password' => 'required|string|min:6',
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

    return response()->json(['message' => 'Lecturer registered: ' . $lecturer->FullName]);
}

public function groups()
{
    $groups = Group::withCount('members')
        ->get()
        ->map(function ($group) {
            return [
                'id' => $group->GroupID,
                'name' => $group->GroupName,
                'members' => $group->members_count,
            ];
        });

    return response()->json($groups);
}

public function discussions()
{
    $discussions = Discussion::with('user')
        ->latest()
        ->take(50)
        ->get()
        ->map(function ($d) {
            return [
                'id' => $d->DiscussionID,
                'title' => $d->Title,
                'author' => $d->user->FullName ?? 'Unknown',
                'posted_at' => $d->created_at->diffForHumans(),
            ];
        });

    return response()->json($discussions);
}
}