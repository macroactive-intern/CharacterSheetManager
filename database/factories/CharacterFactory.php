<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Character>
 */
class CharacterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'class' => fake()->randomElement(['Fighter', 'Wizard', 'Rogue', 'Cleric']),
            'level' => fake()->numberBetween(1, 20),
        ];
    }
}
