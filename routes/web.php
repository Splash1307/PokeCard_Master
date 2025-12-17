<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
