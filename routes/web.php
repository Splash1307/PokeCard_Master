<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


Route::get('dashboard', function () {
    $user = auth()->user();

    // Statistiques du joueur
    $totalCards = $user->collections()->sum('nbCard');
    $uniqueCards = $user->collections()->count();
    $totalCoins = $user->coin;

    // Challenges actifs (max 3 pour l'aperçu)
    $activeChallenges = \App\Models\Challenge::with(['requirements'])
        ->where('status', 'Actif')
        ->take(3)
        ->get()
        ->map(function ($challenge) use ($user) {
            $userChallenge = \App\Models\UserChallenge::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->first();

            $requirements = $challenge->requirements->map(function ($req) use ($user) {
                $progress = \App\Models\UserRequirementProgress::where('user_id', $user->id)
                    ->where('requirement_id', $req->id)
                    ->first();

                return [
                    'target_count' => $req->target_count,
                    'progress_count' => $progress ? $progress->progress_count : 0,
                ];
            });

            $totalTarget = $requirements->sum('target_count');
            $totalProgress = $requirements->sum('progress_count');
            $percentage = $totalTarget > 0 ? min(100, round(($totalProgress / $totalTarget) * 100)) : 0;

            return [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'reward' => $challenge->reward,
                'status' => $userChallenge ? $userChallenge->status : 'Non commencé',
                'progress_percentage' => $percentage,
            ];
        });

    // 5 cartes aléatoires de la collection
    $recentCards = $user->collections()
        ->with('card.set')
        ->inRandomOrder()
        ->take(5)
        ->get()
        ->map(function ($collection) {
            return [
                'id' => $collection->card->id,
                'name' => $collection->card->name,
                'image' => $collection->card->image,
                'set_name' => $collection->card->set->name ?? 'Inconnu',
                'obtained_at' => 'Dans votre collection',
            ];
        });

    // Échanges récents (5 derniers)
    $recentTrades = \App\Models\Trade::with(['creator', 'responder', 'offeredCard', 'requestedCard'])
        ->where(function ($query) use ($user) {
            $query->where('creator_id', $user->id)
                  ->orWhere('responder_id', $user->id);
        })
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($trade) use ($user) {
            $isCreator = $trade->creator_id === $user->id;
            return [
                'id' => $trade->id,
                'partner' => $isCreator
                    ? ($trade->responder ? $trade->responder->pseudo : 'En attente')
                    : $trade->creator->pseudo,
                'offered_card' => $trade->offeredCard->name,
                'requested_card' => $trade->requestedCard->name,
                'status' => $trade->status,
                'is_creator' => $isCreator,
                'created_at' => $trade->created_at->diffForHumans(),
            ];
        });

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_cards' => $totalCards,
            'unique_cards' => $uniqueCards,
            'coins' => $totalCoins,
        ],
        'active_challenges' => $activeChallenges,
        'recent_cards' => $recentCards,
        'recent_trades' => $recentTrades,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Route de debug temporaire
Route::get('challenges-debug', function () {
    $user = auth()->user();
    $challenges = \App\Models\Challenge::where('status', 'Actif')->get();

    return response()->json([
        'user' => $user ? ['id' => $user->id, 'name' => $user->pseudo] : null,
        'challenges_count' => $challenges->count(),
        'challenges' => $challenges->map(fn($c) => ['id' => $c->id, 'title' => $c->title])
    ]);
})->middleware(['auth', 'verified'])->name('challenges.debug');

// Routes pour la collection et les échanges de cartes
Route::middleware(['auth', 'verified'])->group(function () {

    // Collection
    Route::get('/collection', [App\Http\Controllers\CollectionController::class, 'index'])->name('collection.index');


    // Trades
    Route::get('/trades', [App\Http\Controllers\TradeController::class, 'index'])->name('trades.index');
    Route::get('/trades/my', [App\Http\Controllers\TradeController::class, 'myTrades'])->name('trades.my');
    Route::post('/trades', [App\Http\Controllers\TradeController::class, 'store'])->name('trades.store');
    Route::post('/trades/{trade}/accept', [App\Http\Controllers\TradeController::class, 'accept'])->name('trades.accept');
    Route::post('/trades/{trade}/cancel', [App\Http\Controllers\TradeController::class, 'cancel'])->name('trades.cancel');

    // Shop
    Route::post('/shop/purchase/{card}', [App\Http\Controllers\ShopController::class, 'purchase'])->name('shop.purchase');

    // Boosters
    // Voir la page des boosters
    Route::get('/boosters', [App\Http\Controllers\BoosterController::class, 'index'])->name('boosters.index');
    // Ouvrir un booster
    Route::post('/boosters/{set}/open', [App\Http\Controllers\BoosterController::class, 'open'])->name('boosters.open');

    // Challenges
    // Voir tous les challenges actifs
    Route::get('/challenges', [App\Http\Controllers\ChallengeController::class, 'index'])->name('challenges.index');
    // Réclamer la récompense
    Route::post('/challenges/{challenge}/claim', [App\Http\Controllers\ChallengeController::class, 'claim'])->name('challenges.claim');
    // Donner une carte au challenge
    Route::post('/challenges/{challenge}/donate', [App\Http\Controllers\ChallengeController::class, 'donateCard'])->name('challenges.donate');

});

// Routes Admin
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Gestion des challenges
    Route::get('/challenges', [App\Http\Controllers\Admin\ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/challenges/create', [App\Http\Controllers\Admin\ChallengeController::class, 'create'])->name('challenges.create');
    Route::post('/challenges', [App\Http\Controllers\Admin\ChallengeController::class, 'store'])->name('challenges.store');
    Route::get('/challenges/{challenge}/edit', [App\Http\Controllers\Admin\ChallengeController::class, 'edit'])->name('challenges.edit');
    Route::put('/challenges/{challenge}', [App\Http\Controllers\Admin\ChallengeController::class, 'update'])->name('challenges.update');
    Route::post('/challenges/{challenge}/toggle-status', [App\Http\Controllers\Admin\ChallengeController::class, 'toggleStatus'])->name('challenges.toggle-status');
    Route::delete('/challenges/{challenge}', [App\Http\Controllers\Admin\ChallengeController::class, 'destroy'])->name('challenges.destroy');
});

require __DIR__.'/settings.php';
