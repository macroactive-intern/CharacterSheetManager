<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $character->name }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('characters.edit', $character) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Edit</a>
                <a href="{{ route('characters.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="grid gap-4 p-6 text-gray-900 sm:grid-cols-3">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Name</div>
                        <div class="mt-1 text-lg font-semibold">{{ $character->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Class</div>
                        <div class="mt-1 text-lg font-semibold">{{ $character->class }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Level</div>
                        <div class="mt-1 text-lg font-semibold">{{ $character->level }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="space-y-6 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Stats</h3>
                        <div class="text-sm font-medium text-gray-600">
                            {{ $character->stats->sum('value') }} / 100
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($character->stats as $stat)
                            <div class="rounded-md border border-gray-200 p-4">
                                <form method="POST" action="{{ route('characters.stats.update', [$character, $stat]) }}" class="grid gap-3 md:grid-cols-[1fr_120px_auto] md:items-end">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <x-input-label for="stat-name-{{ $stat->id }}" value="Name" />
                                        <x-text-input id="stat-name-{{ $stat->id }}" name="stats[{{ $stat->id }}][name]" type="text" class="mt-1 block w-full" :value="old('stats.' . $stat->id . '.name', $stat->name)" required />
                                        <x-input-error :messages="$errors->get('stats.' . $stat->id . '.name')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="stat-value-{{ $stat->id }}" value="Value" />
                                        <x-text-input id="stat-value-{{ $stat->id }}" name="stats[{{ $stat->id }}][value]" type="number" min="1" class="mt-1 block w-full" :value="old('stats.' . $stat->id . '.value', $stat->value)" required />
                                        <x-input-error :messages="$errors->get('stats.' . $stat->id . '.value')" class="mt-2" />
                                    </div>
                                    <div class="flex gap-2">
                                        <x-primary-button>Update</x-primary-button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('characters.stats.destroy', [$character, $stat]) }}" class="mt-3" onsubmit="return confirm('Delete this stat?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600">No stats yet.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('characters.stats.store', $character) }}" class="grid gap-3 border-t border-gray-200 pt-6 md:grid-cols-[1fr_120px_auto] md:items-end">
                        @csrf
                        <div>
                            <x-input-label for="new-stat-name" value="Name" />
                            <x-text-input id="new-stat-name" name="stat[name]" type="text" class="mt-1 block w-full" :value="old('stat.name')" required />
                            <x-input-error :messages="$errors->get('stat.name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="new-stat-value" value="Value" />
                            <x-text-input id="new-stat-value" name="stat[value]" type="number" min="1" class="mt-1 block w-full" :value="old('stat.value')" required />
                            <x-input-error :messages="$errors->get('stat.value')" class="mt-2" />
                        </div>
                        <x-primary-button>Add Stat</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="space-y-6 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Items</h3>

                    <div class="space-y-3">
                        @forelse ($character->items as $item)
                            <div class="rounded-md border border-gray-200 p-4">
                                <form method="POST" action="{{ route('characters.items.update', [$character, $item]) }}" class="grid gap-3 md:grid-cols-[1fr_1fr_120px_auto] md:items-end">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <x-input-label for="item-name-{{ $item->id }}" value="Name" />
                                        <x-text-input id="item-name-{{ $item->id }}" name="items[{{ $item->id }}][name]" type="text" class="mt-1 block w-full" :value="old('items.' . $item->id . '.name', $item->name)" required />
                                        <x-input-error :messages="$errors->get('items.' . $item->id . '.name')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="item-type-{{ $item->id }}" value="Type" />
                                        <x-text-input id="item-type-{{ $item->id }}" name="items[{{ $item->id }}][type]" type="text" class="mt-1 block w-full" :value="old('items.' . $item->id . '.type', $item->type)" required />
                                        <x-input-error :messages="$errors->get('items.' . $item->id . '.type')" class="mt-2" />
                                    </div>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input name="items[{{ $item->id }}][equipped]" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('items.' . $item->id . '.equipped', $item->equipped))>
                                        Equipped
                                    </label>
                                    <x-primary-button>Update</x-primary-button>
                                </form>
                                <div class="mt-3 flex items-center gap-4">
                                    <span class="text-sm text-gray-600">Status: {{ $item->equipped ? 'Equipped' : 'Stored' }}</span>
                                    <form method="POST" action="{{ route('characters.items.toggle-equipped', [$character, $item]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            Toggle Equipped
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('characters.items.destroy', [$character, $item]) }}" onsubmit="return confirm('Delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600">No items yet.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('characters.items.store', $character) }}" class="grid gap-3 border-t border-gray-200 pt-6 md:grid-cols-[1fr_1fr_120px_auto] md:items-end">
                        @csrf
                        <div>
                            <x-input-label for="new-item-name" value="Name" />
                            <x-text-input id="new-item-name" name="item[name]" type="text" class="mt-1 block w-full" :value="old('item.name')" required />
                            <x-input-error :messages="$errors->get('item.name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="new-item-type" value="Type" />
                            <x-text-input id="new-item-type" name="item[type]" type="text" class="mt-1 block w-full" :value="old('item.type')" required />
                            <x-input-error :messages="$errors->get('item.type')" class="mt-2" />
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input name="item[equipped]" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('item.equipped'))>
                            Equipped
                        </label>
                        <x-primary-button>Add Item</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
