<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('fr_FR');

        DB::table('users')->insert([
            'pseudo' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'coin' => 999999,
            'level_id' => 50,
            'lastConnexionAt' => Carbon::now()->subDay(),
            'role_id' => 1,
        ]);

        DB::table('users')->insert([
            'pseudo' => 'Player1',
            'email' => 'player1@example.com',
            'password' => Hash::make('password'),
            'coin' => 999999,
            'level_id' => 50,
            'lastConnexionAt' => Carbon::now()->subDay(),
            'role_id' => 2,
        ]);

        DB::table('users')->insert([
            'pseudo' => 'Player2',
            'email' => 'player2@example.com',
            'password' => Hash::make('password'),
            'coin' => 999999,
            'level_id' => 50,
            'lastConnexionAt' => Carbon::now()->subDay(),
            'role_id' => 2,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->insert([
                'pseudo' => $faker->firstName . ' ' . $faker->lastName,
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'coin' => $faker->numberBetween(100, 5000),
                'lastConnexionAt' => Carbon::now()->subDay(),
                'role_id' => 2,
            ]);
        }

        echo "✔️ 1 admin et 7 utilisateurs générés avec succès.\n";
    }
}
