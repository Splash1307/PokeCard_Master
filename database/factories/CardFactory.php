<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Set;
use App\Models\Rarity;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'localId' => fake()->unique()->bothify('??##'),
            'image' => fake()->imageUrl(),
            'set_id' => Set::factory(),
            'rarity_id' => Rarity::factory(),
            'primaryType_id' => Type::factory(),
            'secondaryType_id' => null,
        ];
    }
}
