<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Character') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('characters.store') }}" class="space-y-6 p-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="class" value="Class" />
                        <x-text-input id="class" name="class" type="text" class="mt-1 block w-full" :value="old('class')" required />
                        <x-input-error :messages="$errors->get('class')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="level" value="Level" />
                        <x-text-input id="level" name="level" type="number" min="1" max="100" class="mt-1 block w-full" :value="old('level', 1)" required />
                        <x-input-error :messages="$errors->get('level')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('characters.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        <x-primary-button>Create</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
