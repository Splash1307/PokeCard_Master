<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\UserRequirementProgress;
use App\Models\ChallengeRequirement;
use App\Models\ChallengeDonation;
use App\Models\Collection;
use App\Models\Trade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    /**
     * Afficher tous les challenges actifs pour l'utilisateur
     */
    public function index()
    {
        $user = auth()->user();

        // Récupérer tous les challenges actifs
        $challenges = Challenge::with([
            'requirements.set',
            'requirements.requirementCards.card.set'
        ])
            ->where('status', 'Actif')
            ->get()
            ->map(function ($challenge) use ($user) {
                // Récupérer ou créer la participation de l'utilisateur
                $userChallenge = UserChallenge::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'challenge_id' => $challenge->id,
                    ],
                    [
                        'status' => 'En cours',
                    ]
                );

                // Calculer la progression totale
                $requirements = $challenge->requirements->map(function ($req) use ($user, $challenge) {
                    // Récupérer ou créer la progression du requirement
                    $progress = UserRequirementProgress::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'requirement_id' => $req->id,
                        ],
                        [
                            'progress_count' => 0,
                            'updated_at' => now(),
                        ]
                    );

                    return [
                        'id' => $req->id,
                        'type' => $req->type,
                        'set_id' => $req->set_id,
                        'set_name' => $req->set->name ?? null,
                        'target_count' => $req->target_count,
                        'progress_count' => $progress->progress_count,
                        'completed' => $progress->completed_at !== null,
                        'cards' => $req->requirementCards->map(function ($reqCard) use ($user, $challenge) {
                            // Récupérer la quantité possédée par l'utilisateur
                            $collection = Collection::where('user_id', $user->id)
                                ->where('card_id', $reqCard->card_id)
                                ->first();
                            $ownedQty = $collection ? $collection->nbCard : 0;

                            // Récupérer la quantité déjà donnée au challenge
                            $donatedQty = ChallengeDonation::where('user_id', $user->id)
                                ->where('challenge_id', $challenge->id)
                                ->where('card_id', $reqCard->card_id)
                                ->sum('qty');

                            return [
                                'card_id' => $reqCard->card_id,
                                'card_name' => $reqCard->card->name,
                                'card_image' => $reqCard->card->image,
                                'card_set_name' => $reqCard->card->set->name ?? null,
                                'required_qty' => $reqCard->required_qty,
                                'owned_qty' => $ownedQty,
                                'donated_qty' => $donatedQty,
                            ];
                        })->toArray(),
                    ];
                });

                // Vérifier si tous les requirements sont complétés
                $allCompleted = $requirements->every(fn($req) => $req['completed']);

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'start_date' => $challenge->start_date?->format('Y-m-d'),
                    'end_date' => $challenge->end_date?->format('Y-m-d'),
                    'reward' => $challenge->reward,
                    'status' => $userChallenge->status,
                    'completed_at' => $userChallenge->completed_at?->format('Y-m-d H:i'),
                    'claimed_at' => $userChallenge->claimed_at?->format('Y-m-d H:i'),
                    'can_claim' => $allCompleted && $userChallenge->status === 'Complété',
                    'requirements' => $requirements->toArray(),
                ];
            });

        return Inertia::render('Challenges/Index', [
            'challenges' => $challenges
        ]);
    }

    /**
     * Réclamer la récompense d'un challenge complété
     */
    public function claim(Challenge $challenge)
    {
        $user = auth()->user();

        // Récupérer la participation de l'utilisateur
        $userChallenge = UserChallenge::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->first();

        if (!$userChallenge) {
            return back()->withErrors(['error' => 'Vous ne participez pas à ce challenge.']);
        }

        if ($userChallenge->status !== 'Complété') {
            return back()->withErrors(['error' => 'Ce challenge n\'est pas encore complété.']);
        }

        if ($userChallenge->claimed_at !== null) {
            return back()->withErrors(['error' => 'Vous avez déjà réclamé cette récompense.']);
        }

        // Ajouter la récompense
        $user->coin += $challenge->reward;
        $user->save();

        // Marquer comme réclamé
        $userChallenge->update([
            'status' => 'Récompense récupérée',
            'claimed_at' => now(),
        ]);

        return back()->with('success', "Vous avez reçu {$challenge->reward} coins !");
    }

    /**
     * Donner une carte à un challenge
     */
    public function donateCard(Request $request, Challenge $challenge)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,id',
            'qty' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $cardId = $request->card_id;
        $qty = $request->qty;

        // Vérifier que l'utilisateur possède la carte en quantité suffisante
        $collection = Collection::where('user_id', $user->id)
            ->where('card_id', $cardId)
            ->first();

        if (!$collection || $collection->nbCard < $qty) {
            return back()->withErrors(['error' => 'Vous ne possédez pas assez de cette carte.']);
        }

        // Vérifier que la carte fait partie du challenge (type CARD_LIST)
        $requirementCard = null;
        $requirement = null;

        foreach ($challenge->requirements()->where('type', 'CARD_LIST')->get() as $req) {
            $reqCard = $req->requirementCards()->where('card_id', $cardId)->first();
            if ($reqCard) {
                $requirementCard = $reqCard;
                $requirement = $req;
                break;
            }
        }

        if (!$requirementCard) {
            return back()->withErrors(['error' => 'Cette carte ne fait pas partie de ce challenge.']);
        }

        // Vérifier que l'utilisateur n'a pas déjà donné le maximum requis
        $alreadyDonated = ChallengeDonation::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->where('card_id', $cardId)
            ->sum('qty');

        if ($alreadyDonated + $qty > $requirementCard->required_qty) {
            return back()->withErrors(['error' => 'Vous avez déjà donné le maximum requis pour cette carte.']);
        }

        // Créer l'entrée de donation
        ChallengeDonation::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'card_id' => $cardId,
            'qty' => $qty,
            'donated_at' => now(),
        ]);

        // Retirer la carte de la collection
        $collection->nbCard -= $qty;
        if ($collection->nbCard <= 0) {
            $collection->delete();
        } else {
            $collection->save();
        }

        // Annuler les échanges incompatibles pour cette carte
        $this->cancelIncompatibleTrades($user->id, $cardId);

        // Mettre à jour la progression du challenge
        self::checkAndUpdateProgress($user->id);

        return back()->with('success', "Carte donnée au challenge avec succès !");
    }

    /**
     * Attribuer tous les challenges actifs à un nouvel utilisateur
     * Appelé lors de la création d'un compte
     */
    public static function assignActiveChallenges($userId)
    {
        // Récupérer tous les challenges actifs
        $challenges = Challenge::with('requirements')
            ->where('status', 'Actif')
            ->get();

        foreach ($challenges as $challenge) {
            // Créer la participation pour chaque challenge
            UserChallenge::firstOrCreate(
                [
                    'user_id' => $userId,
                    'challenge_id' => $challenge->id,
                ],
                [
                    'status' => 'En cours',
                ]
            );

            // Créer la progression pour chaque requirement
            foreach ($challenge->requirements as $requirement) {
                UserRequirementProgress::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'requirement_id' => $requirement->id,
                    ],
                    [
                        'progress_count' => 0,
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Attribuer un challenge spécifique à tous les utilisateurs
     * Appelé quand un challenge est activé
     */
    public static function assignChallengeToAllUsers($challengeId)
    {
        $challenge = Challenge::with('requirements')->find($challengeId);

        if (!$challenge || $challenge->status !== 'Actif') {
            return;
        }

        // Récupérer tous les utilisateurs
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            // Créer la participation pour ce challenge
            UserChallenge::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'challenge_id' => $challenge->id,
                ],
                [
                    'status' => 'En cours',
                ]
            );

            // Créer la progression pour chaque requirement
            foreach ($challenge->requirements as $requirement) {
                UserRequirementProgress::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'requirement_id' => $requirement->id,
                    ],
                    [
                        'progress_count' => 0,
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Vérifier et mettre à jour la progression d'un utilisateur sur un challenge
     * Appelé automatiquement après ouverture de booster ou autre action
     */
    public static function checkAndUpdateProgress($userId)
    {
        $user = \App\Models\User::find($userId);

        // Récupérer tous les challenges actifs
        $challenges = Challenge::with('requirements.requirementCards')
            ->where('status', 'Actif')
            ->get();

        foreach ($challenges as $challenge) {
            // Récupérer ou créer la participation
            $userChallenge = UserChallenge::firstOrCreate(
                [
                    'user_id' => $userId,
                    'challenge_id' => $challenge->id,
                ],
                [
                    'status' => 'En cours',
                ]
            );

            // Ne pas vérifier si déjà complété
            if ($userChallenge->status !== 'En cours') {
                continue;
            }

            $allRequirementsCompleted = true;

            foreach ($challenge->requirements as $requirement) {
                $progress = UserRequirementProgress::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'requirement_id' => $requirement->id,
                    ],
                    [
                        'progress_count' => 0,
                        'updated_at' => now(),
                    ]
                );

                // Calculer la progression selon le type
                $currentProgress = self::calculateProgress($user, $requirement);

                // Mettre à jour la progression
                $progress->progress_count = $currentProgress;
                $progress->updated_at = now();

                // Marquer comme complété si objectif atteint
                if ($currentProgress >= $requirement->target_count && !$progress->completed_at) {
                    $progress->completed_at = now();
                }

                $progress->save();

                // Vérifier si ce requirement est complété
                if ($currentProgress < $requirement->target_count) {
                    $allRequirementsCompleted = false;
                }
            }

            // Si tous les requirements sont complétés, marquer le challenge comme complété
            if ($allRequirementsCompleted && $userChallenge->status === 'En cours') {
                $userChallenge->update([
                    'status' => 'Complété',
                    'completed_at' => now(),
                ]);
            }
        }
    }

    /**
     * Calculer la progression d'un requirement pour un utilisateur
     */
    private static function calculateProgress($user, ChallengeRequirement $requirement)
    {
        switch ($requirement->type) {
            case 'CARD_LIST':
                // Pour CARD_LIST, compter les cartes données au challenge
                $totalDonated = 0;
                foreach ($requirement->requirementCards as $reqCard) {
                    $donated = ChallengeDonation::where('user_id', $user->id)
                        ->where('challenge_id', $requirement->challenge_id)
                        ->where('card_id', $reqCard->card_id)
                        ->sum('qty');

                    $totalDonated += min($donated, $reqCard->required_qty);
                }
                return $totalDonated;

            case 'OPEN_PACKS':
                // Compter les boosters ouverts de ce set
                $openingsCount = \App\Models\BoosterOpening::where('user_id', $user->id)
                    ->where('set_id', $requirement->set_id)
                    ->count();

                return $openingsCount;

            case 'OWN_CARDS':
                // Compter combien de cartes du set l'utilisateur possède
                $cardsCount = $user->collections()
                    ->whereHas('card', function ($query) use ($requirement) {
                        $query->where('set_id', $requirement->set_id);
                    })
                    ->sum('nbCard');

                return $cardsCount;

            default:
                return 0;
        }
    }

    /**
     * Annuler les échanges incompatibles pour un utilisateur et une carte
     */
    private function cancelIncompatibleTrades($userId, $cardId)
    {
        // Récupérer la quantité restante de la carte
        $collection = Collection::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        $availableQty = $collection ? $collection->nbCard : 0;

        // Récupérer tous les échanges "pending" où l'utilisateur offre cette carte
        $pendingTrades = Trade::where('status', 'pending')
            ->where('creator_id', $userId)
            ->where('offered_card_id', $cardId)
            ->get();

        // Compter combien de cartes sont nécessaires pour tous les échanges
        $requiredQty = $pendingTrades->count();

        // Si l'utilisateur n'a pas assez de cartes pour tous les échanges, annuler les plus récents
        if ($availableQty < $requiredQty) {
            $toCancel = $requiredQty - $availableQty;
            $tradesToCancel = $pendingTrades->sortByDesc('created_at')->take($toCancel);

            foreach ($tradesToCancel as $trade) {
                $trade->update(['status' => 'cancelled']);
            }
        }
    }
}
