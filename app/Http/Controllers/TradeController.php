<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TradeController extends Controller
{
    /**
     * Afficher toutes les offres d'échange disponibles
     */
    public function index()
    {
        $user = auth()->user();

        // Récupérer toutes les offres d'échange en attente
        // SAUF celles créées par l'utilisateur connecté
        $trades = Trade::with([
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
            ->where('creator_id', '!=', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Trades/Index', [
            'trades' => $trades
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
            ->get();

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

        // Vérifier que l'utilisateur possède la carte demandée
        $userCollection = Collection::where('user_id', $user->id)
            ->where('card_id', $trade->requested_card_id)
            ->first();

        if (!$userCollection || $userCollection->nbCard <= 0) {
            return back()->withErrors(['error' => 'Vous ne possédez pas cette carte.']);
        }

        // Vérifier que le créateur possède toujours la carte offerte
        $creatorCollection = Collection::where('user_id', $trade->creator_id)
            ->where('card_id', $trade->offered_card_id)
            ->first();

        if (!$creatorCollection || $creatorCollection->nbCard <= 0) {
            return back()->withErrors(['error' => 'Le créateur ne possède plus cette carte.']);
        }

        // Effectuer l'échange
        // 1. Retirer la carte offerte au créateur
        $creatorCollection->nbCard -= 1;
        $creatorCollection->save();

        // 2. Retirer la carte demandée au répondeur
        $userCollection->nbCard -= 1;
        $userCollection->save();

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

        return redirect()->route('trades.index')->with('success', 'Échange effectué avec succès !');
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
