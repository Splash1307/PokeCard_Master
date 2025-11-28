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

        // Récupérer toutes les cartes de la collection de l'utilisateur
        // avec les informations complètes de chaque carte
        $collection = Collection::with([
            'card.rarity',
            'card.type',
            'card.set.serie',
            'card.set'
        ])
            ->where('user_id', $user->id)
        // Récupérer la collection de l'utilisateur
        $userCollection = Collection::where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('nbCard', 'card_id')
            ->toArray();

        // Récupérer toutes les cartes qui ne sont PAS dans la collection
        // pour permettre à l'utilisateur de choisir une carte à demander
        $userCardIds = $collection->pluck('card_id')->toArray();

        $availableCards = Card::with(['rarity', 'type', 'set', 'set.serie'])
            ->whereNotIn('id', $userCardIds)
        // Récupérer TOUTES les cartes avec les relations
        $allCards = Card::with(['rarity', 'type', 'set.series'])
            ->orderBy('name')
            ->get()
            ->map(function ($card) use ($userCollection) {
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'image' => $card->image,
                    'rarity' => $card->rarity,
                    'type' => $card->type,
                    'set' => $card->set,
                    'owned' => isset($userCollection[$card->id]),
                    'quantity' => $userCollection[$card->id] ?? 0,
                ];
            });

        // Récupérer les cartes disponibles pour les échanges (celles que l'utilisateur ne possède pas)
        $availableCards = $allCards->filter(fn($card) => !$card['owned']);

        return Inertia::render('Collection/Index', [
            'allCards' => $allCards,
            'availableCards' => $availableCards
        ]);
    }
}
