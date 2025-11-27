<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CardsSeeder extends Seeder
{
    public function run()
    {
        $setId = DB::table('sets')->where('abbreviation', 'swsh1')->value('id');

        $typeTranslation = [
            'Metal' => 'Acier',
            'Obscurité' => 'Ténèbres',
            'Incolore' => 'Normal',
        ];

        for ($i = 1; $i <= 216; $i++) {
            $cardId = 'swsh1-' . $i;
            $response = Http::get("https://api.tcgdex.net/v2/fr/cards/$cardId");

            if (!$response->successful()) {
                continue;
            }

            $card = $response->json();

            $primaryTypeName = $card['types'][0] ?? null;
            $secondaryTypeName = $card['types'][1] ?? null;
            $rarityName = $card['rarity'] ?? 'Commune';

            // Conversion des types anglais vers français si nécessaire
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

            DB::table('cards')->insert([
                'name' => $card['name'],
                'localId' => $card['localId'],
                'logo' => $card['image'],
                'set_id' => $setId,
                'rarity_id' => $rarityId,
                'primaryType' => $primaryTypeId,
                'secondaryType' => $secondaryTypeId,
            ]);

            echo "✔️ Carte ajoutée : " . $card['name'] . PHP_EOL;
        }
    }
}
