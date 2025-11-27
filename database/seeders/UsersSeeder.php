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
            'lastConnexionAt' => Carbon::now(),
            'role_id' => 1,
        ]);

        for ($i = 1; $i <= 20; $i++) {
            DB::table('users')->insert([
                'pseudo' => $faker->firstName . ' ' . $faker->lastName,
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'coin' => $faker->numberBetween(100, 5000),
                'lastConnexionAt' => $faker->dateTimeBetween('-30 days', 'now'),
                'role_id' => 2,
            ]);
        }

        echo "✔️ 1 admin et 20 utilisateurs générés avec succès.\n";
    }
}
