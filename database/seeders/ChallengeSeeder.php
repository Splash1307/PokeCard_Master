<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Challenge;
use App\Models\ChallengeRequirement;
use Carbon\Carbon;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $challenges = [
            [
                'title' => 'Les Starters Légendaires',
                'description' => 'Collectionnez les cartes des Pokémon de départ emblématiques de chaque génération. Bulbizarre, Salamèche, Carapuce et leurs évolutions vous attendent !',
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(25),
                'status' => 'En attente',
                'reward' => 1500,
                'requirements' => [
                    ['type' => 'OWN_CARDS', 'set_id' => 1, 'target_count' => 50],
                ],
            ],
            [
                'title' => 'L\'Expédition Kanto',
                'description' => 'Partez à l\'aventure dans la région de Kanto ! Ouvrez des boosters pour découvrir les Pokémon de la première génération et revivez vos souvenirs d\'enfance.',
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(20),
                'status' => 'En attente',
                'reward' => 2000,
                'requirements' => [
                    ['type' => 'OPEN_PACKS', 'set_id' => 1, 'target_count' => 10],
                ],
            ],
            [
                'title' => 'Maître des Types Feu',
                'description' => 'Prouvez votre affinité avec le type Feu ! Rassemblez une collection impressionnante de Pokémon Feu pour montrer votre passion dévorante.',
                'start_date' => $now->copy()->subDays(3),
                'end_date' => $now->copy()->addDays(27),
                'status' => 'En attente',
                'reward' => 1200,
                'requirements' => [
                    ['type' => 'OWN_CARDS', 'set_id' => 2, 'target_count' => 30],
                ],
            ],
            [
                'title' => 'Le Conseil des Quatre',
                'description' => 'Les membres du Conseil des Quatre et les Champions de Ligue vous lancent un défi ! Collectez les cartes Dresseur des personnages les plus emblématiques de l\'univers Pokémon.',
                'start_date' => $now->copy()->subDays(15),
                'end_date' => $now->copy()->addDays(15),
                'status' => 'En attente',
                'reward' => 3000,
                'requirements' => [
                    ['type' => 'OWN_CARDS', 'set_id' => 3, 'target_count' => 100],
                ],
            ],
            [
                'title' => 'Pokédex Complet : Édition Johto',
                'description' => 'Complétez votre Pokédex avec les Pokémon de la région de Johto ! Du petit Germignon au légendaire Lugia, attrapez-les tous !',
                'start_date' => $now->copy()->subDays(7),
                'end_date' => $now->copy()->addDays(23),
                'status' => 'En attente',
                'reward' => 2500,
                'requirements' => [
                    ['type' => 'OPEN_PACKS', 'set_id' => 2, 'target_count' => 15],
                    ['type' => 'OWN_CARDS', 'set_id' => 2, 'target_count' => 75],
                ],
            ],
            [
                'title' => 'Les Pokémon Légendaires',
                'description' => 'Seuls les dresseurs les plus dévoués peuvent compléter ce défi ! Rassemblez les cartes des Pokémon Légendaires et Fabuleux pour prouver votre valeur.',
                'start_date' => $now->copy()->subDays(2),
                'end_date' => $now->copy()->addDays(28),
                'status' => 'En attente',
                'reward' => 5000,
                'requirements' => [
                    ['type' => 'OWN_CARDS', 'set_id' => 4, 'target_count' => 150],
                ],
            ],
            [
                'title' => 'Duo Dynamique : Pikachu et Évoli',
                'description' => 'Les mascottes de Pokémon vous défient ! Collectionnez Pikachu, Évoli et toutes leurs évolutions pour ce défi adorable.',
                'start_date' => $now->copy()->subDays(8),
                'end_date' => $now->copy()->addDays(22),
                'status' => 'En attente',
                'reward' => 1800,
                'requirements' => [
                    ['type' => 'OPEN_PACKS', 'set_id' => 3, 'target_count' => 8],
                ],
            ],
            [
                'title' => 'Chasseur de Brillants',
                'description' => 'La chance sourit aux audacieux ! Ouvrez de nombreux boosters dans l\'espoir de découvrir des cartes rares et holographiques éblouissantes.',
                'start_date' => $now->copy()->subDays(12),
                'end_date' => $now->copy()->addDays(18),
                'status' => 'En attente',
                'reward' => 2200,
                'requirements' => [
                    ['type' => 'OPEN_PACKS', 'set_id' => 5, 'target_count' => 20],
                ],
            ],
        ];

        foreach ($challenges as $challengeData) {
            // Extraire les requirements avant de créer le challenge
            $requirements = $challengeData['requirements'] ?? [];
            unset($challengeData['requirements']);

            // Créer le challenge
            $challenge = Challenge::create($challengeData);

            // Créer les requirements associés
            foreach ($requirements as $reqData) {
                ChallengeRequirement::create([
                    'challenge_id' => $challenge->id,
                    'type' => $reqData['type'],
                    'set_id' => $reqData['set_id'] ?? null,
                    'target_count' => $reqData['target_count'],
                ]);
            }
        }
    }
}
