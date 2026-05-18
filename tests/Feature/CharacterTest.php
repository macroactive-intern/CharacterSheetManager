<?php

use App\Models\Character;
use App\Models\Item;
use App\Models\Stat;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

it('redirects guests away from character routes', function () {
    get('/characters')->assertRedirect('/login');
});

it('lets a user create a character', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('characters.store'), ['name' => 'Astra', 'class' => 'Wizard', 'level' => 5])
        ->assertRedirect(route('characters.show', Character::where('name', 'Astra')->first()));
});

it('only shows a user their own characters on the index', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    $other->characters()->create(['name' => 'Hidden', 'class' => 'Rogue', 'level' => 3]);
    $user->characters()->create(['name' => 'Astra', 'class' => 'Wizard', 'level' => 5]);

    actingAs($user)->get(route('characters.index'))
        ->assertOk()
        ->assertSee('Astra')
        ->assertDontSee('Hidden');
});

it('forbids viewing or editing another user\'s character', function () {
    $user      = User::factory()->create();
    $other     = User::factory()->create();
    $character = $other->characters()->create(['name' => 'Locked', 'class' => 'Paladin', 'level' => 2]);

    actingAs($user)->get(route('characters.show', $character))->assertForbidden();

    actingAs($user)->put(route('characters.update', $character), [
        'name' => 'Changed', 'class' => 'Paladin', 'level' => 3,
    ])->assertForbidden();

    expect(Character::find($character->id)->name)->toBe('Locked');
});

it('assigns a new stat to the route character ignoring character_id in the body', function () {
    $user      = User::factory()->create();
    $character = $user->characters()->create(['name' => 'Astra', 'class' => 'Wizard', 'level' => 5]);
    $otherChar = User::factory()->create()->characters()->create(['name' => 'Mira', 'class' => 'Ranger', 'level' => 4]);

    actingAs($user)
        ->post(route('characters.stats.store', $character), [
            'stat'         => ['name' => 'Strength', 'value' => 10],
            'character_id' => $otherChar->id,
        ])
        ->assertRedirect(route('characters.show', $character));

    expect(Stat::where('name', 'Strength')->first()->character->is($character))->toBeTrue();
});

it('rejects a stat that would push the total over 100', function () {
    $user      = User::factory()->create();
    $character = $user->characters()->create(['name' => 'Astra', 'class' => 'Wizard', 'level' => 5]);
    $character->stats()->create(['name' => 'Strength', 'value' => 60]);

    actingAs($user)
        ->post(route('characters.stats.store', $character), [
            'stat' => ['name' => 'Dexterity', 'value' => 41],
        ])
        ->assertSessionHasErrors('stat.value');
});

it('enforces unique stat names per character', function () {
    $user      = User::factory()->create();
    $character = Character::factory()->for($user)->create();
    $character->stats()->create(['name' => 'Strength', 'value' => 10]);

    actingAs($user)
        ->post(route('characters.stats.store', $character), [
            'stat' => ['name' => 'Strength', 'value' => 20],
        ])
        ->assertSessionHasErrors('stat.name');
});

it('forbids updating a stat on another user\'s character', function () {
    $user      = User::factory()->create();
    $otherChar = User::factory()->create()->characters()->create(['name' => 'Mira', 'class' => 'Ranger', 'level' => 4]);
    $otherStat = $otherChar->stats()->create(['name' => 'Luck', 'value' => 10]);

    actingAs($user)
        ->put(route('stats.update', $otherStat), [
            'stats' => [$otherStat->id => ['name' => 'Luck', 'value' => 10]],
        ])
        ->assertForbidden();
});

it('assigns a new item to the route character ignoring character_id in the body', function () {
    $user      = User::factory()->create();
    $character = $user->characters()->create(['name' => 'Astra', 'class' => 'Wizard', 'level' => 5]);
    $otherChar = User::factory()->create()->characters()->create(['name' => 'Mira', 'class' => 'Ranger', 'level' => 4]);

    actingAs($user)
        ->post(route('characters.items.store', $character), [
            'item'         => ['name' => 'Staff', 'type' => 'Weapon', 'equipped' => '1'],
            'character_id' => $otherChar->id,
        ])
        ->assertRedirect(route('characters.show', $character));

    $item = Item::where('name', 'Staff')->first();
    expect($item->character->is($character))->toBeTrue();
    expect($item->equipped)->toBeTrue();
});

it('can toggle an item\'s equipped status', function () {
    $user      = User::factory()->create();
    $character = $user->characters()->create(['name' => 'Astra', 'class' => 'Wizard', 'level' => 5]);
    $item      = $character->items()->create(['name' => 'Staff', 'type' => 'Weapon', 'equipped' => true]);

    actingAs($user)
        ->patch(route('items.toggle-equipped', $item))
        ->assertRedirect(route('characters.show', $character));

    expect($item->fresh()->equipped)->toBeFalse();
});

it('forbids deleting an item on another user\'s character', function () {
    $user      = User::factory()->create();
    $otherChar = User::factory()->create()->characters()->create(['name' => 'Mira', 'class' => 'Ranger', 'level' => 4]);
    $otherItem = $otherChar->items()->create(['name' => 'Bow', 'type' => 'Weapon']);

    actingAs($user)
        ->delete(route('items.destroy', $otherItem))
        ->assertForbidden();
});

it('cascades character deletion to stats and items', function () {
    $user      = User::factory()->create();
    $character = $user->characters()->create(['name' => 'Astra', 'class' => 'Wizard', 'level' => 5]);
    $stat      = $character->stats()->create(['name' => 'Strength', 'value' => 20]);
    $item      = $character->items()->create(['name' => 'Staff', 'type' => 'Weapon']);

    actingAs($user)
        ->delete(route('characters.destroy', $character))
        ->assertRedirect(route('characters.index'));

    expect(Character::find($character->id))->toBeNull();
    expect(Stat::find($stat->id))->toBeNull();
    expect(Item::find($item->id))->toBeNull();
});

it('provides working factories for characters, stats, and items', function () {
    $character = Character::factory()->create();
    $stat      = Stat::factory()->for($character)->create();
    $item      = Item::factory()->for($character)->create();

    expect($stat->character->is($character))->toBeTrue();
    expect($item->character->is($character))->toBeTrue();
});

it('can seed more than six stats with the factory', function () {
    expect(Stat::factory()->count(7)->create())->toHaveCount(7);
});
