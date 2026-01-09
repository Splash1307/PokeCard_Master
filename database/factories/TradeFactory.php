<?php

namespace Database\Factories;

use App\Models\Trade;
use App\Models\User;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'offered_card_id' => Card::factory(),
            'requested_card_id' => Card::factory(),
            'responder_id' => null,
            'status' => 'pending',
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'responder_id' => null,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'responder_id' => User::factory(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
