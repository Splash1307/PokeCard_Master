<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Card;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollectionController extends Controller
{
    /**
     * Afficher la collection de l'utilisateur connecté
     */
    public function index()
    {
        $user = auth()->user();

        // Récupérer la collection de l'utilisateur avec les informations complètes de chaque carte
        $userCollection = Collection::with([
            'card.rarity',
            'card.type',
            'card.set.serie',
            'card.set'
        ])
            ->where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('nbCard', 'card_id')
            ->toArray();

        // Récupérer toutes les cartes avec leurs relations
        $allCards = Card::with(['rarity', 'type', 'set.serie', 'set'])
            ->orderBy('name')
            ->get()
            ->map(function ($card) use ($userCollection) {
                return [
                    'id' => $card->id,
                    'localId' => $card->localId,
                    'name' => $card->name,
                    'image' => $card->image,
                    'rarity' => $card->rarity,
                    'type' => $card->type,
                    'set' => $card->set,
                    'serie' => $card->set->serie,
                    'owned' => isset($userCollection[$card->id]),
                    'quantity' => $userCollection[$card->id] ?? 0,
                ];
            });

        // Récupérer les cartes disponibles pour les échanges (celles que l'utilisateur ne possède pas)
        $availableCards = $allCards->filter(fn($card) => !$card['owned'])->values(); // ->values() réindexe la collection

        return Inertia::render('Collection/Index', [
            'allCards' => $allCards,
            'availableCards' => $availableCards
        ]);
    }

}
