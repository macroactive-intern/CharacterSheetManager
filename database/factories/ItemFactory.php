<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'character_id' => Character::factory(),
            'name' => fake()->word(),
            'type' => fake()->randomElement(['Weapon', 'Armor', 'Potion', 'Trinket']),
            'equipped' => fake()->boolean(),
        ];
    }
}
