<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Plante', 'logo' => '/assets/energies/Plante.png'],
            ['name' => 'Feu' , 'logo' => '/assets/energies/Feu.png'],
            ['name' => 'Eau' , 'logo' => '/assets/energies/Eau.png'],
            ['name' => 'Électrique' , 'logo' => '/assets/energies/Électrique.png'],
            ['name' => 'Combat' , 'logo' => '/assets/energies/Combat.png'],
            ['name' => 'Psy' , 'logo' => '/assets/energies/Psy.png'],
            ['name' => 'Incolore' , 'logo' => '/assets/energies/Incolore.png'],
            ['name' => 'Obscurité' , 'logo' => '/assets/energies/Obscurité.png'],
            ['name' => 'Métal' ,'logo' => 'assets/energies/Métal.png'],
            ['name' => 'Dragon' , 'logo' => '/assets/energies/Dragon.png'],
            ['name' => 'Fée' , 'logo' => '/assets/energies/Fée.png'],
        ];

        DB::table('types')->insert($types);
    }
}
