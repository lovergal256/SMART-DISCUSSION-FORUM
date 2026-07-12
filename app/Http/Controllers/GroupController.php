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
        ]);


        $group->members()->attach(Auth::id(), ['Role' => 'admin']);
        
        return redirect()->route('groups.show', $group->GroupID);
    }

    public function show($id) {
        $group = Group::findOrFail($id);
        $members = $group->members;
        return view('groups.show', ['group' => $group, 'members' => $members]);
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

    public function leave(Request $request, $id){
        $group = Group::findOrFail($id);

        //checking if a user is actually a member
        if(!$group->members()->where('group_members.UserID', Auth::id())->exists()) {
            return redirect()->route('groups.index')->with('error', 'You are not a member of this group.');
        }

        //Check the user is not the only admin
        $adminCount = $group->members()->wherePivot('Role', 'admin')->count();
        $userRole = $group->members()->where('group_members.UserID', Auth::id())->first()->pivot->Role;

        if($userRole === 'admin' && $adminCount === 1) {
            return back()->with('error', 'You are the only admin. Promote another member before leaving.');
        }

        $group->members()->detach(Auth::id());

        return redirect()->route('groups.index')->with('success', 'You have left the group.');
    }

    public function promote(Request $request, $id, $userId) {
        $group = Group::findOrFail($id);

        //only admins can promote
        $userRole = $group->members()
                          ->where('group_members.UserID', Auth::id())
                          ->first();

        if(!$userRole || $userRole->pivot->Role !== 'admin') {
            return back()->with('error', 'Only admins can promote members.');
        }

        //Check the target user is actually a member 
        $member = $group->members()
                        ->where('group_members.UserID', $userId)
                        ->first();

        if(!$member) {
            return back()->with('error', 'That user is not a member of this group.');
        }

        //Update their role to admin
        $group->members()->updateExistingPivot($userId, ['Role' => 'admin']);

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

    return back()->with('success', $member->FullName . ' has been removed from the group.');
}
}