<?php

namespace Database\Factories;

use App\Models\Set;
use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

class SetFactory extends Factory
{
    protected $model = Set::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'abbreviation' => fake()->unique()->bothify('SET-###'),
            'serie_id' => Series::factory(),
            'logo' => null,
        ];
    }
}
