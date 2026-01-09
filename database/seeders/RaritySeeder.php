<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RaritySeeder extends Seeder
{
    public function run()
    {
        $rarities = [
            ['name' => 'Commune', 'percentageSpawn' => 80, 'price' => 50],
            ['name' => 'Peu commune', 'percentageSpawn' => 50, 'price' => 100],
            ['name' => 'Rare', 'percentageSpawn' => 40, 'price' => 200],
            ['name' => 'Holo Rare', 'percentageSpawn' => 40, 'price' => 300],
            ['name' => 'HIGH-TECG rare', 'percentageSpawn' => 10, 'price' => 300],
            ['name' => 'Promo', 'percentageSpawn' => 10, 'price' => 300],
            ['name' => 'Double Rare', 'percentageSpawn' => 7, 'price' => 300],
            ['name' => 'Holo Rare V', 'percentageSpawn' => 7, 'price' => 300],
            ['name' => 'Holo Rare VMAX', 'percentageSpawn' => 6, 'price' => 350],
            ['name' => 'Magnifique rare', 'percentageSpawn' => 5, 'price' => 400],
            ['name' => 'Ultra Rare', 'percentageSpawn' => 4, 'price' => 500],
            ['name' => 'Illustration Rare', 'percentageSpawn' => 3, 'price' => 750],
            ['name' => 'Illustration spéciale rare', 'percentageSpawn' => 2, 'price' => 1500],
            ['name' => 'Hyper Rare', 'percentageSpawn' => 1, 'price' => 2000],
        ];

        DB::table('rarity')->insert($rarities);
    }
}
