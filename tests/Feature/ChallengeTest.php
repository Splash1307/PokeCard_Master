<?php

use App\Models\User;
use App\Models\Card;
use App\Models\Set;
use App\Models\Challenge;
use App\Models\ChallengeRequirement;
use App\Models\RequirementCard;
use App\Models\UserChallenge;
use App\Models\UserRequirementProgress;
use App\Models\ChallengeDonation;
use App\Models\Collection;
use App\Models\BoosterOpening;
use App\Http\Controllers\ChallengeController;
use function Pest\Laravel\{actingAs, get, post, assertDatabaseHas};

beforeEach(function () {
    seedDatabase();

    $this->user = User::factory()->create([
        'coin' => 100,
    ]);

    $this->set = Set::factory()->create();

    // Créer un challenge actif
    $this->challenge = Challenge::factory()->create([
        'title' => 'Challenge Test',
        'description' => 'Un challenge de test',
        'status' => 'Actif',
        'reward' => 500,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
    ]);
});

// Note: Les tests GET de pages Inertia sont skip car ils nécessitent Vue.js compilé
// On teste uniquement la logique des challenges

it('crée automatiquement une participation utilisateur pour un challenge actif', function () {
    ChallengeController::assignActiveChallenges($this->user->id);

    assertDatabaseHas('user_challenges', [
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'status' => 'En cours',
    ]);
});

it('calcule la progression pour un requirement OPEN_PACKS', function () {
    // Créer un requirement de type OPEN_PACKS
    $requirement = ChallengeRequirement::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => 'OPEN_PACKS',
        'set_id' => $this->set->id,
        'target_count' => 5,
    ]);

    // Créer des ouvertures de booster
    BoosterOpening::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'set_id' => $this->set->id,
    ]);

    ChallengeController::checkAndUpdateProgress($this->user->id);

    // Vérifier la progression
    $progress = UserRequirementProgress::where('user_id', $this->user->id)
        ->where('requirement_id', $requirement->id)
        ->first();

    expect($progress->progress_count)->toBe(3);
});

it('calcule la progression pour un requirement OWN_CARDS', function () {
    // Créer un requirement de type OWN_CARDS
    $requirement = ChallengeRequirement::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => 'OWN_CARDS',
        'set_id' => $this->set->id,
        'target_count' => 10,
    ]);

    // Créer des cartes du set
    $cards = Card::factory()->count(7)->create([
        'set_id' => $this->set->id,
    ]);

    // Ajouter ces cartes à la collection
    foreach ($cards as $card) {
        Collection::factory()->create([
            'user_id' => $this->user->id,
            'card_id' => $card->id,
            'nbCard' => 1,
        ]);
    }

    ChallengeController::checkAndUpdateProgress($this->user->id);

    // Vérifier la progression
    $progress = UserRequirementProgress::where('user_id', $this->user->id)
        ->where('requirement_id', $requirement->id)
        ->first();

    expect($progress->progress_count)->toBe(7);
});

it('calcule la progression pour un requirement CARD_LIST', function () {
    // Créer un requirement de type CARD_LIST
    $requirement = ChallengeRequirement::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => 'CARD_LIST',
        'target_count' => 3,
    ]);

    // Créer des cartes requises
    $card1 = Card::factory()->create();
    $card2 = Card::factory()->create();

    RequirementCard::create([
        'requirement_id' => $requirement->id,
        'card_id' => $card1->id,
        'required_qty' => 2,
    ]);

    RequirementCard::create([
        'requirement_id' => $requirement->id,
        'card_id' => $card2->id,
        'required_qty' => 1,
    ]);

    // Donner les cartes
    ChallengeDonation::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'card_id' => $card1->id,
        'qty' => 2,
        'donated_at' => now(),
    ]);

    ChallengeDonation::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'card_id' => $card2->id,
        'qty' => 1,
        'donated_at' => now(),
    ]);

    // Vérifier que les RequirementCards existent
    $requirementCardsCount = RequirementCard::where('requirement_id', $requirement->id)->count();
    expect($requirementCardsCount)->toBe(2);

    // Vérifier que les donations existent
    $donationsTotal = ChallengeDonation::where('user_id', $this->user->id)
        ->where('challenge_id', $this->challenge->id)
        ->sum('qty');
    expect($donationsTotal)->toBe(3);

    ChallengeController::checkAndUpdateProgress($this->user->id);

    // Vérifier la progression
    $progress = UserRequirementProgress::where('user_id', $this->user->id)
        ->where('requirement_id', $requirement->id)
        ->first();

    expect($progress->progress_count)->toBe(3); // 2 + 1
});

