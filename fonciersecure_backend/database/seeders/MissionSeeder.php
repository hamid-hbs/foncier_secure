<?php

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        Mission::create([
            'citoyen_id' => 2,
            'geometre_id' => 5,
            'parcelle_id' => 1,
            'titre' => 'Bornage parcelle Gbégamey',
            'description' => 'Besoin d\'un bornage et plan topographique pour ma parcelle à Gbégamey.',
            'type_mission' => 'leve_terrain',
            'statut' => 'terminee',
            'prix_estime' => 150000,
            'rapport_path' => 'rapports/missions/1/rapport-bornage.pdf',
            'date_soumise' => now()->subDays(20),
            'date_acceptee' => now()->subDays(18),
            'completed_at' => now()->subDays(5),
        ]);

        Mission::create([
            'citoyen_id' => 3,
            'geometre_id' => 8,
            'parcelle_id' => 7,
            'titre' => 'Division parcellaire Zongo',
            'description' => 'Je souhaite diviser ma parcelle en deux lots.',
            'type_mission' => 'plan_topo',
            'statut' => 'acceptee',
            'prix_estime' => 200000,
            'date_soumise' => now()->subDays(3),
            'date_acceptee' => now(),
        ]);

        Mission::create([
            'citoyen_id' => 4,
            'geometre_id' => 5,
            'titre' => 'Levée topographique terrain agricole',
            'description' => 'Terrain agricole de 5 ha à Dokè nécessitant une levée topographique.',
            'type_mission' => 'leve_terrain',
            'statut' => 'soumise',
            'prix_estime' => 300000,
            'date_soumise' => now(),
        ]);
    }
}
