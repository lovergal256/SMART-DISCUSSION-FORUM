<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $group->GroupName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Group Info --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    About this group
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $group->Description ?? 'No description provided.' }}
                </p>
            </div>

            {{-- Success / Error messages --}}
            @if(session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Members List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Members ({{ $members->count() }})
                </h3>

                @if($members->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No members yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($members as $member)
                            <div class="flex items-center justify-between border border-gray-100 dark:border-gray-700 rounded-lg px-4 py-3">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $member->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $member->email }}
                                    </div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full">
                                    {{ $member->pivot->Role === 'admin'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $member->pivot->Role }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Add Member --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Add a Member
                </h3>

                <form method="POST" action="{{ route('groups.addMember', $group->GroupID) }}">
                    @csrf
                    <div class="flex gap-3">
                        <input type="number"
                               name="user_id"
                               placeholder="Enter User ID"
                               class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                            Add Member
                        </button>
                    </div>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </form>
            </div>


            {{-- Exclusions --}}
<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
        My Exclusions
    </h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Excluded members will not see your posts in this group.
    </p>

    {{-- Exclude a member form --}}
    <form method="POST" action="{{ route('exclusions.store', $group->GroupID) }}">
        @csrf
        <div class="flex gap-3 mb-6">
            <input type="number"
                   name="excluded_user_id"
                   placeholder="Enter User ID to exclude"
                   class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm text-sm">
            <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
                Exclude
            </button>
        </div>
        @error('excluded_user_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </form>

    {{-- Current exclusions list --}}
    @php
        $myExclusions = $group->exclusions()->where('UserID', auth()->id())->with('excludedUser')->get();
    @endphp

    @if($myExclusions->isEmpty())
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            You have not excluded anyone in this group.
        </p>
    @else
        <div class="space-y-3">
            @foreach($myExclusions as $exclusion)
                <div class="flex items-center justify-between border border-gray-100 dark:border-gray-700 rounded-lg px-4 py-3">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $exclusion->excludedUser->name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $exclusion->excludedUser->email }}
                        </div>
                    </div>
                    <form method="POST"
                          action="{{ route('exclusions.destroy', [$group->GroupID, $exclusion->id]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-sm text-red-600 hover:text-red-800">
                            Remove
                        </button>
                    </form>
                         </div>
                    @endforeach
                </div>
            @endif
       </div>



            <div>
                <a href="{{ route('groups.index') }}"
                   class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    ← Back to Groups
                </a>
            </div>

        </div>
    </div>
</x-app-layout>