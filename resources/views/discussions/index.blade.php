@extends('layouts.app')

@section('title', 'Recommendations — Smart Discussion Forum')

@section('content')
    <div class="max-w-3xl mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="page-head">Discussions</h1>
                <a href="{{ route('discussions.index') }}"
                    class="text-sm text-cyan-600 hover:text-cyan-500 font-semibold">
                    ← Back to all
                </a>
            </div>

            <form method="GET" action="{{ route('discussions.search') }}" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <input type="text" name="q" value="{{ $query ?? '' }}"
                    placeholder="Search discussions..." style="flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.375rem;">
                <button type="submit" style="background: #0891b2; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer; font-weight: bold;"
                onmouseover="this.style.background='#0e7490'"onmouseout="this.style.background='#0891b2'"
    >
                    Search
                </button>
            </form>
        </div>

        @forelse($topics as $topic)
            <a href="{{ route('discussions.show', $topic->TopicID) }}"
                class="block bg-white rounded-2xl shadow-lg p-6 mb-4 hover:shadow-xl transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-lg font-bold text-gray-800">{{ $topic->Title }}</h2>
                            @if(strtolower($topic->Status) === 'open')
                                <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                    {{ ucfirst($topic->Status) }}
                                </span>
                            @else
                                <span class="text-xs font-semibold bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                    {{ ucfirst($topic->Status) }}
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-sm mb-3 line-clamp-2">{{ $topic->Description }}</p>
                        <div class="flex items-center gap-4 text-xs text-gray-400">
                            <span>👤 {{ $topic->user->FullName ?? 'Unknown' }}</span>
                            @if($topic->group)
                                <span>📁 {{ $topic->group->Name ?? $topic->group->GroupName ?? 'Group' }}</span>
                            @endif
                            <span>💬 {{ $topic->posts_count }} {{ Str::plural('post', $topic->posts_count) }}</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl shadow-lg p-10 text-center text-gray-500">
                No discussions found.
            </div>
        @endforelse

        @if($topics->hasPages())
            <div class="bg-white rounded-2xl shadow-lg p-4 mt-4">
                {{ $topics->links() }}
            </div>
        @endif

    </div>

@endsection
