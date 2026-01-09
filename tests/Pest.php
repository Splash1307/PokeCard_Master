

<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Crée les données nécessaires pour les tests (rôles et niveaux)
 */
function seedDatabase()
{
    // Seed des rôles
    \Illuminate\Support\Facades\DB::table('roles')->insert([
        ['id' => 1, 'name' => 'Admin'],
        ['id' => 2, 'name' => 'Player'],
    ]);

    // Seed des niveaux (au moins les premiers niveaux)
    for ($i = 1; $i <= 10; $i++) {
        \Illuminate\Support\Facades\DB::table('levels')->insert([
            ['id' => $i, 'level' => $i, 'nbCoins' => 25 * $i, 'nbBooster' => $i > 1 ? 1 : 0],
        ]);
    }
}

/**
 * Alias pour la compatibilité
 */
function seedRoles()
{
    seedDatabase();
}
