<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character);

        $validated = $request->validate([
            'item.name' => ['required', 'string', 'max:255'],
            'item.type' => ['required', 'string', 'max:255'],
            'item.equipped' => ['sometimes', 'boolean'],
        ]);

        $itemData = $validated['item'];

        $character->items()->create([
            'name' => $itemData['name'],
            'type' => $itemData['type'],
            'equipped' => (bool) ($itemData['equipped'] ?? false),
        ]);

        return redirect()->route('characters.show', $character)
            ->with('status', 'Item added.');
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $character = $item->character;
        $this->authorize('update', $character);

        $itemKey = "items.{$item->id}";

        $validated = $request->validate([
            "{$itemKey}.name" => ['required', 'string', 'max:255'],
            "{$itemKey}.type" => ['required', 'string', 'max:255'],
            "{$itemKey}.equipped" => ['sometimes', 'boolean'],
        ]);

        $itemData = data_get($validated, $itemKey);

        $item->update([
            'name' => $itemData['name'],
            'type' => $itemData['type'],
            'equipped' => (bool) ($itemData['equipped'] ?? false),
        ]);

        return redirect()->route('characters.show', $character)
            ->with('status', 'Item updated.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $character = $item->character;
        $this->authorize('update', $character);

        $item->delete();

        return redirect()->route('characters.show', $character)
            ->with('status', 'Item deleted.');
    }

    public function toggleEquipped(Item $item): RedirectResponse
    {
        $character = $item->character;
        $this->authorize('update', $character);

        $item->update([
            'equipped' => ! $item->equipped,
        ]);

        return redirect()->route('characters.show', $character)
            ->with('status', 'Item updated.');
    }
}
