<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RaritySeeder extends Seeder
{
    public function run()
    {
        $rarities = [
            ['name' => 'Commune', 'percentageSpawn' => 60, 'price' => 50],
            ['name' => 'Peu commune', 'percentageSpawn' => 25, 'price' => 100],
            ['name' => 'Rare', 'percentageSpawn' => 10, 'price' => 200],
            ['name' => 'Ultra Rare', 'percentageSpawn' => 3, 'price' => 500],
            ['name' => 'Double Rare', 'percentageSpawn' => 1, 'price' => 1000],
            ['name' => 'Illustration Rare', 'percentageSpawn' => 0, 'price' => 750],
            ['name' => 'Illustration spéciale rare', 'percentageSpawn' => 0, 'price' => 1500],
            ['name' => 'Hyper Rare', 'percentageSpawn' => 0, 'price' => 2000],
            ['name' => 'Promo', 'percentageSpawn' => 1, 'price' => 300],
        ];

        DB::table('rarity')->insert($rarities);
    }
}
