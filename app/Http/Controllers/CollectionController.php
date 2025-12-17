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
            'card.secondaryType',
            'card.set.serie',
            'card.set'
        ])
            ->where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('nbCard', 'card_id')
            ->toArray();

        // Récupérer toutes les cartes avec leurs relations
        $allCards = Card::with(['rarity', 'primaryType', 'secondaryType', 'set.serie', 'set'])
            ->whereHas('set.serie')
            ->orderBy('localId')
            ->get()
            ->map(function ($card) use ($userCollection) {
                return [
                    'id' => $card->id,
                    'localId' => $card->localId,
                    'name' => $card->name,
                    'image' => $card->image,
                    'rarity' => $card->rarity ? [
                        'id' => $card->rarity->id, // ✅ Ajouté l'id
                        'name' => $card->rarity->name,
                        'price' => $card->rarity->price,
                    ] : null,
                    'primaryType' => $card->primaryType ? [
                        'id' => $card->primaryType->id, // ✅ Ajouté l'id
                        'name' => $card->primaryType->name,
                        'logo' => $card->primaryType->logo,
                    ] : null,
                    'secondaryType' => $card->secondaryType ? [
                        'id' => $card->secondaryType->id, // ✅ Ajouté l'id
                        'name' => $card->secondaryType->name,
                        'logo' => $card->secondaryType->logo,
                    ] : null,
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

        // ✅ CHANGEMENT : Toutes les cartes au lieu de filtrer
        // Garde l'info 'owned' pour que le filtre dans CardSelector fonctionne
        $availableCards = $allCards->values();

        return Inertia::render('Collection/Index', [
            'allCards' => $allCards,
            'availableCards' => $availableCards
        ]);
    }
}
