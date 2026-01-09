<?php

use App\Models\User;
use App\Models\Card;
use App\Models\Rarity;
use App\Models\Collection;
use function Pest\Laravel\{actingAs, post, assertDatabaseHas};

beforeEach(function () {
    seedDatabase();

    // Créer une rareté avec un prix
    $this->rarity = Rarity::factory()->create([
        'name' => 'Rare',
        'price' => 100,
    ]);

    // Créer une carte avec cette rareté
    $this->card = Card::factory()->create([
        'rarity_id' => $this->rarity->id,
    ]);

    // Créer un utilisateur avec des coins
    $this->user = User::factory()->create([
        'coin' => 500,
    ]);
});

it('permet d\'acheter une carte avec des coins', function () {
    actingAs($this->user);

    $initialCoins = $this->user->coin;

    $response = post(route('shop.purchase', $this->card));

    $response->assertRedirect();

    // Vérifier que les coins ont été déduits
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins - $this->rarity->price);

    // Vérifier que la carte a été ajoutée à la collection
    assertDatabaseHas('collections', [
        'user_id' => $this->user->id,
        'card_id' => $this->card->id,
        'nbCard' => 1,
    ]);
});

it('ne peut pas acheter une carte sans assez de coins', function () {
    $this->user->update(['coin' => 50]); // Moins que le prix de la carte
    actingAs($this->user);

    $response = post(route('shop.purchase', $this->card));

    $response->assertRedirect();
    $response->assertSessionHasErrors();

    // Vérifier que les coins n'ont pas été déduits
    $this->user->refresh();
    expect($this->user->coin)->toBe(50);

    // Vérifier que la carte n'a pas été ajoutée
    $collection = Collection::where('user_id', $this->user->id)
        ->where('card_id', $this->card->id)
        ->first();

    expect($collection)->toBeNull();
});

it('augmente la quantité si la carte est déjà possédée', function () {
    // Ajouter la carte à la collection
    Collection::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $this->card->id,
        'nbCard' => 1,
    ]);

    actingAs($this->user);

    $response = post(route('shop.purchase', $this->card));

    $response->assertRedirect();

    // Vérifier que la quantité a augmenté
    $collection = Collection::where('user_id', $this->user->id)
        ->where('card_id', $this->card->id)
        ->first();

    expect($collection->nbCard)->toBe(2);
});

it('déduit le bon montant selon la rareté de la carte', function () {
    // Créer une carte avec une rareté différente
    $cheapRarity = Rarity::factory()->create([
        'name' => 'Commune',
        'price' => 10,
    ]);

    $cheapCard = Card::factory()->create([
        'rarity_id' => $cheapRarity->id,
    ]);

    actingAs($this->user);

    $initialCoins = $this->user->coin;

    $response = post(route('shop.purchase', $cheapCard));

    $response->assertRedirect();

    // Vérifier que seulement 10 coins ont été déduits
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins - 10);
});

it('peut acheter plusieurs cartes différentes', function () {
    $card2 = Card::factory()->create([
        'rarity_id' => $this->rarity->id,
    ]);

    actingAs($this->user);

    $initialCoins = $this->user->coin;

    post(route('shop.purchase', $this->card));
    post(route('shop.purchase', $card2));

    // Vérifier que les coins ont été déduits deux fois
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins - ($this->rarity->price * 2));

    // Vérifier que les deux cartes sont dans la collection
    expect($this->user->collections()->count())->toBe(2);
});

it('affiche un message de succès après l\'achat', function () {
    actingAs($this->user);

    $response = post(route('shop.purchase', $this->card));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});
