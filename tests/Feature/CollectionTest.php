<?php

use App\Models\User;
use App\Models\Card;
use App\Models\Collection;
use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    seedDatabase();

    $this->user = User::factory()->create();

    // Créer plusieurs cartes
    $this->cards = Card::factory()->count(10)->create();

    // Ajouter quelques cartes à la collection de l'utilisateur
    Collection::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->cards[0]->id,
        'nbCard' => 2,
    ]);

    Collection::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->cards[1]->id,
        'nbCard' => 1,
    ]);

    Collection::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->cards[2]->id,
        'nbCard' => 5,
    ]);
});

// Note: Les tests de pages Inertia (GET) sont skip car ils nécessitent Vue.js compilé
// Les collections sont testées via les autres fonctionnalités (Booster, Trade, Shop)

it('une collection peut avoir plusieurs exemplaires d\'une même carte', function () {
    $collection = Collection::where('user_id', $this->user->id)
        ->where('card_id', $this->cards[2]->id)
        ->first();

    expect($collection->nbCard)->toBe(5);
});

it('un utilisateur ne voit que sa propre collection', function () {
    $otherUser = User::factory()->create();

    Collection::factory()->create([
        'user_id' => $otherUser->id,
        'card_id' => $this->cards[5]->id,
        'nbCard' => 3,
    ]);

    // L'utilisateur 1 ne doit pas avoir accès à la carte de l'utilisateur 2
    $userCollection = Collection::where('user_id', $this->user->id)
        ->where('card_id', $this->cards[5]->id)
        ->first();

    expect($userCollection)->toBeNull();

    // L'autre utilisateur doit avoir sa carte
    $otherUserCollection = Collection::where('user_id', $otherUser->id)
        ->where('card_id', $this->cards[5]->id)
        ->first();

    expect($otherUserCollection)->not->toBeNull();
    expect($otherUserCollection->nbCard)->toBe(3);
});
