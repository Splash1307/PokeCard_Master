<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Admin'],
            ['name' => 'Player'],
        ];

        DB::table('roles')->insert($roles);
    }
}
