<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function index(Request $request)
    {
        $query = Discussion::query();

        if ($request->filled('search')) {
            $query->where('Title', 'like', '%' . $request->search . '%');
        }

        $discussions = $query->latest()->get();

        return view('discussions.index', compact('discussions'));
    }

    public function create()
    {
        return view('discussions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'Description' => 'nullable|string',
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