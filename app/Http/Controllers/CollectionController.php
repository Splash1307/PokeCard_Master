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
            'card.set.series'
        ])
            ->where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->orderBy('card_id')
            ->get();

        // Récupérer toutes les cartes qui ne sont PAS dans la collection
        // pour permettre à l'utilisateur de choisir une carte à demander
        $userCardIds = $collection->pluck('card_id')->toArray();

        $availableCards = Card::with(['rarity', 'type', 'set'])
            ->whereNotIn('id', $userCardIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('Collection/Index', [
            'collection' => $collection,
            'availableCards' => $availableCards
        ]);
    }
}
