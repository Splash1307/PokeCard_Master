<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $levels = [];

        for ($i = 1; $i <= 100; $i++) {
            if ($i >= 1 && $i <= 9) {
                $nbCoins = 25;
            } elseif ($i >= 10 && $i <= 19) {
                $nbCoins = 50;
            } elseif ($i >= 20 && $i <= 29) {
                $nbCoins = 75;
            } elseif ($i >= 30 && $i <= 39) {
                $nbCoins = 100;
            } else {
                $tier = (int)floor(($i - 40) / 10);
                $nbCoins = 125 + ($tier * 25);
            }

            if ($i >= 1 && $i <= 9) {
                $nbBooster = 0;
            } elseif ($i >= 10 && $i <= 19) {
                $nbBooster = 1;
            } elseif ($i >= 20 && $i <= 39) {
                $nbBooster = 2;
            } elseif ($i >= 40 && $i <= 59) {
                $nbBooster = 3;
            } else {
                $tier = (int)floor(($i - 60) / 20);
                $nbBooster = 4 + $tier;
            }

            $levels[] = [
                'level' => $i,
                'nbCoins' => $nbCoins,
                'nbBooster' => $nbBooster,
            ];
        }

        DB::table('levels')->insert($levels);
    }
}
