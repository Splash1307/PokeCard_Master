<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\UserRequirementProgress;
use App\Models\Trade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord de l'utilisateur
     */
    public function index()
    {
        $user = auth()->user();

        // Statistiques du joueur
        $totalCards = $user->collections()->sum('nbCard');
        $uniqueCards = $user->collections()->count();
        $totalCoins = $user->coin;

        // Challenges actifs (max 3 pour l'aperçu)
        $activeChallenges = Challenge::with(['requirements'])
            ->where('status', 'Actif')
            ->take(3)
            ->get()
            ->map(function ($challenge) use ($user) {
                $userChallenge = UserChallenge::where('user_id', $user->id)
                    ->where('challenge_id', $challenge->id)
                    ->first();

                $requirements = $challenge->requirements->map(function ($req) use ($user) {
                    $progress = UserRequirementProgress::where('user_id', $user->id)
                        ->where('requirement_id', $req->id)
                        ->first();

                    return [
                        'target_count' => $req->target_count,
                        'progress_count' => $progress ? $progress->progress_count : 0,
                    ];
                });

                $totalTarget = $requirements->sum('target_count');
                $totalProgress = $requirements->sum('progress_count');
                $percentage = $totalTarget > 0 ? min(100, round(($totalProgress / $totalTarget) * 100)) : 0;

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'reward' => $challenge->reward,
                    'status' => $userChallenge ? $userChallenge->status : 'Non commencé',
                    'progress_percentage' => $percentage,
                ];
            });

        // 5 cartes les plus rares de la collection
        $recentCards = $user->collections()
            ->with(['card.set', 'card.rarity'])
            ->join('cards', 'collections.card_id', '=', 'cards.id')
            ->orderBy('cards.rarity_id', 'desc')
            ->select('collections.*')
            ->take(5)
            ->get()
            ->map(function ($collection) {
                return [
                    'id' => $collection->card->id,
                    'name' => $collection->card->name,
                    'image' => $collection->card->image,
                    'set_name' => $collection->card->set->name ?? 'Inconnu',
                    'rarity_name' => $collection->card->rarity->name ?? 'Commune',
                    'obtained_at' => 'Dans votre collection',
                ];
            });

        // Échanges récents (5 derniers)
        $recentTrades = Trade::with(['creator', 'responder', 'offeredCard', 'requestedCard'])
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhere('responder_id', $user->id);
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($trade) use ($user) {
                $isCreator = $trade->creator_id === $user->id;
                return [
                    'id' => $trade->id,
                    'partner' => $isCreator
                        ? ($trade->responder ? $trade->responder->pseudo : 'En attente')
                        : $trade->creator->pseudo,
                    'offered_card' => $trade->offeredCard->name,
                    'requested_card' => $trade->requestedCard->name,
                    'status' => $trade->status,
                    'is_creator' => $isCreator,
                    'created_at' => $trade->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_cards' => $totalCards,
                'unique_cards' => $uniqueCards,
                'coins' => $totalCoins,
            ],
            'active_challenges' => $activeChallenges,
            'recent_cards' => $recentCards,
            'recent_trades' => $recentTrades,
        ]);
    }

    public function claimDailyBooster()
    {
        $user = auth()->user();

        if (!$user->lastConnexionAt || !$user->lastConnexionAt->isToday()) {
            $user->increment('nbBooster');
            $user->lastConnexionAt = now();
            $user->save();

            return redirect()->back();
        }

        return redirect()->back();
    }


}
