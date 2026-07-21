<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupApiController extends Controller
{
    /**
     * List the groups the authenticated user belongs to.
     */
    public function index(Request $request)
    {
        $groups = $request->user()
            ->groups()
            ->get()
            ->map(function ($group) {
                $memberCount = $group->members()
                    ->wherePivot('Status', 'approved')
                    ->count();

                return [
                    'GroupID' => $group->GroupID,
                    'GroupName' => $group->GroupName,
                    'Description' => $group->Description,
                    'MemberCount' => $memberCount,
                ];
            });

        return response()->json($groups);
    }
    /**
 * Create a new group, with the creator automatically added as admin.
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'GroupName' => 'required|string|max:255',
        'Description' => 'nullable|string',
    ]);

    $group = \App\Models\Group::create([
        'GroupName' => $validated['GroupName'],
        'Description' => $validated['Description'] ?? null,
        'CreatedBy' => $request->user()->UserID,
        'Visibility' => 'private',
    ]);

    $group->members()->attach($request->user()->UserID, [
        'Role' => 'admin',
        'Status' => 'approved',
    ]);

    return response()->json([
        'GroupID' => $group->GroupID,
        'GroupName' => $group->GroupName,
        'Description' => $group->Description,
    ], 201);
}

    /**
     * Full group detail: info, visibility, members, pending requests (if admin).
     */
    public function show(Request $request, $id)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $isMember = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        $isAdmin = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->exists();

        $hasPendingRequest = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Status', 'pending')
            ->exists();

        $members = $group->members()
            ->wherePivot('Status', 'approved')
            ->get()
            ->map(function ($member) use ($group) {
                return [
                    'UserID' => $member->UserID,
                    'FullName' => $member->FullName,
                    'Email' => $member->Email,
                    'Role' => $member->pivot->Role,
                    'IsCreator' => $member->UserID == $group->CreatedBy,
                ];
            });

        $pendingRequests = [];
        if ($isAdmin) {
            $pendingRequests = $group->members()
                ->wherePivot('Status', 'pending')
                ->get()
                ->map(function ($member) {
                    return [
                        'UserID' => $member->UserID,
                        'FullName' => $member->FullName,
                        'Email' => $member->Email,
                    ];
                });
        }

        $discussions = \App\Models\Discussion::where('GroupID', $group->GroupID)
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'DiscussionID' => $d->DiscussionID,
                    'Title' => $d->Title,
                ];
            });

        return response()->json([
            'GroupID' => $group->GroupID,
            'GroupName' => $group->GroupName,
            'Description' => $group->Description,
            'Visibility' => $group->Visibility,
            'IsMember' => $isMember,
            'IsAdmin' => $isAdmin,
            'HasPendingRequest' => $hasPendingRequest,
            'Members' => $members,
            'PendingRequests' => $pendingRequests,
            'Discussions' => $discussions,
        ]);
    }

    /**
     * List members of a specific group, including the auth user's admin status.
     * (Kept for any screens that only need the members subset.)
     */
    public function members(Request $request, $id)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $isMember = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        $isAdmin = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->exists();

        $members = $group->members()
            ->wherePivot('Status', 'approved')
            ->get()
            ->map(function ($member) use ($group) {
                return [
                    'UserID' => $member->UserID,
                    'FullName' => $member->FullName,
                    'Email' => $member->Email,
                    'Role' => $member->pivot->Role,
                    'IsCreator' => $member->UserID == $group->CreatedBy,
                ];
            });

        return response()->json([
            'GroupID' => $group->GroupID,
            'GroupName' => $group->GroupName,
            'IsAdmin' => $isAdmin,
            'Members' => $members,
        ]);
    }

    public function toggleVisibility(Request $request, $id)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $isAdmin = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can change group visibility.'], 403);
        }

        $group->Visibility = $group->Visibility === 'public' ? 'private' : 'public';
        $group->save();

        return response()->json([
            'message' => 'Group visibility updated to ' . $group->Visibility . '.',
            'Visibility' => $group->Visibility,
        ]);
    }

    public function addMember(Request $request, $id)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $authUser = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Status', 'approved')
            ->first();

        if (!$authUser || $authUser->pivot->Role !== 'admin') {
            return response()->json(['message' => 'Only group admins can add members directly.'], 403);
        }

        $request->validate(['user_id' => 'required|exists:users,UserID']);

        if ($group->members()->where('group_members.UserID', $request->user_id)->exists()) {
            return response()->json(['message' => 'User is already a member of this group.'], 422);
        }

        $group->members()->attach($request->user_id, ['Role' => 'member', 'Status' => 'approved']);

        return response()->json(['message' => 'Member added successfully.']);
    }

    public function promote(Request $request, $id, $userId)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $authRole = $group->members()->where('group_members.UserID', $authId)->first();

        if (!$authRole || $authRole->pivot->Role !== 'admin') {
            return response()->json(['message' => 'Only admins can promote members.'], 403);
        }

        $member = $group->members()->where('group_members.UserID', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'That user is not a member of this group.'], 404);
        }

        $group->members()->updateExistingPivot($userId, ['Role' => 'admin']);

        \App\Models\Notification::create([
            'NotificationID' => uniqid(),
            'UserID' => $userId,
            'Message' => "You have been promoted to admin in the group \"{$group->GroupName}\".",
            'Type' => 'group_promoted',
            'Status' => 'Unread',
        ]);

        return response()->json(['message' => $member->FullName . ' has been promoted to admin.']);
    }

    public function removeMember(Request $request, $id, $userId)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $authUser = $group->members()->where('group_members.UserID', $authId)->first();

        if (!$authUser || $authUser->pivot->Role !== 'admin') {
            return response()->json(['message' => 'Only admins can remove members.'], 403);
        }

        if ($userId == $authId) {
            return response()->json(['message' => 'Use the leave option to leave the group.'], 422);
        }

        $member = $group->members()->where('group_members.UserID', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'That user is not a member of this group.'], 404);
        }

        $group->members()->detach($userId);

        \App\Models\Notification::create([
            'NotificationID' => uniqid(),
            'UserID' => $userId,
            'Message' => "You have been removed from the group \"{$group->GroupName}\".",
            'Type' => 'group_removed',
            'Status' => 'Unread',
        ]);

        return response()->json(['message' => $member->FullName . ' has been removed from the group.']);
    }

    /**
     * Blacklist a member of the group (admin only).
     */
    public function blacklistMember(Request $request, $id, $userId)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $authUser = $group->members()
            ->where('group_members.UserID', $authId)
            ->first();

        if (!$authUser || $authUser->pivot->Role !== 'admin') {
            return response()->json(['message' => 'Only admins can blacklist members.'], 403);
        }

        if ($userId == $authId) {
            return response()->json(['message' => 'You cannot blacklist yourself.'], 422);
        }

        $member = $group->members()
            ->where('group_members.UserID', $userId)
            ->first();

        if (!$member) {
            return response()->json(['message' => 'That user is not a member of this group.'], 404);
        }

        $alreadyBlacklisted = \App\Models\Blacklist::where('UserID', $userId)
            ->where('EndDate', '>=', now()->toDateString())
            ->exists();

        if ($alreadyBlacklisted) {
            return response()->json(['message' => $member->FullName . ' is already blacklisted.'], 422);
        }

        \App\Models\Blacklist::create([
            'BlacklistID' => uniqid(),
            'UserID' => $userId,
            'Reason' => 'Blacklisted by group admin in "' . $group->GroupName . '"',
            'StartDate' => now()->toDateString(),
            'EndDate' => now()->copy()->addMonth()->toDateString(),
        ]);

        \App\Models\Notification::create([
            'NotificationID' => uniqid(),
            'UserID' => $userId,
            'Message' => "You have been blacklisted by an admin of \"{$group->GroupName}\" for one month.",
            'Type' => 'blacklist',
            'Status' => 'Unread',
        ]);

        return response()->json(['message' => $member->FullName . ' has been blacklisted for one month.']);
    }

    public function approveMember(Request $request, $id, $userId)
    {
        $group = \App\Models\Group::findOrFail($id);
        $group->members()->updateExistingPivot($userId, ['Status' => 'approved']);
        return response()->json(['message' => 'Member approved.']);
    }

    public function rejectMember(Request $request, $id, $userId)
    {
        $group = \App\Models\Group::findOrFail($id);
        $group->members()->detach($userId);
        return response()->json(['message' => 'Request rejected.']);
    }

    public function leave(Request $request, $id)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        if (!$group->members()->where('group_members.UserID', $authId)->exists()) {
            return response()->json(['message' => 'You are not a member of this group.'], 422);
        }

        $adminCount = $group->members()->wherePivot('Role', 'admin')->count();
        $userRole = $group->members()->where('group_members.UserID', $authId)->first()->pivot->Role;

        if ($userRole === 'admin' && $adminCount === 1) {
            return response()->json(['message' => 'You are the only admin. Promote another member before leaving.'], 422);
        }

        $group->members()->detach($authId);

        return response()->json(['message' => 'You have left the group.']);
    }

    public function destroy(Request $request, $id)
    {
        $group = \App\Models\Group::findOrFail($id);
        $authId = $request->user()->UserID;

        $isAdmin = $group->members()
            ->where('group_members.UserID', $authId)
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isAdmin) {
            return response()->json(['message' => 'Only the group admin can delete this group.'], 403);
        }

        $group->delete();

        return response()->json(['message' => 'Group deleted successfully.']);
    }
}
