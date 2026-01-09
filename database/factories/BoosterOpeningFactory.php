<?php

namespace Database\Factories;

use App\Models\BoosterOpening;
use App\Models\User;
use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoosterOpeningFactory extends Factory
{
    protected $model = BoosterOpening::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'set_id' => Set::factory(),
            'opened_at' => now(),
        ];
    }
}
