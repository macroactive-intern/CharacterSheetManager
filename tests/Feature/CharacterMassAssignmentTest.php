<?php

use App\Models\Character;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('ignores user_id from the request body', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    actingAs($attacker)->post('/characters', [
        'name'    => 'Hacked',
        'class'   => 'Rogue',
        'level'   => 1,
        'user_id' => $owner->id,
    ]);

    expect(Character::where('user_id', $owner->id)->count())->toBe(0);
});
