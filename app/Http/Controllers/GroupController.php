<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $myGroups = Auth::user()->groups()
            ->when($search, function ($query, $search) {
                $query->where('GroupName', 'like', '%' . $search . '%');
            })
            ->get();

        $myGroupIds = $myGroups->pluck('GroupID');

        $discoverGroups = Group::where('Visibility', 'public')
            ->whereNotIn('GroupID', $myGroupIds)
            ->when($search, function ($query, $search) {
                $query->where('GroupName', 'like', '%' . $search . '%');
            })
            ->get();

        $layout = Auth::user()->RoleID == 2 ? 'layouts.lecturer_app' : 'layouts.app';

        return view('groups.index', [
            'groups' => $myGroups,
            'discoverGroups' => $discoverGroups,
            'search' => $search,
            'layout' => $layout,
        ]);
    }

    public function create() {
        $layout = Auth::user()->RoleID == 2 ? 'layouts.lecturer_app' : 'layouts.app';

        return view('groups.create', compact('layout'));
    }

    public function store(Request $request) {
        $request->validate([
           'GroupName' => 'required|max:100|unique:groups,GroupName',
           'Description' => 'nullable',
        ]);

        $group = Group::create([
            'GroupName' => $request->input('GroupName'),
            'Description' => $request->input('Description'),
            'Visibility' => $request->input('Visibility', 'private'),
            'CreatedBy' => Auth::id(),
        ]);

        $group->members()->attach(Auth::id(), ['Role' => 'admin', 'Status' => 'approved']);

        return redirect()->route('groups.show', $group->GroupID);
    }

    public function show($id) {
        $group = Group::findOrFail($id);

        $isMember = $group->members()
            ->where('group_members.UserID', Auth::id())
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isMember) {
            return redirect()->route('groups.index')
                ->with('error', 'You are not a member of this group.');
        }

        $members = $group->members()->wherePivot('Status', 'approved')->get();
        $pendingRequests = $group->members()->wherePivot('Status', 'pending')->get();

        $isAdmin = $group->members()
            ->where('group_members.UserID', Auth::id())
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->exists();

        $hasPendingRequest = $group->members()
            ->where('group_members.UserID', Auth::id())
            ->wherePivot('Status', 'pending')
            ->exists();

        $discussions = \App\Models\Discussion::where('GroupID', $group->GroupID)->latest()->get();

        $layout = Auth::user()->RoleID == 2 ? 'layouts.lecturer_app' : 'layouts.app';

        return view('groups.show', compact(
            'group', 'members', 'pendingRequests', 'isMember', 'isAdmin',
            'hasPendingRequest', 'discussions', 'layout'
        ));
    }


    public function addMember(Request $request, $id) {
      $group = Group::findOrFail($id);

      // Only admins can directly add members
      $authUser = $group->members()
                      ->where('group_members.UserID', Auth::id())
                      ->wherePivot('Status', 'approved')
                      ->first();

      if (!$authUser || $authUser->pivot->Role !== 'admin') {
          return back()->with('error', 'Only group admins can add members directly.');
      }

      $request->validate([
          'user_id' => 'required|exists:users,UserID',
      ]);

      if ($group->members()->where('group_members.UserID', $request->user_id)->exists()) {
          return back()->with('error', 'User is already a member of this group.');
      }

      $group->members()->attach($request->user_id, ['Role' => 'member', 'Status' => 'approved']);

      return back()->with('success', 'Member added successfully.');
    }

    public function requestJoin($id) {
        $group = Group::findOrFail($id);

        $alreadyRequested = $group->members()->where('group_members.UserID', Auth::id())->exists();

        if ($alreadyRequested) {
            return back()->with('error', 'You have already requested or joined this group.');
        }

        $group->members()->attach(Auth::id(), ['Role' => 'member', 'Status' => 'pending']);

        $requester = Auth::user();

        $adminIds = $group->members()
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->pluck('users.UserID');

        foreach ($adminIds as $adminId) {
            \App\Models\Notification::create([
                'NotificationID' => uniqid(),
                'UserID' => $adminId,
                'Message' => "{$requester->FullName} requested to join {$group->GroupName}.",
                'Type' => 'group_join_request',
                'Status' => 'Unread',
            ]);
        }

        return back()->with('success', 'Join request sent. Waiting for admin approval.');
    }

    public function approveMember($groupId, $userId) {
        $group = Group::findOrFail($groupId);
        $group->members()->updateExistingPivot($userId, ['Status' => 'approved']);
        return back()->with('success', 'Member approved.');
    }

    public function rejectMember($groupId, $userId) {
        $group = Group::findOrFail($groupId);
        $group->members()->detach($userId);
        return back()->with('success', 'Request rejected.');
    }

    public function leave(Request $request, $id) {
        $group = Group::findOrFail($id);

        // Checking if a user is actually a member
        if (!$group->members()->where('group_members.UserID', Auth::id())->exists()) {
            return redirect()->route('groups.index')->with('error', 'You are not a member of this group.');
        }

        // Check the user is not the only admin
        $adminCount = $group->members()->wherePivot('Role', 'admin')->count();
        $userRole = $group->members()->where('group_members.UserID', Auth::id())->first()->pivot->Role;

        if ($userRole === 'admin' && $adminCount === 1) {
            return back()->with('error', 'You are the only admin. Promote another member before leaving.');
        }

        $group->members()->detach(Auth::id());

        return redirect()->route('groups.index')->with('success', 'You have left the group.');
    }

    public function promote(Request $request, $id, $userId) {
    $group = Group::findOrFail($id);

    // Only admins can promote
    $userRole = $group->members()
                      ->where('group_members.UserID', Auth::id())
                      ->first();

    if (!$userRole || $userRole->pivot->Role !== 'admin') {
        return back()->with('error', 'Only admins can promote members.');
    }

    // Check the target user is actually a member
    $member = $group->members()
                    ->where('group_members.UserID', $userId)
                    ->first();

    if (!$member) {
        return back()->with('error', 'That user is not a member of this group.');
    }

    // Update their role to admin
    $group->members()->updateExistingPivot($userId, ['Role' => 'admin']);

    \App\Models\Notification::create([
        'NotificationID' => uniqid(),
        'UserID' => $userId,
        'Message' => "You have been promoted to admin in the group \"{$group->GroupName}\".",
        'Type' => 'group_promoted',
        'Status' => 'Unread',
    ]);

    return back()->with('success', $member->FullName . " has been promoted to admin.");
}

     public function removeMember(Request $request, $id, $userId)
{
    $group = Group::findOrFail($id);

    // Only admins can remove members
    $authUser = $group->members()
                      ->where('group_members.UserID', Auth::id())
                      ->first();

    if (!$authUser || $authUser->pivot->Role !== 'admin') {
        return back()->with('error', 'Only admins can remove members.');
    }

    // Can't remove yourself this way
    if ($userId == Auth::id()) {
        return back()->with('error', 'Use the leave button to leave the group.');
    }

    // Check target is actually a member
    $member = $group->members()
                    ->where('group_members.UserID', $userId)
                    ->first();

    if (!$member) {
        return back()->with('error', 'That user is not a member of this group.');
    }

    $group->members()->detach($userId);

    \App\Models\Notification::create([
        'NotificationID' => uniqid(),
        'UserID' => $userId,
        'Message' => "You have been removed from the group \"{$group->GroupName}\".",
        'Type' => 'group_removed',
        'Status' => 'Unread',
    ]);

    return back()->with('success', $member->FullName . ' has been removed from the group.');
}
public function blacklistMember(Request $request, $id, $userId)
{
    $group = Group::findOrFail($id);

    // Only admins can blacklist members
    $authUser = $group->members()
                      ->where('group_members.UserID', Auth::id())
                      ->first();

    if (!$authUser || $authUser->pivot->Role !== 'admin') {
        return back()->with('error', 'Only admins can blacklist members.');
    }

    // Can't blacklist yourself this way
    if ($userId == Auth::id()) {
        return back()->with('error', 'You cannot blacklist yourself.');
    }

    // Check target is actually a member
    $member = $group->members()
                    ->where('group_members.UserID', $userId)
                    ->first();

    if (!$member) {
        return back()->with('error', 'That user is not a member of this group.');
    }

    // Skip if already actively blacklisted
    $alreadyBlacklisted = \App\Models\Blacklist::where('UserID', $userId)
        ->where('EndDate', '>=', now()->toDateString())
        ->exists();

    if ($alreadyBlacklisted) {
        return back()->with('error', $member->FullName . ' is already blacklisted.');
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

    return back()->with('success', $member->FullName . ' has been blacklisted for one month.');
}

    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        $isAdmin = $group->members()
            ->where('group_members.UserID', Auth::id())
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->exists();

        if (!$isAdmin) {
            return redirect()->route('groups.show', $group->GroupID)
                ->with('error', 'Only the group admin can delete this group.');
        }

        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Group deleted successfully.');
    }

    public function toggleVisibility($id)
{
    $group = Group::findOrFail($id);

    $isAdmin = $group->members()
        ->where('group_members.UserID', Auth::id())
        ->wherePivot('Role', 'admin')
        ->wherePivot('Status', 'approved')
        ->exists();

    if (!$isAdmin) {
        return back()->with('error', 'Only admins can change group visibility.');
    }

    $group->Visibility = $group->Visibility === 'public' ? 'private' : 'public';
    $group->save();

    return back()->with('success', 'Group visibility updated to ' . $group->Visibility . '.');
}
}