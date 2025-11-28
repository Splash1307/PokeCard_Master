<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    /**
     * Afficher la boutique
     */
    public function index()
    {
        $user = auth()->user();

        // Récupérer la collection de l'utilisateur
        $userCollection = Collection::where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('nbCard', 'card_id')
            ->toArray();

        // Récupérer toutes les cartes avec leurs prix (basés sur la rareté)
        $allCards = Card::with(['rarity', 'type', 'set.serie'])
            ->orderBy('name')
            ->get()
            ->map(function ($card) use ($userCollection) {
                return [
                    'id' => $card->id,
                    'localId' => $card->localId,
                    'name' => $card->name,
                    'image' => $card->image,
                    'rarity' => $card->rarity ? [
                        'id' => $card->rarity->id,
                        'name' => $card->rarity->name,
                        'price' => $card->rarity->price,
                    ] : null,
                    'type' => $card->type,
                    'set' => $card->set,
                    'owned' => isset($userCollection[$card->id]),
                    'quantity' => $userCollection[$card->id] ?? 0,
                ];
            });

        return Inertia::render('Shop/Index', [
            'allCards' => $allCards,
        ]);
    }

    /**
     * Acheter une carte
     */
    public function purchase(Card $card)
    {
        $user = auth()->user();

        $price = $card->rarity->price;

        // Vérifier que l'utilisateur a assez d'argent
        if ($user->coin < $price) {
            return back()->withErrors(['error' => 'Vous n\'avez pas assez de coins.']);
        }

        // Déduire les coins
        $user->coin -= $price;
        $user->save();

        // Ajouter la carte à la collection
        $collection = Collection::firstOrCreate(
            [
                'user_id' => $user->id,
                'card_id' => $card->id,
            ],
            ['nbCard' => 0]
        );

        $collection->nbCard += 1;
        $collection->save();

        return redirect()->route('shop.index')->with('success', 'Carte achetée avec succès !');
    }
}
