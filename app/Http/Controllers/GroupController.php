<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index() {
        $groups = Group::all();
        return view('groups.index', ['groups' => $groups]);
    }


    public function create() {

        return view('groups.create');

    }

    public function store(Request $request) {
    
        $request->validate([
           'GroupName' => 'required|max:100',
           'Description' => 'nullable',
        ]);

        $group = Group::create([
    'GroupName' => $request->input('GroupName'),
    'Description' => $request->input('Description'),
    'CreatedBy' => Auth::id(),
]);


        $group->members()->attach(Auth::id(), ['Role' => 'admin', 'Status' => 'approved']);
        
        return redirect()->route('groups.show', $group->GroupID);
    }

    public function show($id) {
    $group = Group::findOrFail($id);

    $members = $group->members()->wherePivot('Status', 'approved')->get();

    $pendingRequests = $group->members()->wherePivot('Status', 'pending')->get();

    $isMember = $group->members()
        ->where('group_members.UserID', Auth::id())
        ->wherePivot('Status', 'approved')
        ->exists();

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

    return view('groups.show', compact('group', 'members', 'pendingRequests', 'isMember', 'isAdmin', 'hasPendingRequest', 'discussions'));
}

    public function addMember(Request $request, $id) {

         $group = Group::findOrFail($id);

         $request->validate([
         'user_id' => 'required|exists:users,UserID',
         ]);

        if ($group->members()->where('group_members.UserID', $request->user_id)->exists()) {
         return back()->with('error', 'User is already a member of this group.');
        }

         $group->members()->attach($request->user_id, ['Role' => 'member']);

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
public function leaveGroup($id) {
    $group = Group::findOrFail($id);

    $isMember = $group->members()
        ->where('group_members.UserID', Auth::id())
        ->exists();

    if (!$isMember) {
        return back()->with('error', 'You are not a member of this group.');
    }

    $isAdmin = $group->members()
        ->where('group_members.UserID', Auth::id())
        ->wherePivot('Role', 'admin')
        ->wherePivot('Status', 'approved')
        ->exists();

    if ($isAdmin) {
        $otherAdmins = $group->members()
            ->wherePivot('Role', 'admin')
            ->wherePivot('Status', 'approved')
            ->where('group_members.UserID', '!=', Auth::id())
            ->exists();

        if (!$otherAdmins) {
            return back()->with('error', 'You are the only admin. Promote another member to admin first, or delete the group instead.');
        }
    }

    $group->members()->detach(Auth::id());

    return redirect()->route('groups.index')->with('success', 'You have left the group.');
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
}