<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetsSeeder extends Seeder
{
    public function run()
    {
        $sets = [
            ['name' => 'Épée et Bouclier', 'abbreviation' => 'swsh1', 'serie_id' => 1],
            ['name' => 'Évolution Céleste', 'abbreviation' => 'swsh7', 'serie_id' => 1]
        ];

        DB::table('sets')->insert($sets);
    }
}