it('permet de donner une carte à un challenge', function () {
    // Créer un requirement CARD_LIST
    $requirement = ChallengeRequirement::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => 'CARD_LIST',
        'target_count' => 1,
    ]);

    $card = Card::factory()->create();

    RequirementCard::create([
        'requirement_id' => $requirement->id,
        'card_id' => $card->id,
        'required_qty' => 2,
    ]);

    // Ajouter la carte à la collection
    Collection::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $card->id,
        'nbCard' => 2,
    ]);

    actingAs($this->user);

    post(route('challenges.donate', $this->challenge), [
        'card_id' => $card->id,
        'qty' => 1,
    ]);

    // Vérifier que la donation a été enregistrée
    assertDatabaseHas('challenge_donations', [
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'card_id' => $card->id,
        'qty' => 1,
    ]);

    // Vérifier que la carte a été retirée de la collection
    $collection = Collection::where('user_id', $this->user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($collection->nbCard)->toBe(1);
});

it('ne peut pas donner plus de cartes que requis', function () {
    $requirement = ChallengeRequirement::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => 'CARD_LIST',
        'target_count' => 1,
    ]);

    $card = Card::factory()->create();

    RequirementCard::create([
        'requirement_id' => $requirement->id,
        'card_id' => $card->id,
        'required_qty' => 1,
    ]);

    // Ajouter la carte à la collection
    Collection::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $card->id,
        'nbCard' => 5,
    ]);

    // Donner déjà 1 carte
    ChallengeDonation::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'card_id' => $card->id,
        'qty' => 1,
        'donated_at' => now(),
    ]);

    actingAs($this->user);

    // Essayer de donner une autre carte
    post(route('challenges.donate', $this->challenge), [
        'card_id' => $card->id,
    ]);

    // Vérifier que le total des donations n'a pas augmenté
    $totalDonations = ChallengeDonation::where('user_id', $this->user->id)
        ->where('challenge_id', $this->challenge->id)
        ->where('card_id', $card->id)
        ->sum('qty');

    expect($totalDonations)->toBe(1); // Toujours 1, pas 2
});

it('marque le challenge comme complété quand tous les requirements sont remplis', function () {
    // Créer un requirement simple
    $requirement = ChallengeRequirement::factory()->create([
        'challenge_id' => $this->challenge->id,
        'type' => 'OPEN_PACKS',
        'set_id' => $this->set->id,
        'target_count' => 2,
    ]);

    // Créer la participation
    UserChallenge::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'status' => 'En cours',
    ]);

    // Créer 2 ouvertures
    BoosterOpening::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'set_id' => $this->set->id,
    ]);

    ChallengeController::checkAndUpdateProgress($this->user->id);

    // Vérifier que le challenge est complété
    $userChallenge = UserChallenge::where('user_id', $this->user->id)
        ->where('challenge_id', $this->challenge->id)
        ->first();

    expect($userChallenge->status)->toBe('Complété');
    expect($userChallenge->completed_at)->not->toBeNull();
});

it('permet de réclamer la récompense d\'un challenge complété', function () {
    // Créer une participation complétée
    UserChallenge::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'status' => 'Complété',
        'completed_at' => now(),
    ]);

    $initialCoins = $this->user->coin;

    actingAs($this->user);

    post(route('challenges.claim', $this->challenge));

    // Vérifier que les coins ont été ajoutés
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins + $this->challenge->reward);

    // Vérifier que le status est mis à jour
    $userChallenge = UserChallenge::where('user_id', $this->user->id)
        ->where('challenge_id', $this->challenge->id)
        ->first();

    expect($userChallenge->status)->toBe('Récompense récupérée');
    expect($userChallenge->claimed_at)->not->toBeNull();
});

it('ne peut pas réclamer la récompense deux fois', function () {
    // Créer une participation avec récompense déjà récupérée
    UserChallenge::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'status' => 'Récompense récupérée',
        'completed_at' => now(),
        'claimed_at' => now(),
    ]);

    $initialCoins = $this->user->coin;

    actingAs($this->user);

    post(route('challenges.claim', $this->challenge));

    // Vérifier que les coins n'ont pas changé
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins);
});

it('ne peut pas réclamer la récompense d\'un challenge non complété', function () {
    UserChallenge::create([
        'user_id' => $this->user->id,
        'challenge_id' => $this->challenge->id,
        'status' => 'En cours',
    ]);

    $initialCoins = $this->user->coin;

    actingAs($this->user);

    post(route('challenges.claim', $this->challenge));

    // Vérifier que les coins n'ont pas changé
    $this->user->refresh();
    expect($this->user->coin)->toBe($initialCoins);
});

it('assigne automatiquement les challenges actifs aux nouveaux utilisateurs', function () {
    $newUser = User::factory()->create();

    ChallengeController::assignActiveChallenges($newUser->id);

    // Vérifier que le challenge actif a été assigné
    assertDatabaseHas('user_challenges', [
        'user_id' => $newUser->id,
        'challenge_id' => $this->challenge->id,
        'status' => 'En cours',
    ]);

    // Vérifier que les requirements progress ont été créés
    $requirements = $this->challenge->requirements;
    foreach ($requirements as $requirement) {
        assertDatabaseHas('user_requirement_progress', [
            'user_id' => $newUser->id,
            'requirement_id' => $requirement->id,
            'progress_count' => 0,
        ]);
    }
});
