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
}