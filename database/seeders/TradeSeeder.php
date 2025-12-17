<?php

namespace Database\Seeders;

use App\Models\Trade;
use App\Models\User;
use App\Models\Card;
use App\Models\Collection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les utilisateurs (sauf les admins role_id = 1)
        $users = User::where('role_id', '!=', 1)->get();

        if ($users->count() < 2) {
            $this->command->warn('Pas assez d\'utilisateurs pour créer des échanges. Il faut au moins 2 utilisateurs non-admin.');
            return;
        }

        // Récupérer toutes les cartes disponibles
        $cards = Card::all();

        if ($cards->count() < 10) {
            $this->command->warn('Pas assez de cartes pour créer des échanges. Il faut au moins 10 cartes.');
            return;
        }

        $this->command->info('Création de 100 échanges...');

        $createdCount = 0;
        $attempts = 0;
        $maxAttempts = 500; // Pour éviter une boucle infinie

        while ($createdCount < 100 && $attempts < $maxAttempts) {
            $attempts++;

            // Choisir un créateur aléatoire
            $creator = $users->random();

            // Récupérer les cartes que le créateur possède
            $creatorCards = Collection::where('user_id', $creator->id)
                ->where('nbCard', '>', 0)
                ->pluck('card_id')
                ->toArray();

            // Si le créateur ne possède aucune carte, passer à un autre utilisateur
            if (empty($creatorCards)) {
                continue;
            }

            // Choisir une carte aléatoire que le créateur possède
            $offeredCardId = $creatorCards[array_rand($creatorCards)];

            // Choisir une carte demandée aléatoire (différente de la carte offerte)
            $availableRequestedCards = $cards->where('id', '!=', $offeredCardId)->pluck('id')->toArray();
            $requestedCardId = $availableRequestedCards[array_rand($availableRequestedCards)];

            // Créer l'échange
            Trade::create([
                'creator_id' => $creator->id,
                'offered_card_id' => $offeredCardId,
                'requested_card_id' => $requestedCardId,
                'status' => 'pending',
            ]);

            $createdCount++;

            if ($createdCount % 20 == 0) {
                $this->command->info("$createdCount échanges créés...");
            }
        }

        if ($createdCount < 100) {
            $this->command->warn("Seulement $createdCount échanges ont pu être créés (manque de cartes dans les collections des utilisateurs).");
        } else {
            $this->command->info("✓ 100 échanges créés avec succès !");
        }
    }
}
