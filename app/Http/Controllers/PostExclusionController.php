<?php

namespace App\Http\Controllers;
use App\Models\ExclusionList;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class PostExclusionController extends Controller
{
    // Exclude a user from seeing a post
    public function store(Request $request, Topic $topic, Post $post)
    {
        $request->validate([
            'excluded_user_id' => 'required',
        ]);

        ExclusionList::create([
            'UserID'         => '1', // replace with auth()->id() later
            'ExcludedUserID' => $request->excluded_user_id,
            'ContentType'    => 'post',
            'ContentID'      => $post->PostID,
            'ExclusionDate'  => now(),
        ]);

        return redirect()->back()->with('success', 'User excluded from this post.');
    }

    // Remove exclusion
    public function destroy(Topic $topic, Post $post, User $user)
    {
        ExclusionList::where('ExcludedUserID', $user->UserID)
            ->where('ContentID', $post->PostID)
            ->where('ContentType', 'post')
            ->delete();

        return redirect()->back()->with('success', 'Exclusion removed.');
    }
    // View all exclusions for a topic
    public function index(Topic $topic)
{
    $exclusions = ExclusionList::with(['excludedUser'])
        ->where('ContentType', 'post')
        ->whereIn('ContentID', $topic->posts()->pluck('PostID'))
        ->get();

    return view('exclusions.index', compact('topic', 'exclusions'));
}
}

>>>>>>> 1b11ce3cb088bfe2adeb69eb507f1cdf2f084f83
