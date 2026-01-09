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
            'swsh1' => 216,
            'swsh7' => 237,
            'sv08.5' => 180,
            'bw10' => 105,
            'xy11' => 116,
        ];

        foreach ($sets as $setAbbr => $cardCount) {
            $setId = DB::table('sets')->where('abbreviation', $setAbbr)->value('id');
            $serieName = DB::table('series')
                ->join('sets', 'series.id', '=', 'sets.serie_id')
                ->where('sets.id', $setId)
                ->value('series.abbreviation');

            echo "🔄 Traitement de $setAbbr ($cardCount cartes)..." . PHP_EOL;

            // Préparer les URLs pour les requêtes concurrentes
            $requests = [];
            for ($i = 1; $i <= $cardCount; $i++) {
                $requests[] = [
                    'id' => $i,
                    'unpadded' => "{$setAbbr}-{$i}",
                    'padded' => "{$setAbbr}-" . str_pad($i, 3, '0', STR_PAD_LEFT)
                ];
            }

            // Traiter par lots de 50 pour éviter de surcharger l'API
            $chunks = array_chunk($requests, 50);
            $allCardsData = [];

            foreach ($chunks as $chunkIndex => $chunk) {
                echo "  📦 Lot " . ($chunkIndex + 1) . "/" . count($chunks) . PHP_EOL;

                // Requêtes HTTP concurrentes avec Http::pool()
                $responses = Http::pool(function ($pool) use ($chunk) {
                    $poolRequests = [];
                    foreach ($chunk as $index => $card) {
                        $poolRequests[$index] = $pool->get("https://api.tcgdex.net/v2/fr/cards/{$card['unpadded']}");
                    }
                    return $poolRequests;
                });

                // Traiter les réponses
                foreach ($chunk as $index => $card) {
                    $response = $responses[$index];

                    // Si échec avec format non-paddé, essayer avec format paddé
                    if (!$response->successful()) {
                        $response = Http::get("https://api.tcgdex.net/v2/fr/cards/{$card['padded']}");
                    }

                    if (!$response->successful()) {
                        echo "  ❌ Carte non trouvée : {$card['unpadded']}" . PHP_EOL;
                        continue;
                    }

                    $cardData = $response->json();

                    $primaryTypeName = $cardData['types'][0] ?? null;
                    $secondaryTypeName = $cardData['types'][1] ?? null;
                    $rarityName = $cardData['rarity'] ?? 'Commune';

                    $primaryTypeId = $primaryTypeName
                        ? DB::table('types')->where('name', ucfirst($primaryTypeName))->value('id')
                        : null;

                    $secondaryTypeId = $secondaryTypeName
                        ? DB::table('types')->where('name', ucfirst($secondaryTypeName))->value('id')
                        : null;

                    $rarityId = DB::table('rarity')->where('name', ucfirst($rarityName))->value('id');
                    if (!$rarityId) {
                        $rarityId = DB::table('rarity')->insertGetId([
                            'name' => ucfirst($rarityName),
                            'percentageSpawn' => 0
                        ]);
                    }

                    $allCardsData[] = [
                        'name' => $cardData['name'],
                        'localId' => $cardData['localId'],
                        'image' => "/assets/cards/{$serieName}/{$setAbbr}/{$cardData['localId']}.png",
                        'set_id' => $setId,
                        'rarity_id' => $rarityId,
                        'primaryType_id' => $primaryTypeId,
                        'secondaryType_id' => $secondaryTypeId,
                    ];
                }
            }

            // Insertion en masse de toutes les cartes du set
            if (!empty($allCardsData)) {
                DB::table('cards')->insert($allCardsData);
                echo "✅ {$setAbbr} terminé : " . count($allCardsData) . " cartes insérées" . PHP_EOL;
            }
        }

        echo "🎉 Seeding terminé !" . PHP_EOL;
    }
}
