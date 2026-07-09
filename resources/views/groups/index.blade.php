<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Groups
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        All Groups
                    </h3>
                    <a href="{{ route('groups.create') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                        + New Group
                    </a>
                </div>

                @if($groups->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">No groups yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($groups as $group)
                            <a href="{{ route('groups.show', $group->GroupID) }}"
                               class="block border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $group->GroupName }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $group->Description ?? 'No description' }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>