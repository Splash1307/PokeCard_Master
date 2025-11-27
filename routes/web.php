<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes pour la collection et les échanges de cartes
Route::middleware(['auth', 'verified'])->group(function () {

    // Collection
    // Voir ma collection de cartes
    Route::get('/collection', [App\Http\Controllers\CollectionController::class, 'index'])->name('collection.index');


    // Trades

    // Voir toutes les offres d'échange disponibles
    Route::get('/trades', [App\Http\Controllers\TradeController::class, 'index'])->name('trades.index');
    // Voir mes échanges (créés et acceptés)
    Route::get('/trades/my', [App\Http\Controllers\TradeController::class, 'myTrades'])->name('trades.my');
    // Créer une nouvelle offre d'échange
    Route::post('/trades', [App\Http\Controllers\TradeController::class, 'store'])->name('trades.store');
    // Accepter une offre d'échange
    Route::post('/trades/{trade}/accept', [App\Http\Controllers\TradeController::class, 'accept'])->name('trades.accept');
    // Annuler une offre d'échange
    Route::post('/trades/{trade}/cancel', [App\Http\Controllers\TradeController::class, 'cancel'])->name('trades.cancel');
});

require __DIR__.'/settings.php';
