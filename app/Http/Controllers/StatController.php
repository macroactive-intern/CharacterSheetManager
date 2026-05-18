<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Stat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StatController extends Controller
{
    public function store(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character);

        $validated = $request->validate([
            'stat.name' => ['required', 'string', 'max:255'],
            'stat.value' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $statData = $validated['stat'];

        try {
            DB::transaction(function () use ($character, $statData) {
                $lockedCharacter = Character::whereKey($character->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureStatNameIsUnique($lockedCharacter, $statData['name'], null, 'stat.name');
                $this->ensureStatTotalIsValid($lockedCharacter, (int) $statData['value'], null, 'stat.value');

                $lockedCharacter->stats()->create($statData);
            });
        } catch (QueryException $exception) {
            $this->throwValidationForUniqueStatNameConstraint($exception, 'stat.name');
        }

        return redirect()->route('characters.show', $character)
            ->with('status', 'Stat added.');
    }

    public function update(Request $request, Character $character, Stat $stat): RedirectResponse
    {
        $this->authorize('update', $character);
        $this->ensureStatBelongsToCharacter($character, $stat);

        $statKey = "stats.{$stat->id}";

        $validated = $request->validate([
            "{$statKey}.name" => ['required', 'string', 'max:255'],
            "{$statKey}.value" => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $statData = data_get($validated, $statKey);

        try {
            DB::transaction(function () use ($character, $stat, $statData, $statKey) {
                $lockedCharacter = Character::whereKey($character->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedStat = Stat::whereKey($stat->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureStatBelongsToCharacter($lockedCharacter, $lockedStat);
                $this->ensureStatNameIsUnique($lockedCharacter, $statData['name'], $lockedStat, "{$statKey}.name");
                $this->ensureStatTotalIsValid($lockedCharacter, (int) $statData['value'], $lockedStat, "{$statKey}.value");

                $lockedStat->update($statData);
            });
        } catch (QueryException $exception) {
            $this->throwValidationForUniqueStatNameConstraint($exception, "{$statKey}.name");
        }

        return redirect()->route('characters.show', $character)
            ->with('status', 'Stat updated.');
    }

    public function destroy(Character $character, Stat $stat): RedirectResponse
    {
        $this->authorize('update', $character);
        $this->ensureStatBelongsToCharacter($character, $stat);

        $stat->delete();

        return redirect()->route('characters.show', $character)
            ->with('status', 'Stat deleted.');
    }

    private function ensureStatBelongsToCharacter(Character $character, Stat $stat): void
    {
        if ($stat->character_id !== $character->id) {
            abort(404);
        }
    }

    private function ensureStatTotalIsValid(Character $character, int $value, ?Stat $stat = null, string $errorKey = 'value'): void
    {
        $query = $character->stats();

        if ($stat) {
            $query->where('id', '!=', $stat->id);
        }

        if ($query->sum('value') + $value > 100) {
            throw ValidationException::withMessages([
                $errorKey => 'Total stat points cannot exceed 100.',
            ]);
        }
    }

    private function ensureStatNameIsUnique(Character $character, string $name, ?Stat $stat = null, string $errorKey = 'name'): void
    {
        $query = $character->stats()->where('name', $name);

        if ($stat) {
            $query->where('id', '!=', $stat->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                $errorKey => 'This character already has a stat with that name.',
            ]);
        }
    }

    private function throwValidationForUniqueStatNameConstraint(QueryException $exception, string $errorKey): void
    {
        if (! $this->isUniqueConstraintViolation($exception)) {
            throw $exception;
        }

        throw ValidationException::withMessages([
            $errorKey => 'This character already has a stat with that name.',
        ]);
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $sqlState === '23000'
            || $sqlState === '23505'
            || str_contains($exception->getMessage(), 'UNIQUE constraint failed')
            || str_contains($exception->getMessage(), 'Duplicate entry');
    }
}
