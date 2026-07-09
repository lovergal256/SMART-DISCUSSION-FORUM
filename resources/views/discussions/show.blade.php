<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $topic->Title }} - Smart Discussion Forum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen py-10" style="background: linear-gradient(135deg, #0891b2, #06b6d4, #0e7490);">

    <div class="max-w-3xl mx-auto px-4">

        <a href="{{ route('discussions.index') }}"
            class="inline-block mb-4 text-white/90 hover:text-white text-sm font-semibold">
            ← Back to discussions
        </a>

        <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <h1 class="text-2xl font-bold text-gray-800">{{ $topic->Title }}</h1>
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
            <p class="text-gray-500 text-sm mb-4">
                Started by {{ $topic->user->FullName ?? 'Unknown' }}
                @if($topic->group)
                    in {{ $topic->group->Name ?? $topic->group->GroupName ?? 'a group' }}
                @endif
            </p>
            <p class="text-gray-700">{{ $topic->Description }}</p>
        </div>

        @forelse($posts as $post)
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-800">{{ $post->user->FullName ?? 'Unknown' }}</span>
                    <span class="text-xs text-gray-400">{{ optional($post->DatePosted)->format('M d, Y g:i A') ?? '' }}</span>
                </div>
                <p class="text-gray-700 mb-4">{{ $post->Content }}</p>

                @if($post->replies->count())
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        @foreach($post->replies->whereNull('ParentReplyID') as $reply)
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-semibold text-gray-700">{{ $reply->user->FullName ?? 'Unknown' }}</span>
                                    <span class="text-xs text-gray-400">{{ optional($reply->DateCreated)->format('M d, Y g:i A') ?? '' }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $reply->Body }}</p>

                                @php
                                    $childReplies = $post->replies->where('ParentReplyID', $reply->ReplyID);
                                @endphp
                                @if($childReplies->count())
                                    <div class="mt-3 ml-4 space-y-2 border-l-2 border-cyan-100 pl-4">
                                        @foreach($childReplies as $child)
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-xs font-semibold text-gray-700">{{ $child->user->FullName ?? 'Unknown' }}</span>
                                                    <span class="text-xs text-gray-400">{{ optional($child->DateCreated)->format('M d, Y g:i A') ?? '' }}</span>
                                                </div>
                                                <p class="text-xs text-gray-600">{{ $child->Body }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-lg p-10 text-center text-gray-500">
                No posts in this discussion yet.
            </div>
        @endforelse

    </div>

</body>
</html>
