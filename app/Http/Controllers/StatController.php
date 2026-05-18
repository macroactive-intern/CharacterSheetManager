<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStatRequest;
use App\Models\Character;
use App\Models\Stat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StatController extends Controller
{
    public function store(StoreStatRequest $request, Character $character): RedirectResponse
    {
        $character->stats()->create($request->validated()['stat']);

        return redirect()->route('characters.show', $character)
            ->with('status', 'Stat added.');
    }

    public function update(Request $request, Stat $stat): RedirectResponse
    {
        $character = $stat->character;
        $this->authorize('update', $character);

        $statKey = "stats.{$stat->id}";

        $validated = $request->validate([
            "{$statKey}.name" => ['required', 'string', 'max:255'],
            "{$statKey}.value" => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $stat->update(data_get($validated, $statKey));

        return redirect()->route('characters.show', $character)
            ->with('status', 'Stat updated.');
    }

    public function destroy(Stat $stat): RedirectResponse
    {
        $character = $stat->character;
        $this->authorize('update', $character);

        $stat->delete();

        return redirect()->route('characters.show', $character)
            ->with('status', 'Stat deleted.');
    }
}
