<?php

namespace Database\Factories;

use App\Models\ChallengeRequirement;
use App\Models\Challenge;
use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeRequirementFactory extends Factory
{
    protected $model = ChallengeRequirement::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['CARD_LIST','OPEN_PACKS','OWN_CARDS']);

        // Si c'est CARD_LIST, target_count = 1, sinon entre 1 et 20
        $targetCount = $type === 'CARD_LIST'
            ? 1
            : $this->faker->numberBetween(1, 20);

        return [
            'challenge_id'        => Challenge::inRandomOrder()->first()->id ?? Challenge::factory(),
            'type'                => $type,
            'set_id'              => in_array($type, ['OPEN_PACKS','OWN_CARDS'])
                ? (Set::inRandomOrder()->first()->id ?? Set::factory())
                : null,
            'target_count'        => $targetCount,
        ];
    }
}

