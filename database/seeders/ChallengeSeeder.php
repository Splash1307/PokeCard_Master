<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Challenge;
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
                'status' => 'Actif',
                'reward' => 1500,
            ],
            [
                'title' => 'L\'Expédition Kanto',
                'description' => 'Partez à l\'aventure dans la région de Kanto ! Ouvrez des boosters pour découvrir les Pokémon de la première génération et revivez vos souvenirs d\'enfance.',
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(20),
                'status' => 'Actif',
                'reward' => 2000,
            ],
            [
                'title' => 'Maître des Types Feu',
                'description' => 'Prouvez votre affinité avec le type Feu ! Rassemblez une collection impressionnante de Pokémon Feu pour montrer votre passion dévorante.',
                'start_date' => $now->copy()->subDays(3),
                'end_date' => $now->copy()->addDays(27),
                'status' => 'Actif',
                'reward' => 1200,
            ],
            [
                'title' => 'Le Conseil des Quatre',
                'description' => 'Les membres du Conseil des Quatre et les Champions de Ligue vous lancent un défi ! Collectez les cartes Dresseur des personnages les plus emblématiques de l\'univers Pokémon.',
                'start_date' => $now->copy()->subDays(15),
                'end_date' => $now->copy()->addDays(15),
                'status' => 'Actif',
                'reward' => 3000,
            ],
            [
                'title' => 'Pokédex Complet : Édition Johto',
                'description' => 'Complétez votre Pokédex avec les Pokémon de la région de Johto ! Du petit Germignon au légendaire Lugia, attrapez-les tous !',
                'start_date' => $now->copy()->subDays(7),
                'end_date' => $now->copy()->addDays(23),
                'status' => 'Actif',
                'reward' => 2500,
            ],
            [
                'title' => 'Les Pokémon Légendaires',
                'description' => 'Seuls les dresseurs les plus dévoués peuvent compléter ce défi ! Rassemblez les cartes des Pokémon Légendaires et Fabuleux pour prouver votre valeur.',
                'start_date' => $now->copy()->subDays(2),
                'end_date' => $now->copy()->addDays(28),
                'status' => 'Actif',
                'reward' => 5000,
            ],
            [
                'title' => 'Duo Dynamique : Pikachu et Évoli',
                'description' => 'Les mascottes de Pokémon vous défient ! Collectionnez Pikachu, Évoli et toutes leurs évolutions pour ce défi adorable.',
                'start_date' => $now->copy()->subDays(8),
                'end_date' => $now->copy()->addDays(22),
                'status' => 'Actif',
                'reward' => 1800,
            ],
            [
                'title' => 'Chasseur de Brillants',
                'description' => 'La chance sourit aux audacieux ! Ouvrez de nombreux boosters dans l\'espoir de découvrir des cartes rares et holographiques éblouissantes.',
                'start_date' => $now->copy()->subDays(12),
                'end_date' => $now->copy()->addDays(18),
                'status' => 'Actif',
                'reward' => 2200,
            ],
        ];

        foreach ($challenges as $challengeData) {
            Challenge::create($challengeData);
        }
    }
}
