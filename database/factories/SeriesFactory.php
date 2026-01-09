<?php

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeriesFactory extends Factory
{
    protected $model = Series::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'abbreviation' => fake()->unique()->bothify('???-##'),
            'logo' => null,
        ];
    }
}
