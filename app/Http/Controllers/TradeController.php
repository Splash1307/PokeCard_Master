<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Models\Trade;
use App\Models\Collection;
use App\Models\Type;
use App\Models\Rarity;
use App\Models\Set;
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

        // Récupérer tous les filtres
        $search = $request->input('search', '');
        $rarity = $request->input('rarity', '');
        $type = $request->input('type', '');
        $serie = $request->input('serie', '');
        $set = $request->input('set', '');

        // Récupérer les IDs des cartes possédées par l'utilisateur
        $userCardIds = Collection::where('user_id', $user->id)
            ->where('nbCard', '>', 0)
            ->pluck('card_id')
            ->toArray();

        // Récupérer toutes les offres d'échange en attente
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

        // Filtrer par recherche (nom de carte)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('offeredCard', function ($cardQuery) use ($search) {
                    $cardQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('localId', 'like', '%' . $search . '%');
                });
            });
        }

        // Filtrer par série
        if (!empty($serie)) {
            $serieIds = explode(',', $serie);
            $query->whereHas('offeredCard.set.serie', function ($q) use ($serieIds) {
                $q->whereIn('id', $serieIds);
            });
        }

        // Filtrer par set
        if (!empty($set)) {
            $setIds = explode(',', $set);
            $query->whereHas('offeredCard.set', function ($q) use ($setIds) {
                $q->whereIn('id', $setIds);
            });
        }

        // Filtrer par type
        if (!empty($type)) {
            $typeNames = explode(',', $type);
            $query->whereHas('offeredCard.primaryType', function ($q) use ($typeNames) {
                $q->whereIn('name', $typeNames);
            });
        }

        // Filtrer par rareté
        if (!empty($rarity)) {
            $rarityNames = explode(',', $rarity);
            $query->whereHas('offeredCard.rarity', function ($q) use ($rarityNames) {
                $q->whereIn('name', $rarityNames);
            });
        }

        // Paginer les résultats (21 par page)
        $trades = $query->latest()
            ->paginate(21)
            ->withQueryString()
            ->through(function ($trade) use ($user, $userCardIds) {
                $userHasCard = Collection::where('user_id', $user->id)
                    ->where('card_id', $trade->requested_card_id)
                    ->where('nbCard', '>', 0)
                    ->exists();

                $creatorHasCard = Collection::where('user_id', $trade->creator_id)
                    ->where('card_id', $trade->offered_card_id)
                    ->where('nbCard', '>', 0)
                    ->exists();

                $trade->can_accept = $userHasCard && $creatorHasCard;
                $trade->user_has_offered_card = in_array($trade->offered_card_id, $userCardIds);

                return $trade;
            });

        // Générer la config des filtres basée sur TOUTES les cartes disponibles dans les trades
        $allOfferedCardIds = Trade::where('status', 'pending')
            ->where('creator_id', '!=', $user->id)
            ->pluck('offered_card_id')
            ->unique()
            ->toArray();

        $filterConfig = [
            'series' => Series::whereHas('sets.cards', function ($q) use ($allOfferedCardIds) {
                $q->whereIn('cards.id', $allOfferedCardIds);
            })
                ->get()
                ->map(function ($s) use ($allOfferedCardIds) {
                    $count = DB::table('cards')
                        ->join('sets', 'cards.set_id', '=', 'sets.id')
                        ->where('sets.serie_id', $s->id)
                        ->whereIn('cards.id', $allOfferedCardIds)
                        ->count();
                    return ['label' => $s->name, 'value' => $s->id, 'count' => $count];
                })
                ->filter(fn($s) => $s['count'] > 0)
                ->values()
                ->toArray(),

            'sets' => Set::whereHas('cards', function ($q) use ($allOfferedCardIds) {
                $q->whereIn('id', $allOfferedCardIds);
            })
                ->withCount(['cards as count' => function ($q) use ($allOfferedCardIds) {
                    $q->whereIn('id', $allOfferedCardIds);
                }])
                ->get()
                ->map(fn($s) => ['label' => $s->name, 'value' => $s->id, 'count' => $s->count])
                ->filter(fn($s) => $s['count'] > 0)
                ->toArray(),

            'types' => Type::whereHas('primaryCards', function ($q) use ($allOfferedCardIds) {
                $q->whereIn('id', $allOfferedCardIds);
            })
                ->withCount(['primaryCards as count' => function ($q) use ($allOfferedCardIds) {
                    $q->whereIn('id', $allOfferedCardIds);
                }])
                ->get()
                ->map(fn($t) => ['label' => $t->name, 'value' => $t->name, 'count' => $t->count, 'logo' => $t->logo])
                ->filter(fn($t) => $t['count'] > 0)
                ->toArray(),

            'rarities' => Rarity::whereHas('cards', function ($q) use ($allOfferedCardIds) {
                $q->whereIn('id', $allOfferedCardIds);
            })
                ->withCount(['cards as count' => function ($q) use ($allOfferedCardIds) {
                    $q->whereIn('id', $allOfferedCardIds);
                }])
                ->get()
                ->map(fn($r) => ['label' => $r->name, 'value' => $r->name, 'count' => $r->count])
                ->filter(fn($r) => $r['count'] > 0)
                ->toArray(),
        ];

        return Inertia::render('Trades/Index', [
            'trades' => $trades,
            'filters' => [
                'search' => $search,
                'rarity' => $rarity,
                'type' => $type,
                'serie' => $serie,
                'set' => $set,
            ],
            'filterConfig' => $filterConfig,
        ]);
    }

    /**
     * Afficher les échanges de l'utilisateur connecté
     */
    public function myTrades()
    {
        $user = auth()->user();

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
                $creatorHasCard = Collection::where('user_id', $user->id)
                    ->where('card_id', $trade->offered_card_id)
                    ->where('nbCard', '>', 0)
                    ->exists();

                $trade->is_valid = $creatorHasCard;
                return $trade;
            });

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

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'offered_card_id' => 'required|exists:cards,id',
            'requested_card_id' => 'required|exists:cards,id|different:offered_card_id',
        ]);

        $hasCard = Collection::where('user_id', $user->id)
            ->where('card_id', $validated['offered_card_id'])
            ->where('nbCard', '>', 0)
            ->exists();

        if (!$hasCard) {
            return back()->withErrors([
                'offered_card_id' => 'Vous ne possédez pas cette carte dans votre collection.'
            ]);
        }

        Trade::create([
            'creator_id' => $user->id,
            'offered_card_id' => $validated['offered_card_id'],
            'requested_card_id' => $validated['requested_card_id'],
            'status' => 'pending',
        ]);

        return redirect()->route('trades.my')->with('success', 'Offre d\'échange créée avec succès !');
    }

    public function accept(Trade $trade)
    {
        $user = auth()->user();

        if (!$trade->isPending()) {
            return back()->withErrors(['error' => 'Cette offre n\'est plus disponible.']);
        }

        if ($trade->creator_id === $user->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas accepter votre propre offre.']);
        }

        try {
            DB::transaction(function () use ($trade, $user) {
                $userCollection = Collection::where('user_id', $user->id)
                    ->where('card_id', $trade->requested_card_id)
                    ->lockForUpdate()
                    ->first();

                if (!$userCollection || $userCollection->nbCard <= 0) {
                    throw new \Exception('Vous ne possédez pas cette carte.');
                }

                $creatorCollection = Collection::where('user_id', $trade->creator_id)
                    ->where('card_id', $trade->offered_card_id)
                    ->lockForUpdate()
                    ->first();

                if (!$creatorCollection || $creatorCollection->nbCard <= 0) {
                    throw new \Exception('Le créateur ne possède plus cette carte.');
                }

                $creatorCollection->nbCard -= 1;
                if ($creatorCollection->nbCard <= 0) {
                    $creatorCollection->delete();
                } else {
                    $creatorCollection->save();
                }

                $userCollection->nbCard -= 1;
                if ($userCollection->nbCard <= 0) {
                    $userCollection->delete();
                } else {
                    $userCollection->save();
                }

                $creatorNewCard = Collection::firstOrCreate(
                    [
                        'user_id' => $trade->creator_id,
                        'card_id' => $trade->requested_card_id
                    ],
                    ['nbCard' => 0]
                );
                $creatorNewCard->nbCard += 1;
                $creatorNewCard->save();

                $userNewCard = Collection::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'card_id' => $trade->offered_card_id
                    ],
                    ['nbCard' => 0]
                );
                $userNewCard->nbCard += 1;
                $userNewCard->save();

                $trade->update([
                    'responder_id' => $user->id,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                $this->cancelIncompatibleTrades($trade->creator_id, $trade->offered_card_id);
                $this->cancelIncompatibleTrades($user->id, $trade->requested_card_id);
            });

            return redirect()->route('trades.index')->with('success', 'Échange effectué avec succès !');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function cancelIncompatibleTrades($userId, $cardId)
    {
        $collection = Collection::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        $availableQty = $collection ? $collection->nbCard : 0;

        $pendingTrades = Trade::where('status', 'pending')
            ->where('creator_id', $userId)
            ->where('offered_card_id', $cardId)
            ->get();

        $requiredQty = $pendingTrades->count();

        if ($availableQty < $requiredQty) {
            $toCancel = $requiredQty - $availableQty;
            $tradesToCancel = $pendingTrades->sortByDesc('created_at')->take($toCancel);

            foreach ($tradesToCancel as $trade) {
                $trade->update(['status' => 'cancelled']);
            }
        }
    }

    public function cancel(Trade $trade)
    {
        $user = auth()->user();

        if ($trade->creator_id !== $user->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas annuler cette offre.']);
        }

        if (!$trade->isPending()) {
            return back()->withErrors(['error' => 'Cette offre ne peut plus être annulée.']);
        }

        $trade->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('trades.my')->with('success', 'Offre annulée avec succès.');
    }
}
