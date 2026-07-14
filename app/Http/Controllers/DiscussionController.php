<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DiscussionController extends Controller
{
    public function index(Request $request)
{
    $userGroupIds = \App\Models\GroupMember::where('UserID', auth()->user()->UserID)
        ->pluck('GroupID');

    $query = Discussion::with('group')
        ->whereIn('GroupID', $userGroupIds);

    if ($request->filled('search')) {
        $query->where('Title', 'like', '%' . $request->search . '%');
    }

    $discussions = $query->latest()->get();

    return view('discussions.index', compact('discussions'));
}
    public function create(Request $request)
{
    $groupId = $request->query('group');
    $group = $groupId ? \App\Models\Group::find($groupId) : null;

    return view('discussions.create', compact('group'));
}

   
      public function store(Request $request)
{
    $validated = $request->validate([
        'Title' => 'required|string|max:255',
        'Description' => 'nullable|string',
        'GroupID' => 'required|exists:groups,GroupID',
    ]);
    $validated['UserID'] = auth()->id() ?? auth()->user()->UserID;

    $discussion = Discussion::create($validated);

    return redirect()->route('discussions.show', $discussion->DiscussionID)
        ->with('success', 'Discussion created successfully.');
}

    public function show(Discussion $discussion)
    {
        $topics = $discussion->topics()->latest()->get();

        return view('discussions.show', compact('discussion', 'topics'));
    }
     public function exportPdf(Discussion $discussion)
    {
        $discussion->load([
            'user',
            'group',
            'topics.user',
            'topics.posts.user',
            'topics.posts.replies.user',
        ]);

        $pdf = Pdf::loadView('discussions.pdf', compact('discussion'))
            ->setPaper('a4');

        $filename = 'discussion-' . $discussion->DiscussionID . '.pdf';

        return $pdf->download($filename);
    }
    public function edit(Discussion $discussion)
    {
        return view('discussions.edit', compact('discussion'));
    }

    public function update(Request $request, Discussion $discussion)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
        ]);

        $discussion->update($validated);

        return redirect()->route('discussions.show', $discussion->DiscussionID)
            ->with('success', 'Discussion updated successfully.');
    }

    public function destroy(Discussion $discussion)
    {
        $discussion->delete();

        return redirect()->route('discussions.index')
            ->with('success', 'Discussion deleted successfully.');
    }
}