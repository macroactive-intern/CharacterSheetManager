<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Item;
use App\Models\Stat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterSheetManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_character_routes(): void
    {
        $this->get('/characters')
            ->assertRedirect('/login');
    }

    public function test_user_can_create_and_only_see_their_own_characters(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherUser->characters()->create([
            'name' => 'Hidden',
            'class' => 'Rogue',
            'level' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('characters.store'), [
            'name' => 'Astra',
            'class' => 'Wizard',
            'level' => 5,
        ]);

        $character = Character::where('name', 'Astra')->first();

        $response->assertRedirect(route('characters.show', $character));

        $this->actingAs($user)->get(route('characters.index'))
            ->assertOk()
            ->assertSee('Astra')
            ->assertDontSee('Hidden');
    }

    public function test_users_cannot_access_or_modify_another_users_character(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = $otherUser->characters()->create([
            'name' => 'Locked',
            'class' => 'Paladin',
            'level' => 2,
        ]);

        $this->actingAs($user)->get(route('characters.show', $character))
            ->assertForbidden();

        $this->actingAs($user)->put(route('characters.update', $character), [
            'name' => 'Changed',
            'class' => 'Paladin',
            'level' => 3,
        ])->assertForbidden();

        $this->assertDatabaseHas('characters', [
            'id' => $character->id,
            'name' => 'Locked',
        ]);
    }

    public function test_stats_are_owned_through_parent_character_and_capped_at_100(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = $user->characters()->create([
            'name' => 'Astra',
            'class' => 'Wizard',
            'level' => 5,
        ]);
        $otherCharacter = $otherUser->characters()->create([
            'name' => 'Mira',
            'class' => 'Ranger',
            'level' => 4,
        ]);

        $this->actingAs($user)->post(route('characters.stats.store', $character), [
            'stat' => [
                'name' => 'Strength',
                'value' => 60,
            ],
            'character_id' => $otherCharacter->id,
        ])->assertRedirect(route('characters.show', $character));

        $stat = Stat::where('name', 'Strength')->first();
        $this->assertTrue($stat->character->is($character));

        $this->actingAs($user)->post(route('characters.stats.store', $character), [
            'stat' => [
                'name' => 'Dexterity',
                'value' => 41,
            ],
        ])->assertSessionHasErrors('stat.value');

        $otherStat = $otherCharacter->stats()->create([
            'name' => 'Luck',
            'value' => 10,
        ]);

        $this->actingAs($user)->put(route('stats.update', $otherStat), [
            'stats' => [
                $otherStat->id => [
                    'name' => 'Luck',
                    'value' => 10,
                ],
            ],
        ])->assertForbidden();
    }

    public function test_stat_names_must_be_unique_per_character(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create();

        $character->stats()->create([
            'name' => 'Strength',
            'value' => 10,
        ]);

        $this->actingAs($user)->post(route('characters.stats.store', $character), [
            'stat' => [
                'name' => 'Strength',
                'value' => 20,
            ],
        ])->assertSessionHasErrors('stat.name');
    }

    public function test_items_are_owned_through_parent_and_can_toggle_equipped(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $character = $user->characters()->create([
            'name' => 'Astra',
            'class' => 'Wizard',
            'level' => 5,
        ]);
        $otherCharacter = $otherUser->characters()->create([
            'name' => 'Mira',
            'class' => 'Ranger',
            'level' => 4,
        ]);

        $this->actingAs($user)->post(route('characters.items.store', $character), [
            'item' => [
                'name' => 'Staff',
                'type' => 'Weapon',
                'equipped' => '1',
            ],
            'character_id' => $otherCharacter->id,
        ])->assertRedirect(route('characters.show', $character));

        $item = Item::where('name', 'Staff')->first();
        $this->assertTrue($item->character->is($character));
        $this->assertTrue($item->equipped);

        $this->actingAs($user)->patch(route('items.toggle-equipped', $item))
            ->assertRedirect(route('characters.show', $character));

        $this->assertFalse($item->fresh()->equipped);

        $otherItem = $otherCharacter->items()->create([
            'name' => 'Bow',
            'type' => 'Weapon',
        ]);

        $this->actingAs($user)->delete(route('items.destroy', $otherItem))
            ->assertForbidden();
    }

    public function test_deleting_character_cascades_to_stats_and_items(): void
    {
        $user = User::factory()->create();
        $character = $user->characters()->create([
            'name' => 'Astra',
            'class' => 'Wizard',
            'level' => 5,
        ]);
        $stat = $character->stats()->create([
            'name' => 'Strength',
            'value' => 20,
        ]);
        $item = $character->items()->create([
            'name' => 'Staff',
            'type' => 'Weapon',
        ]);

        $this->actingAs($user)->delete(route('characters.destroy', $character))
            ->assertRedirect(route('characters.index'));

        $this->assertDatabaseMissing('characters', ['id' => $character->id]);
        $this->assertDatabaseMissing('stats', ['id' => $stat->id]);
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_character_stat_and_item_factories_exist(): void
    {
        $character = Character::factory()->create();
        $stat = Stat::factory()->for($character)->create();
        $item = Item::factory()->for($character)->create();

        $this->assertTrue($stat->character->is($character));
        $this->assertTrue($item->character->is($character));
    }

    public function test_stat_factory_can_create_more_than_six_stats(): void
    {
        $stats = Stat::factory()->count(7)->create();

        $this->assertCount(7, $stats);
    }
}
