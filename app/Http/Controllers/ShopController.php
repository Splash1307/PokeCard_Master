<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
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

        // Redirection avec message de succès
        return redirect()->route('collection.index')->with('success', 'Carte achetée avec succès !');
    }
}
