<?php

namespace Database\Factories;

use App\Models\Rarity;
use Illuminate\Database\Eloquent\Factories\Factory;

class RarityFactory extends Factory
{
    protected $model = Rarity::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'percentageSpawn' => fake()->numberBetween(1, 100),
            'price' => fake()->numberBetween(10, 1000),
        ];
    }
}
