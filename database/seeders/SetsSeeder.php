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
            ['name' => 'Évolution Céleste', 'abbreviation' => 'swsh7', 'serie_id' => 1],
            ['name' => 'Évolutions Prismatiques', 'abbreviation' => 'sv08.5', 'serie_id' => 2],
            ['name' => 'Explosion Plasma', 'abbreviation' => 'bw10', 'serie_id' => 3],
            ['name' => 'Offensive Vapeur', 'abbreviation' => 'xy11', 'serie_id' => 4],
        ];

        DB::table('sets')->insert($sets);
    }
}
