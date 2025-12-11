<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Set;
use App\Models\Card;
use App\Models\Challenge;
use App\Models\ChallengeRequirement;
use App\Models\RequirementCard;
use App\Models\UserChallenge;
use App\Models\UserRequirementProgress;
use App\Models\ChallengeDonation;
use Illuminate\Support\Collection;

class ChallengeStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Données de base
        $users      = User::all();                          // Tous les joueurs existants
        $challenges = Challenge::where('status', 'Actif')->get(); // Défis actifs
        $sets       = Set::all();                           // Tous les sets
        $cards      = Card::all();                          // Toutes les cartes

        if ($challenges->isEmpty() || $users->isEmpty() || $cards->isEmpty()) {
            return;
        }

        foreach ($challenges as $challenge) {
            // ---------- 1. CRÉATION DES REQUIREMENTS DU DÉFI ----------

            $nbReq = rand(1, 3); // 1 à 3 requirements par défi

            for ($i = 0; $i < $nbReq; $i++) {
                $typeOptions = ['CARD_LIST','OPEN_PACKS','OWN_CARDS'];
                $type = $typeOptions[array_rand($typeOptions)];

                $set  = $sets->random(); // utile uniquement pour OPEN_PACKS / OWN_CARDS

                $requirement = ChallengeRequirement::create([
                    'challenge_id'  => $challenge->id,
                    'type'          => $type,
                    // Pour CARD_LIST, pas de set_id (NULL)
                    'set_id'        => in_array($type, ['OPEN_PACKS','OWN_CARDS']) ? $set->id : null,
                    'target_count'  => $type === 'CARD_LIST' ? rand(3, 6) : rand(5, 15),
                ]);

                // ---------- 2. SI LISTE DE CARTES (CARD_LIST) : CHOIX SUR TOUTES LES CARTES ----------
                if ($type === 'CARD_LIST') {
                    // On choisit target_count cartes dans tout l'ensemble des cartes (tous sets confondus)
                    /** @var Collection $selectedCards */
                    $selectedCards = $cards->random($requirement->target_count);

                    foreach ($selectedCards as $card) {
                        RequirementCard::create([
                            'requirement_id' => $requirement->id,
                            'card_id'        => $card->id,
                            'required_qty'   => 1,
                        ]);
                    }
                }
            }

            // ---------- 3. ASSIGNATION DES UTILISATEURS AU DÉFI ----------

            // OPTION A : quelques utilisateurs aléatoires
            // $participants = $users->random(min(5, $users->count()));

            // OPTION B : TOUS les utilisateurs participent au défi
            $participants = $users;

            foreach ($participants as $user) {
                $userChallenge = UserChallenge::create([
                    'user_id'      => $user->id,
                    'challenge_id' => $challenge->id,
                    'status'       => 'En cours',
                    'completed_at' => null,
                    'claimed_at'   => null,
                ]);

                // ---------- 4. PROGRESSION PAR REQUIREMENT ----------

                foreach ($challenge->requirements as $req) {
                    $progress = rand(0, $req->target_count);

                    UserRequirementProgress::create([
                        'user_id'        => $user->id,
                        'requirement_id' => $req->id,
                        'progress_count' => $progress,
                        'updated_at'     => now(),
                        'completed_at'   => $progress >= $req->target_count ? now() : null,
                    ]);

                    // ---------- 5. DONS COHÉRENTS POUR LES CARD_LIST ----------
                    if ($req->type === 'CARD_LIST' && $progress > 0) {
                        // Liste des id des cartes cibles de ce requirement
                        $targetCards = $req->requirementCards->pluck('card_id')->toArray();

                        // On tire "progress" cartes distinctes parmi ces cibles
                        $donatedCards = collect($targetCards)
                            ->shuffle()              // on mélange l'ordre
                            ->take($progress);       // on en garde 'progress'

                        foreach ($donatedCards as $cardId) {
                            ChallengeDonation::create([
                                'user_id'      => $user->id,
                                'challenge_id' => $challenge->id,
                                'card_id'      => $cardId,
                                'qty'          => 1,
                                // Date de don pseudo-aléatoire : aujourd'hui ou jusqu'à 5 jours en arrière
                                'donated_at'   => now()->subDays(rand(0, 5)),
                            ]);
                        }
                    }
                }

                // ---------- 6. MARQUER LE DÉFI COMME TERMINÉ SI TOUS LES REQUIREMENTS SONT COMPLETS ----------

                $allCompleted = $challenge->requirements->every(function ($req) use ($user) {
                    $urp = UserRequirementProgress::where('user_id', $user->id)
                        ->where('requirement_id', $req->id)
                        ->first();

                    return $urp && $urp->completed_at !== null;
                });

                if ($allCompleted) {
                    $userChallenge->update([
                        'status'       => 'Completé',
                        'completed_at' => now(),
                    ]);
                }
            }
        }
    }
}
