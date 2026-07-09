<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Exclusion;
use Illuminate\Support\Facades\Auth;

class ExclusionController extends Controller
{
    public function store(Request $request, $groupId) {
        $group = Group::findOrFail($groupId);

        $request->validate([
            'excluded_user_id' => 'required|exists:users,UserID', 
        ]);

        if($request->excluded_user_id == Auth::id()) {
            return back()->with('error', 'You cannot exclude yourself.');
        }

        //checking the excluded user is actually in the group
        if(!$group->members()->where('UserID', $request->excluded_user_id)) {
            return back()->with('error', 'That user is not a member of this groupchat');
        }

        //check if exclusion already exists
        $exists = Exclusion::where('UserID', Auth::id())
                           ->where('ExcludedUserID', $request->excluded_user_id)
                           ->where('GroupID', $groupId)
                           ->exists();
        
        if($exists) {
            return back()->with('error', 'You have already excluded this user.');
        }

        Exclusion::create([
            'UserID' => Auth::id(),
            'ExcludedUserID' => $request->excluded_user_id,
            'GroupID' => $groupId,
        ]);

        return back()->with('success', 'User excluded successfully.');
    }

    public function destroy($groupId, $exclusionId) {
        $exclusion = Exclusion::where('id', $exclusionId)
                              ->where('UserID', Auth::id())
                              ->where('GroupID', $groupId)
                              ->firstOrFail();

        $exclusion->delete();

        return back()->with('success', 'Exclusion removed.');
    }
}
