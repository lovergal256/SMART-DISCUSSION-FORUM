<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Exclusion;
use Illuminate\Http\Request;

class ExclusionApiController extends Controller
{
    public function index(Request $request, $groupId)
    {
        $authId = $request->user()->UserID;

        $exclusions = Exclusion::where('UserID', $authId)
            ->where('GroupID', $groupId)
            ->get()
            ->map(function ($e) {
                $excludedUser = \App\Models\User::find($e->ExcludedUserID);
                return [
                    'ExclusionID' => $e->id,
                    'ExcludedUserID' => $e->ExcludedUserID,
                    'ExcludedUserName' => $excludedUser->FullName ?? 'Unknown',
                ];
            });

        return response()->json($exclusions);
    }

    public function store(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $authId = $request->user()->UserID;

        $request->validate([
            'excluded_user_id' => 'required|exists:users,UserID',
        ]);

        if ($request->excluded_user_id == $authId) {
            return response()->json(['message' => 'You cannot exclude yourself.'], 422);
        }

        if (!$group->members()->where('group_members.UserID', $request->excluded_user_id)->exists()) {
            return response()->json(['message' => 'That user is not a member of this group.'], 422);
        }

        $exists = Exclusion::where('UserID', $authId)
            ->where('ExcludedUserID', $request->excluded_user_id)
            ->where('GroupID', $groupId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already excluded this user.'], 422);
        }

        Exclusion::create([
            'UserID' => $authId,
            'ExcludedUserID' => $request->excluded_user_id,
            'GroupID' => $groupId,
        ]);

        return response()->json(['message' => 'User excluded successfully.'], 201);
    }

    public function destroy(Request $request, $groupId, $exclusionId)
    {
        $exclusion = Exclusion::where('id', $exclusionId)
            ->where('UserID', $request->user()->UserID)
            ->where('GroupID', $groupId)
            ->firstOrFail();

        $exclusion->delete();

        return response()->json(['message' => 'Exclusion removed.']);
    }
}