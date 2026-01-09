<?php

use App\Models\User;
use App\Models\Set;
use App\Models\Card;
use App\Models\Rarity;
use App\Models\BoosterOpening;
use function Pest\Laravel\{actingAs, get, post, assertDatabaseHas};

beforeEach(function () {
    seedDatabase();

    // Créer les raretés nécessaires pour les boosters
    $this->commonRarity = Rarity::factory()->create([
        'name' => 'Commune',
        'percentageSpawn' => 75,
        'price' => 10,
    ]);

    $this->rareRarity = Rarity::factory()->create([
        'name' => 'Rare',
        'percentageSpawn' => 25,
        'price' => 50,
    ]);

    // Créer un set avec des cartes
    $this->set = Set::factory()->create();

    // Créer 10 cartes communes
    $this->commonCards = Card::factory()->count(10)->create([
        'set_id' => $this->set->id,
        'rarity_id' => $this->commonRarity->id,
    ]);

    // Créer 5 cartes rares
    $this->rareCards = Card::factory()->count(5)->create([
        'set_id' => $this->set->id,
        'rarity_id' => $this->rareRarity->id,
    ]);

    // Créer un utilisateur avec des coins
    $this->user = User::factory()->create([
        'coin' => 100,
        'nbBooster' => 0,
        'xp' => 0,
    ]);
});

// Note: Les tests GET de pages Inertia sont skip car ils nécessitent Vue.js compilé
// On teste uniquement la logique métier (actions POST)

it('permet d\'ouvrir un booster avec des coins', function () {
    actingAs($this->user);

    $initialCoins = $this->user->coin;

    post(route('boosters.open', $this->set));

    // Vérifier que les coins ont été déduits
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins - 50);

    // Vérifier que l'XP a été ajouté (au minimum +5 pour l'ouverture)
    expect($this->user->xp)->toBeGreaterThanOrEqual(5);

    // Vérifier qu'une ouverture de booster a été enregistrée
    assertDatabaseHas('booster_openings', [
        'user_id' => $this->user->id,
        'set_id' => $this->set->id,
    ]);


});

it('permet d\'ouvrir un booster gratuit', function () {
    $this->user->update(['nbBooster' => 1, 'coin' => 100]);
    actingAs($this->user);

    $initialCoins = $this->user->coin;

    post(route('boosters.open', $this->set));

    // Vérifier que les coins n'ont pas été déduits
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins);

    // Vérifier que le booster gratuit a été utilisé
    expect($this->user->nbBooster)->toBe(0);
});

it('ne peut pas ouvrir un booster sans assez de coins', function () {
    $this->user->update(['coin' => 10, 'nbBooster' => 0]);
    actingAs($this->user);

    $initialCoins = $this->user->coin;

    post(route('boosters.open', $this->set));

    // Vérifier que les coins n'ont PAS changé
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins);

    // Vérifier qu'aucune carte n'a été ajoutée
    expect($this->user->collections()->count())->toBe(0);
});

it('ajoute les bonnes quantités de cartes en double à la collection', function () {
    actingAs($this->user);

    // Ouvrir deux boosters
    post(route('boosters.open', $this->set));
    post(route('boosters.open', $this->set));

    $this->user->refresh();

    // Vérifier que certaines cartes ont nbCard > 1
    $collections = $this->user->collections()->get();
    $hasMultiples = $collections->where('nbCard', '>', 1)->count() > 0;

    expect($hasMultiples)->toBeTrue();
});

it('enregistre correctement les ouvertures de booster', function () {
    actingAs($this->user);

    post(route('boosters.open', $this->set));
    post(route('boosters.open', $this->set));

    $openings = BoosterOpening::where('user_id', $this->user->id)
        ->where('set_id', $this->set->id)
        ->count();

    expect($openings)->toBe(2);
});
