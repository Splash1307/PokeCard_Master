<?php

namespace App\Http\Controllers;

use App\Models\Set;
use App\Models\Series;
use App\Models\Card;
use App\Models\Collection;
use App\Models\Rarity;
use App\Models\BoosterOpening;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BoosterController extends Controller
{
    /**
     * Afficher la page des boosters
     */
    public function index()
    {
        $user = auth()->user();

        // Récupérer toutes les séries avec leurs sets
        $series = Series::with(['sets' => function ($query) {
            $query->orderBy('name');
        }])
            ->orderBy('name')
            ->get()
            ->map(function ($serie) {
                return [
                    'id' => $serie->id,
                    'name' => $serie->name,
                    'abbreviation' => $serie->abbreviation,
                    'logo' => $serie->logo,
                    'sets' => $serie->sets->map(function ($set) {
                        return [
                            'id' => $set->id,
                            'name' => $set->name,
                            'abbreviation' => $set->abbreviation,
                            'logo' => $set->logo,
                        ];
                    }),
                ];
            });

        return Inertia::render('Booster/Index', [
            'series' => $series,
        ]);
    }

    /**
     * Ouvrir un booster pour un set spécifique
     */
    public function open(Request $request, Set $set)
    {
        $user = auth()->user();
        $boosterPrice = 50; // Prix d'un booster
        $usedFreeBooster = false;

        // Vérifier si l'utilisateur a un booster gratuit
        if ($user->nbBooster > 0) {
            // Utiliser un booster gratuit
            $user->nbBooster -= 1;
            $usedFreeBooster = true;
        } else {
            // Vérifier que l'utilisateur a assez de coins
            if ($user->coin < $boosterPrice) {
                return back()->withErrors(['error' => 'Vous n\'avez pas assez de coins pour ouvrir un booster.']);
            }

            // Déduire les coins
            $user->coin -= $boosterPrice;
        }

        $user->save();

        // +5 XP pour l'ouverture du booster
        $user->addXp(5);

        // Générer les 10 cartes du booster
        $cards = $this->generateBoosterCards($set);

        $newCardsCount = 0;

        // Vérifier quelles cartes sont nouvelles et les ajouter à la collection
        foreach ($cards as &$card) {
            $collection = Collection::where('user_id', $user->id)
                ->where('card_id', $card['id'])
                ->first();

            // Si la carte n'existe pas dans la collection, c'est une nouvelle carte
            $card['isNew'] = !$collection || $collection->nbCard == 0;

            // +1 XP pour chaque nouvelle carte
            if ($card['isNew']) {
                $user->addXp(1);
                $newCardsCount++;
            }

            // Ajouter la carte à la collection
            if (!$collection) {
                $collection = Collection::create([
                    'user_id' => $user->id,
                    'card_id' => $card['id'],
                    'nbCard' => 1,
                ]);
            } else {
                $collection->nbCard += 1;
                $collection->save();
            }
        }

        // Enregistrer l'ouverture du booster pour les challenges
        BoosterOpening::create([
            'user_id' => $user->id,
            'set_id' => $set->id,
            'opened_at' => now(),
        ]);

        // Vérifier et mettre à jour la progression des challenges
        ChallengeController::checkAndUpdateProgress($user->id);

        return Inertia::render('Booster/Opening', [
            'cards' => $cards,
            'set' => [
                'id' => $set->id,
                'name' => $set->name,
                'abbreviation' => $set->abbreviation,
                'logo' => $set->logo ?? null,
            ],
            'xpGained' => 5 + $newCardsCount,
            'newCardsCount' => $newCardsCount,
            'usedFreeBooster' => $usedFreeBooster,
            'user' => [
                'level' => $user->level->level,
                'xp' => $user->xp,
                'coin' => $user->coin,
                'nbBooster' => $user->nbBooster,
            ],
        ]);
    }


    /**
     * Générer 10 cartes aléatoires pour un booster
     */
    private function generateBoosterCards(Set $set)
    {
        $cards = [];

        // Récupérer toutes les raretés
        $raritiesInSet = Rarity::whereIn(
            'id',
            Card::where('set_id', $set->id)
                ->pluck('rarity_id')
                ->unique()
        )->get();

        // Séparer les raretés communes (Commune, Peu commune, Rare) des raretés supérieures
        // On considère que les raretés avec un percentageSpawn élevé sont les communes
        // Vous pouvez aussi filtrer par nom si vous préférez
        $commonRarities = $raritiesInSet->filter(function ($rarity) {
            // Les raretés communes sont celles avec un percentageSpawn >= 50
            // Ajustez cette valeur selon votre configuration
            return $rarity->percentageSpawn >= 50;
        });

        $rareRarities = $raritiesInSet->filter(function ($rarity) {
            // Les raretés supérieures sont celles avec un percentageSpawn < 50
            return $rarity->percentageSpawn < 50;
        });

        // Générer 9 cartes normales (seulement raretés communes)
        for ($i = 0; $i < 9; $i++) {
            $card = $this->drawCard($set, $commonRarities);
            if ($card) {
                $cards[] = $card;
            }
        }

        $card = $this->drawCard($set, $rareRarities->isNotEmpty() ? $rareRarities : $raritiesInSet);

        if ($card) {
            $cards[] = $card;
        }

        return $cards;
    }

    /**
     * Tirer une carte aléatoire en fonction des probabilités
     */
    private function drawCard(Set $set, $rarities)
    {
        if ($rarities->isEmpty()) {
            return null;
        }

        // Calculer les probabilités parmi les raretés disponibles
        $totalWeight = $rarities->sum('percentageSpawn');

        if ($totalWeight == 0) {
            // Si aucun poids, prendre une rareté aléatoire
            $selectedRarity = $rarities->random();
        } else {
            $random = mt_rand(1, $totalWeight);
            $currentWeight = 0;
            $selectedRarity = null;

            foreach ($rarities as $rarity) {
                $currentWeight += $rarity->percentageSpawn;
                if ($random <= $currentWeight) {
                    $selectedRarity = $rarity;
                    break;
                }
            }
        }

        if (!$selectedRarity) {
            return null;
        }

        // Récupérer une carte aléatoire du set avec cette rareté
        $card = Card::where('set_id', $set->id)
            ->where('rarity_id', $selectedRarity->id)
            ->with(['rarity', 'primaryType', 'secondaryType', 'set.serie'])
            ->inRandomOrder()
            ->first();

        if (!$card) {
            // Si aucune carte de cette rareté n'existe dans ce set,
            // essayer une autre rareté du même groupe
            $card = Card::where('set_id', $set->id)
                ->whereIn('rarity_id', $rarities->pluck('id')->toArray())
                ->with(['rarity', 'primaryType', 'secondaryType', 'set.serie'])
                ->inRandomOrder()
                ->first();
        }

        if (!$card) {
            // En dernier recours, prendre n'importe quelle carte du set
            $card = Card::where('set_id', $set->id)
                ->with(['rarity', 'primaryType', 'secondaryType', 'set.serie'])
                ->inRandomOrder()
                ->first();
        }

        if (!$card) {
            return null;
        }

        return [
            'id' => $card->id,
            'name' => $card->name,
            'localId' => $card->localId,
            'image' => $card->image,
            'rarity' => $card->rarity ? [
                'id' => $card->rarity->id,
                'name' => $card->rarity->name,
            ] : null,
//            'primaryType' => $card->primaryType ? [
//                'id' => $card->primaryType->id,
//                'name' => $card->primaryType->name,
//            ] : null,
//            'secondaryType' => $card->secondaryType ? [
//                'id' => $card->secondaryType->id,
//                'name' => $card->secondaryType->name,
//            ] : null,
            'set' => [
                'id' => $card->set->id,
                'name' => $card->set->name,
                'abbreviation' => $card->set->abbreviation,
            ],
        ];
    }
}
