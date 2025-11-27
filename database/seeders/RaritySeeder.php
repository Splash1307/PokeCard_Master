<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RaritySeeder extends Seeder
{
    public function run()
    {
        $rarities = [
            ['name' => 'Commune', 'percentageSpawn' => 60],
            ['name' => 'Peu commune', 'percentageSpawn' => 25],
            ['name' => 'Rare', 'percentageSpawn' => 10],
            ['name' => 'Ultra Rare', 'percentageSpawn' => 3],
            ['name' => 'Double Rare', 'percentageSpawn' => 1],
            ['name' => 'Illustration Rare', 'percentageSpawn' => 0],
            ['name' => 'Illustration spéciale rare', 'percentageSpawn' => 0],
            ['name' => 'Hyper Rare', 'percentageSpawn' => 0],
            ['name' => 'Promo', 'percentageSpawn' => 1],
        ];

        DB::table('rarity')->insert($rarities);
    }
}
