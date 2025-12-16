<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeriesSeeder extends Seeder
{
    public function run()
    {
        $series = [
            ['name' => 'Épée et Bouclier', 'abbreviation' => 'swsh'],
            ['name' => 'Scarlet et Violet', 'abbreviation' => 'sv'],
            ['name' => 'Noir et Blanc', 'abbreviation' => 'bw'],
            ['name' => 'XY', 'abbreviation' => 'xy']
        ];

        DB::table('series')->insert($series);
    }
}
