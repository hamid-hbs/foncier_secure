<?php

namespace Database\Seeders;

use App\Models\Arrondissement;
use App\Models\Commune;
use App\Models\Quartier;
use Illuminate\Database\Seeder;

class LocalisationSeeder extends Seeder
{
    public function run(): void
    {
        $cotonou = Commune::create(['nom' => 'Cotonou', 'code_commune' => 'CO', 'departement' => 'Littoral', 'latitude' => 6.367, 'longitude' => 2.425]);
        $pk11 = Arrondissement::create(['commune_id' => $cotonou->id, 'code_arrondissement' => 'CO-11', 'nom' => '11ᵉ Arrondissement']);
        $pk13 = Arrondissement::create(['commune_id' => $cotonou->id, 'code_arrondissement' => 'CO-13', 'nom' => '13ᵉ Arrondissement']);
        foreach ([
            ['arrondissement_id' => $pk11->id, 'nom' => 'Gbégamey', 'latitude' => 6.367, 'longitude' => 2.425],
            ['arrondissement_id' => $pk11->id, 'nom' => 'Agla'],
            ['arrondissement_id' => $pk13->id, 'nom' => 'Fidjrossè'],
            ['arrondissement_id' => $pk13->id, 'nom' => 'Lomé'],
        ] as $q) { Quartier::create($q); }

        $pn = Commune::create(['nom' => 'Porto-Novo', 'code_commune' => 'PN', 'departement' => 'Ouémé', 'latitude' => 6.478, 'longitude' => 2.608]);
        $ouando = Arrondissement::create(['commune_id' => $pn->id, 'code_arrondissement' => 'PN-OD', 'nom' => 'Ouando']);
        $totale = Arrondissement::create(['commune_id' => $pn->id, 'code_arrondissement' => 'PN-TT', 'nom' => 'Totale']);
        foreach ([
            ['arrondissement_id' => $ouando->id, 'nom' => 'Agoè'],
            ['arrondissement_id' => $ouando->id, 'nom' => 'Dowa'],
            ['arrondissement_id' => $totale->id, 'nom' => 'Oganla'],
            ['arrondissement_id' => $totale->id, 'nom' => 'Dokè'],
        ] as $q) { Quartier::create($q); }

        $pk = Commune::create(['nom' => 'Parakou', 'code_commune' => 'PK', 'departement' => 'Borgou', 'latitude' => 9.340, 'longitude' => 2.620]);
        $pk1 = Arrondissement::create(['commune_id' => $pk->id, 'code_arrondissement' => 'PK-1', 'nom' => '1ᵉʳ Arrondissement']);
        $pk3 = Arrondissement::create(['commune_id' => $pk->id, 'code_arrondissement' => 'PK-3', 'nom' => '3ᵉ Arrondissement']);
        foreach ([
            ['arrondissement_id' => $pk1->id, 'nom' => 'Zongo'],
            ['arrondissement_id' => $pk1->id, 'nom' => 'Dépôt'],
            ['arrondissement_id' => $pk3->id, 'nom' => 'Kpébié'],
            ['arrondissement_id' => $pk3->id, 'nom' => 'Albarika'],
        ] as $q) { Quartier::create($q); }
    }
}
