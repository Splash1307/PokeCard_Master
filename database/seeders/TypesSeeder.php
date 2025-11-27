<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Acier'],
            ['name' => 'Combat'],
            ['name' => 'Dragon'],
            ['name' => 'Eau'],
            ['name' => 'Électrique'],
            ['name' => 'Fée'],
            ['name' => 'Feu'],
            ['name' => 'Glace'],
            ['name' => 'Insecte'],
            ['name' => 'Normal'],
            ['name' => 'Plante'],
            ['name' => 'Poison'],
            ['name' => 'Psy'],
            ['name' => 'Roche'],
            ['name' => 'Sol'],
            ['name' => 'Spectre'],
            ['name' => 'Ténèbres'],
            ['name' => 'Vol'],
        ];

        DB::table('types')->insert($types);
    }
}
