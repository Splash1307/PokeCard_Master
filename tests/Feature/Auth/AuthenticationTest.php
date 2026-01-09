<?php

use App\Models\User;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

it('permet à un utilisateur de se connecter avec succès', function () {
    // Création des rôles nécessaires
    seedRoles();

    // Création d'un utilisateur de test
    $user = User::factory()->create([
        'email' => 'player1@exemple.com',
        'password' => bcrypt('password'),
    ]);

    // Soumission du formulaire de connexion
    $response = post('/login', [
        'email' => 'player1@exemple.com',
        'password' => 'password',
    ]);

    // Vérification de la redirection vers le dashboard
    $response->assertRedirect('/dashboard');

    // Vérification que l'utilisateur est authentifié
    assertAuthenticated();
});

it('refuse la connexion avec des identifiants invalides', function () {
    // Création des rôles nécessaires
    seedRoles();

    // Création d'un utilisateur de test
    $user = User::factory()->create([
        'email' => 'player1@exemple.com',
        'password' => bcrypt('password'),
    ]);

    // Tentative de connexion avec un mauvais mot de passe
    $response = post('/login', [
        'email' => 'player1@exemple.com',
        'password' => 'wrong-password',
    ]);

    // Vérification que l'utilisateur n'est pas authentifié
    assertGuest();
});

it('refuse la connexion avec un email invalide', function () {
    seedRoles();

    // Tentative de connexion avec un email qui n'existe pas
    $response = post('/login', [
        'email' => 'nonexistent@exemple.com',
        'password' => 'password',
    ]);

    // Vérification que l'utilisateur n'est pas authentifié
    assertGuest();
});
