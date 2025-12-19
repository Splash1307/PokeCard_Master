<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TradeController extends Controller
{
    /**
     * Afficher toutes les offres d'échange disponibles
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search', '');

        // Récupérer les IDs des cartes possédées par l'utilisateur
        $userCardIds = Collection::where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('card_id')
            ->toArray();

        $query = Trade::with([
            'creator',
            'offeredCard.rarity',
            'offeredCard.primaryType',
            'requestedCard.rarity',
            'requestedCard.primaryType',
            'requestedCard.set',
            'offeredCard.set',
            'requestedCard.set.serie',
            'offeredCard.set.serie',
        ])
            ->where('status', 'pending')
            ->where('creator_id', '!=', $user->id);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('offeredCard', function ($cardQuery) use ($search) {
                    $cardQuery->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('requestedCard', function ($cardQuery) use ($search) {
                    $cardQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $trades = $query->latest()
            ->get()
            ->map(function ($trade) use ($user, $userCardIds) {
                $userHasCard = Collection::where('user_id', $user->id)
                    ->where('card_id', $trade->requested_card_id)
                    ->where('nbCard', '>', 0)
                    ->exists();

                $creatorHasCard = Collection::where('user_id', $trade->creator_id)
                    ->where('card_id', $trade->offered_card_id)
                    ->where('nbCard', '>', 0)
                    ->exists();

                $trade->can_accept = $userHasCard && $creatorHasCard;

                // Ajouter si l'utilisateur possède la carte offerte
                $trade->user_has_offered_card = in_array($trade->offered_card_id, $userCardIds);

                return $trade;
            });

        return Inertia::render('Trades/Index', [
            'trades' => $trades,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }


    /**
     * Afficher les échanges de l'utilisateur connecté
     */
    public function myTrades()
    {
        $user = auth()->user();

        // Récupérer les offres créées par l'utilisateur
        $createdTrades = Trade::with([
            'creator',
            'offeredCard.rarity',
            'offeredCard.primaryType',
            'requestedCard.rarity',
            'requestedCard.primaryType',
            'requestedCard.set',
            'offeredCard.set',
            'requestedCard.set.serie',
            'offeredCard.set.serie',
        ])
            ->where('creator_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($trade) use ($user) {
                // Vérifier si le créateur possède toujours la carte offerte
                $creatorHasCard = Collection::where('user_id', $user->id)
                    ->where('card_id', $trade->offered_card_id)
                    ->where('nbCard', '>', 0)
                    ->exists();

                $trade->is_valid = $creatorHasCard;
                return $trade;
            });

        // Récupérer les offres acceptées par l'utilisateur
        $respondedTrades = Trade::with([
            'creator',
            'offeredCard.rarity',
            'offeredCard.primaryType',
            'requestedCard.rarity',
            'requestedCard.primaryType'
        ])
            ->where('responder_id', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Trades/MyTrades', [
            'createdTrades' => $createdTrades,
            'respondedTrades' => $respondedTrades
        ]);
    }

    /**
     * Créer une nouvelle offre d'échange
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Vérifier que les données sont correctes
        $validated = $request->validate([
            'offered_card_id' => 'required|exists:cards,id',
            'requested_card_id' => 'required|exists:cards,id|different:offered_card_id',
        ]);

        // Vérifier que l'utilisateur possède la carte qu'il veut offrir
        $hasCard = Collection::where('user_id', $user->id)
            ->where('card_id', $validated['offered_card_id'])
            ->where('nbCard', '>', 0)
            ->exists();

        if (!$hasCard) {
            return back()->withErrors([
                'offered_card_id' => 'Vous ne possédez pas cette carte dans votre collection.'
            ]);
        }

        // Créer l'offre d'échange
        Trade::create([
            'creator_id' => $user->id,
            'offered_card_id' => $validated['offered_card_id'],
            'requested_card_id' => $validated['requested_card_id'],
            'status' => 'pending',
        ]);

        return redirect()->route('trades.my')->with('success', 'Offre d\'échange créée avec succès !');
    }

    /**
     * Accepter une offre d'échange
     */
    public function accept(Trade $trade)
    {
        $user = auth()->user();

        // Vérifier que l'offre est toujours en attente
        if (!$trade->isPending()) {
            return back()->withErrors(['error' => 'Cette offre n\'est plus disponible.']);
        }

        // Vérifier que l'utilisateur n'essaie pas d'accepter sa propre offre
        if ($trade->creator_id === $user->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas accepter votre propre offre.']);
        }

        // Utiliser une transaction pour garantir l'intégrité des données
        try {
            DB::transaction(function () use ($trade, $user) {
                // Vérifier que l'utilisateur possède la carte demandée (avec verrou)
                $userCollection = Collection::where('user_id', $user->id)
                    ->where('card_id', $trade->requested_card_id)
                    ->lockForUpdate()
                    ->first();

                if (!$userCollection || $userCollection->nbCard <= 0) {
                    throw new \Exception('Vous ne possédez pas cette carte.');
                }

                // Vérifier que le créateur possède toujours la carte offerte (avec verrou)
                $creatorCollection = Collection::where('user_id', $trade->creator_id)
                    ->where('card_id', $trade->offered_card_id)
                    ->lockForUpdate()
                    ->first();

                if (!$creatorCollection || $creatorCollection->nbCard <= 0) {
                    throw new \Exception('Le créateur ne possède plus cette carte.');
                }

                // Effectuer l'échange
                // 1. Retirer la carte offerte au créateur
                $creatorCollection->nbCard -= 1;
                if ($creatorCollection->nbCard <= 0) {
                    $creatorCollection->delete();
                } else {
                    $creatorCollection->save();
                }

                // 2. Retirer la carte demandée au répondeur
                $userCollection->nbCard -= 1;
                if ($userCollection->nbCard <= 0) {
                    $userCollection->delete();
                } else {
                    $userCollection->save();
                }

                // 3. Ajouter la carte demandée au créateur
                $creatorNewCard = Collection::firstOrCreate(
                    [
                        'user_id' => $trade->creator_id,
                        'card_id' => $trade->requested_card_id
                    ],
                    ['nbCard' => 0]
                );
                $creatorNewCard->nbCard += 1;
                $creatorNewCard->save();

                // 4. Ajouter la carte offerte au répondeur
                $userNewCard = Collection::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'card_id' => $trade->offered_card_id
                    ],
                    ['nbCard' => 0]
                );
                $userNewCard->nbCard += 1;
                $userNewCard->save();

                // 5. Marquer l'échange comme complété
                $trade->update([
                    'responder_id' => $user->id,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // 6. Annuler tous les autres échanges "pending" qui utilisent les cartes échangées
                $this->cancelIncompatibleTrades($trade->creator_id, $trade->offered_card_id);
                $this->cancelIncompatibleTrades($user->id, $trade->requested_card_id);
            });

            return redirect()->route('trades.index')->with('success', 'Échange effectué avec succès !');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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

    /**
     * Annuler une offre d'échange
     */
    public function cancel(Trade $trade)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur est bien le créateur de l'offre
        if ($trade->creator_id !== $user->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas annuler cette offre.']);
        }

        // Vérifier que l'offre est toujours en attente
        if (!$trade->isPending()) {
            return back()->withErrors(['error' => 'Cette offre ne peut plus être annulée.']);
        }

        // Annuler l'offre
        $trade->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('trades.my')->with('success', 'Offre annulée avec succès.');
    }
}
