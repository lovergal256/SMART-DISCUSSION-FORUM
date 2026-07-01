<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

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
           'group_name' => 'required|max:100',
           'Description' => 'nullable',
        ]);

        dd([
        'Group Name' => $request->input('group_name'),
        'Description' => $request->input('Description'),
        ]);

        Group::create([
            'Group Name' => $request->input('group_name'),
            'Description' => $request->input('Description'),
        ]);

        return redirect('/groups');
    }
}