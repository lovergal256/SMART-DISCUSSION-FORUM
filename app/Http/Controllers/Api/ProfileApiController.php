<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileApiController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'UserID' => $user->UserID,
            'FullName' => $user->FullName,
            'Email' => $user->Email,
            'Theme' => $user->Theme,
            'Role' => $user->role_name,
            'RoleID' => $user->RoleID,
            'DateJoined' => optional($user->DateJoined)?->format('M d, Y') ?? (string) $user->DateJoined,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'FullName' => 'required|string|max:255',
            'Theme' => 'required|in:light,dark',
        ]);

        $request->user()->update($validated);

        return response()->json(['message' => 'Profile updated successfully.']);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->Password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update([
            'Password' => Hash::make($validated['new_password']),
        ]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'delete_confirm_password' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['delete_confirm_password'], $user->Password)) {
            return response()->json(['message' => 'Password is incorrect. Account not deleted.'], 422);
        }

        $user->update([
            'FullName' => 'Deleted User',
            'Email' => 'deleted_' . $user->UserID . '_' . uniqid() . '@deleted.local',
            'Password' => Hash::make(uniqid() . uniqid()),
        ]);

        // Revoke all Sanctum tokens so the current API session is invalidated
        $user->tokens()->delete();

        return response()->json(['message' => 'Your account has been deleted.']);
    }
}
