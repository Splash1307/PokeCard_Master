<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Card;
use App\Models\Collection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les utilisateurs
        $users = User::all();

        if ($users->count() === 0) {
            $this->command->warn('Aucun utilisateur trouvé dans la base de données.');
            return;
        }

        // Récupérer toutes les cartes disponibles
        $cards = Card::all();

        if ($cards->count() < 10) {
            $this->command->warn('Pas assez de cartes dans la base de données. Il faut au moins 10 cartes.');
            return;
        }

        $this->command->info("Attribution de 10 cartes à chaque utilisateur ({$users->count()} utilisateurs)...");

        foreach ($users as $user) {
            // Choisir 10 cartes aléatoires
            $randomCards = $cards->random(10);

            foreach ($randomCards as $card) {
                // Créer ou mettre à jour l'entrée dans la collection
                Collection::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'card_id' => $card->id,
                    ],
                    [
                        'nbCard' => 1, // On donne 1 exemplaire de chaque carte
                    ]
                );
            }

            $this->command->info("✓ 10 cartes attribuées à {$user->pseudo}");
        }

        $this->command->info("✓ Attribution terminée pour {$users->count()} utilisateurs !");
    }
}
