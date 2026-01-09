<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\User;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'nbCard' => fake()->numberBetween(1, 10),
        ];
    }
}
