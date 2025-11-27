<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeriesSeeder extends Seeder
{
    public function run()
    {
        $series = [
            ['name' => 'Épée et Bouclier', 'abbreviation' => 'swsh']
        ];

        DB::table('series')->insert($series);
    }
}
