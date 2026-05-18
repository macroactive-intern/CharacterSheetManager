<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Character::class);

        $characters = auth()->user()
            ->characters()
            ->latest()
            ->get();

        return view('characters.index', compact('characters'));
    }

    public function create(): View
    {
        $this->authorize('create', Character::class);

        return view('characters.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Character::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $character = auth()->user()->characters()->create($validated);

        return redirect()->route('characters.show', $character)
            ->with('status', 'Character created.');
    }

    public function show(Character $character): View
    {
        $this->authorize('view', $character);

        $character->load(['stats', 'items']);

        return view('characters.show', compact('character'));
    }

    public function edit(Character $character): View
    {
        $this->authorize('update', $character);

        return view('characters.edit', compact('character'));
    }

    public function update(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $character->update($validated);

        return redirect()->route('characters.show', $character)
            ->with('status', 'Character updated.');
    }

    public function destroy(Character $character): RedirectResponse
    {
        $this->authorize('delete', $character);

        $character->delete();

        return redirect()->route('characters.index')
            ->with('status', 'Character deleted.');
    }
}
