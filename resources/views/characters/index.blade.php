<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Characters') }}
            </h2>
            <a href="{{ route('characters.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Create Character
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($characters->isEmpty())
                        <p class="text-gray-600">No characters yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Class</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Level</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($characters as $character)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $character->name }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $character->class }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $character->level }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex justify-end gap-3">
                                                    <a href="{{ route('characters.show', $character) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">View</a>
                                                    <a href="{{ route('characters.edit', $character) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Edit</a>
                                                    <form method="POST" action="{{ route('characters.destroy', $character) }}" onsubmit="return confirm('Delete this character and all of its stats and items?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
