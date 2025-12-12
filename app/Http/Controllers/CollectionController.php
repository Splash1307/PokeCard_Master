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

        // Récupérer la collection de l'utilisateur
        $userCollection = Collection::with([
            'card.rarity',
            'card.primaryType',
            'card.set.serie',
            'card.set'
        ])
            ->where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('nbCard', 'card_id')
            ->toArray();

        // Récupérer toutes les cartes avec leurs relations, triées par série, set, et localId
        $allCards = Card::with(['rarity', 'primaryType', 'set.serie', 'set'])
            ->whereHas('set.serie') // S'assurer que la carte a une série
            ->orderBy('localId')
            ->get()
            ->map(function ($card) use ($userCollection) {
                return [
                    'id' => $card->id,
                    'localId' => $card->localId,
                    'name' => $card->name,
                    'image' => $card->image,
                    'hp' => $card->hp,
                    'attack' => $card->attack,
                    'defense' => $card->defense,
                    'rarity' => $card->rarity,
                    'primaryType' => $card->primaryType,
                    'set' => [
                        'id' => $card->set->id,
                        'name' => $card->set->name,
                        'abbreviation' => $card->set->abbreviation,
                        'serie' => [
                            'id' => $card->set->serie->id,
                            'name' => $card->set->serie->name,
                            'abbreviation' => $card->set->serie->abbreviation,
                        ],
                    ],
                    'owned' => isset($userCollection[$card->id]),
                    'quantity' => $userCollection[$card->id] ?? 0,
                ];
            });

        // Récupérer les cartes disponibles pour les échanges
        $availableCards = $allCards->filter(fn($card) => !$card['owned'])->values();

        return Inertia::render('Collection/Index', [
            'allCards' => $allCards,
            'availableCards' => $availableCards
        ]);
    }
}
