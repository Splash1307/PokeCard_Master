<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CardsSeeder extends Seeder
{
    public function run()
    {
        $sets = [
            'swsh1' => 216, // nombre de cartes pour swsh1
            'swsh7' => 237,  // nombre de cartes pour swsh7
        ];

        $typeTranslation = [
            'Metal' => 'Acier',
            'Obscurité' => 'Ténèbres',
            'Incolore' => 'Normal',
        ];

        foreach ($sets as $setAbbr => $cardCount) {
            $setId = DB::table('sets')->where('abbreviation', $setAbbr)->value('id');

            for ($i = 1; $i <= $cardCount; $i++) {
                $cardId = $setAbbr . '-' . $i;
                $response = Http::get("https://api.tcgdex.net/v2/fr/cards/$cardId");

                if (!$response->successful()) {
                    echo "❌ Carte non trouvée : $cardId" . PHP_EOL;
                    continue;
                }

                $card = $response->json();

                $primaryTypeName = $card['types'][0] ?? null;
                $secondaryTypeName = $card['types'][1] ?? null;
                $rarityName = $card['rarity'] ?? 'Commune';

                // Traduction des types
                if (isset($typeTranslation[$primaryTypeName])) {
                    $primaryTypeName = $typeTranslation[$primaryTypeName];
                }
                if (isset($typeTranslation[$secondaryTypeName])) {
                    $secondaryTypeName = $typeTranslation[$secondaryTypeName];
                }

                $primaryTypeId = $primaryTypeName
                    ? DB::table('types')->where('name', ucfirst($primaryTypeName))->value('id')
                    : null;

                $secondaryTypeId = $secondaryTypeName
                    ? DB::table('types')->where('name', ucfirst($secondaryTypeName))->value('id')
                    : null;

                $rarityId = DB::table('rarity')->where('name', ucfirst($rarityName))->value('id') ??
                    DB::table('rarity')->insertGetId(['name' => ucfirst($rarityName), 'percentageSpawn' => 0]);

                $serieName = DB::table('series')
                    ->join('sets', 'series.id', '=', 'sets.serie_id')
                    ->where('sets.id', $setId)
                    ->value('series.abbreviation');


                DB::table('cards')->insert([
                    'name' => $card['name'],
                    'localId' => $card['localId'],
                    'image' => "/assets/cards/{$serieName}/{$setAbbr}/{$card['localId']}.png",
                    'set_id' => $setId,
                    'rarity_id' => $rarityId,
                    'primaryType' => $primaryTypeId,
                    'secondaryType' => $secondaryTypeId,
                ]);

                echo "✔️ Carte ajoutée : " . $card['name'] . " ($cardId)" . PHP_EOL;
            }
        }
    }
}
