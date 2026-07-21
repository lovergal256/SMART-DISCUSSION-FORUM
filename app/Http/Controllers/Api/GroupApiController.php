<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupApiController extends Controller
{
    public function index()
    {
        $groups = Auth::user()->groups()
            ->wherePivot('Status', 'approved')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->GroupID,
                    'name' => $group->GroupName,
                    'description' => $group->Description,
                    'visibility' => $group->Visibility,
                    'members_count' => $group->members()->wherePivot('Status', 'approved')->count(),
                    'role' => $group->pivot->Role,
                ];
            });

        return response()->json($groups);
    }

    public function show($id)
    {
        $group = Group::findOrFail($id);

        $isMember = $group->members()
            ->where('group_members.UserID', Auth::id())
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $members = $group->members()
            ->wherePivot('Status', 'approved')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->UserID,
                    'name' => $member->FullName,
                    'email' => $member->Email,
                    'role' => $member->pivot->Role,
                ];
            });

        return response()->json([
            'id' => $group->GroupID,
            'name' => $group->GroupName,
            'description' => $group->Description,
            'visibility' => $group->Visibility,
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100|unique:groups,GroupName',
            'description' => 'nullable',
            'visibility' => 'in:public,private',
        ]);

        $group = Group::create([
            'GroupName' => $request->name,
            'Description' => $request->description,
            'Visibility' => $request->input('visibility', 'private'),
            'CreatedBy' => Auth::id(),
        ]);

        $group->members()->attach(Auth::id(), ['Role' => 'admin', 'Status' => 'approved']);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => [
                'id' => $group->GroupID,
                'name' => $group->GroupName,
            ]
        ], 201);
    }

    public function discover()
    {
        $myGroupIds = Auth::user()->groups()->pluck('groups.GroupID');

        $groups = Group::where('Visibility', 'public')
            ->whereNotIn('GroupID', $myGroupIds)
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->GroupID,
                    'name' => $group->GroupName,
                    'description' => $group->Description,
                    'members_count' => $group->members()->wherePivot('Status', 'approved')->count(),
                ];
            });

        return response()->json($groups);
    }
}