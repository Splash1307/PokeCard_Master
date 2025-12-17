<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\UserRequirementProgress;
use App\Models\ChallengeRequirement;
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
            'requirements.requirementCards.card'
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
                $requirements = $challenge->requirements->map(function ($req) use ($user) {
                    // Récupérer ou créer la progression du requirement
                    $progress = UserRequirementProgress::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'requirement_id' => $req->id,
                        ],
                        [
                            'progress_count' => 0,
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
                        'cards' => $req->requirementCards->map(function ($reqCard) {
                            return [
                                'card_id' => $reqCard->card_id,
                                'card_name' => $reqCard->card->name,
                                'card_image' => $reqCard->card->image,
                                'required_qty' => $reqCard->required_qty,
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
                // Pour CARD_LIST, vérifier combien de cartes l'utilisateur possède
                $totalOwned = 0;
                foreach ($requirement->requirementCards as $reqCard) {
                    $collection = $user->collections()
                        ->where('card_id', $reqCard->card_id)
                        ->first();

                    if ($collection) {
                        $totalOwned += min($collection->nbCard, $reqCard->required_qty);
                    }
                }
                return $totalOwned;

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
}
