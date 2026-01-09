<?php

use App\Models\User;
use App\Models\Card;
use App\Models\Trade;
use App\Models\Collection;
use function Pest\Laravel\{actingAs, get, post, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
    seedDatabase();

    // Créer deux utilisateurs
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();

    // Créer des cartes
    $this->card1 = Card::factory()->create();
    $this->card2 = Card::factory()->create();
    $this->card3 = Card::factory()->create();

    // Ajouter des cartes aux collections des utilisateurs
    Collection::factory()->create([
        'user_id' => $this->user1->id,
        'card_id' => $this->card1->id,
        'nbCard' => 2,
    ]);

    Collection::factory()->create([
        'user_id' => $this->user2->id,
        'card_id' => $this->card2->id,
        'nbCard' => 1,
    ]);
});

// Note: Les tests GET de pages Inertia sont skip car ils nécessitent Vue.js compilé
// On teste uniquement la logique des échanges

it('permet de créer une offre d\'échange', function () {
    actingAs($this->user1);

    post(route('trades.store'), [
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card2->id,
    ]);

    assertDatabaseHas('trades', [
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card2->id,
        'status' => 'pending',
    ]);
});

it('ne peut pas créer un échange sans posséder la carte offerte', function () {
    actingAs($this->user1);

    post(route('trades.store'), [
        'offered_card_id' => $this->card2->id, // Cette carte appartient à user2
        'requested_card_id' => $this->card1->id,
    ]);

    // Vérifier qu'aucun trade n'a été créé
    $trade = Trade::where('creator_id', $this->user1->id)
        ->where('offered_card_id', $this->card2->id)
        ->first();

    expect($trade)->toBeNull();
});

it('permet d\'accepter une offre d\'échange', function () {
    // Créer un trade
    $trade = Trade::factory()->create([
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card2->id,
        'status' => 'pending',
    ]);

    actingAs($this->user2);

    post(route('trades.accept', $trade));

    // Vérifier que le trade est complété
    $trade->refresh();
    expect($trade->status)->toBe('completed');
    expect($trade->responder_id)->toBe($this->user2->id);
    expect($trade->completed_at)->not->toBeNull();

    // Vérifier que les cartes ont été échangées
    // User1 doit avoir perdu card1 et gagné card2
    $user1Collection1 = Collection::where('user_id', $this->user1->id)
        ->where('card_id', $this->card1->id)
        ->first();
    expect($user1Collection1->nbCard)->toBe(1); // 2 - 1

    $user1Collection2 = Collection::where('user_id', $this->user1->id)
        ->where('card_id', $this->card2->id)
        ->first();
    expect($user1Collection2->nbCard)->toBe(1);

    // User2 doit avoir perdu card2 et gagné card1
    $user2Collection2 = Collection::where('user_id', $this->user2->id)
        ->where('card_id', $this->card2->id)
        ->first();
    expect($user2Collection2)->toBeNull(); // 1 - 1 = 0, donc supprimé

    $user2Collection1 = Collection::where('user_id', $this->user2->id)
        ->where('card_id', $this->card1->id)
        ->first();
    expect($user2Collection1->nbCard)->toBe(1);
});

it('ne peut pas accepter un échange sans posséder la carte demandée', function () {
    // Créer un trade où user2 n'a pas la carte demandée
    $trade = Trade::factory()->create([
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card3->id, // User2 ne possède pas card3
        'status' => 'pending',
    ]);

    actingAs($this->user2);

    post(route('trades.accept', $trade));

    // Vérifier que le trade est toujours pending (pas complété)
    $trade->refresh();
    expect($trade->status)->toBe('pending');
});

it('permet d\'annuler une offre d\'échange', function () {
    // Créer un trade
    $trade = Trade::factory()->create([
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card2->id,
        'status' => 'pending',
    ]);

    actingAs($this->user1);

    post(route('trades.cancel', $trade));

    // Vérifier que le trade est annulé
    $trade->refresh();
    expect($trade->status)->toBe('cancelled');
});


it('annule les échanges incompatibles après un échange', function () {
    // User1 a 2 exemplaires de card1
    // Créer deux trades offrant card1
    $trade1 = Trade::factory()->create([
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card2->id,
        'status' => 'pending',
    ]);

    $trade2 = Trade::factory()->create([
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card3->id,
        'status' => 'pending',
    ]);

    // User2 accepte trade1
    actingAs($this->user2);
    post(route('trades.accept', $trade1));

    // User1 n'a plus qu'un exemplaire de card1
    // trade2 devrait toujours être pending car il reste 1 exemplaire
    $trade2->refresh();
    expect($trade2->status)->toBe('pending');

    // Créer un troisième trade
    $trade3 = Trade::factory()->create([
        'creator_id' => $this->user1->id,
        'offered_card_id' => $this->card1->id,
        'requested_card_id' => $this->card2->id,
        'status' => 'pending',
    ]);

    // User3 avec card3
    $user3 = User::factory()->create();
    Collection::factory()->create([
        'user_id' => $user3->id,
        'card_id' => $this->card3->id,
        'nbCard' => 1,
    ]);

    // User3 accepte trade2, user1 n'a plus de card1
    actingAs($user3);
    post(route('trades.accept', $trade2));

    // trade3 devrait être annulé car user1 n'a plus de card1
    $trade3->refresh();
    expect($trade3->status)->toBe('cancelled');
});

it('un échange peut être créé avec une carte spécifique', function () {
    $specificCard = Card::factory()->create(['name' => 'Pikachu']);
    Collection::factory()->create([
        'user_id' => $this->user1->id,
        'card_id' => $specificCard->id,
        'nbCard' => 1,
    ]);

    actingAs($this->user1);

    post(route('trades.store'), [
        'offered_card_id' => $specificCard->id,
        'requested_card_id' => $this->card2->id,
    ]);

    assertDatabaseHas('trades', [
        'creator_id' => $this->user1->id,
        'offered_card_id' => $specificCard->id,
        'status' => 'pending',
    ]);
});
