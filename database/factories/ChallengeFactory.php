<?php

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 week');
        $end   = (clone $start)->modify('+1 month');

        return [
            'title'       => $this->faker->sentence(3),
            'description' => $this->faker->sentence(8),
            'start_date'  => $start,
            'end_date'    => $end,
            'status'      => $this->faker->randomElement(['draft', 'active', 'completed']),
            'reward'      => $this->faker->numberBetween(100, 5000),
        ];
    }
}
