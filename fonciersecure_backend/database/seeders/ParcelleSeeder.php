<?php

namespace Database\Seeders;

use App\Models\Parcelle;
use App\Models\Propriete;
use Illuminate\Database\Seeder;

class ParcelleSeeder extends Seeder
{
    public function run(): void
    {
        $parcelles = [
            ['code_parcelle' => 'CO-2024-001', 'titre_parcelle' => 'Terrain Gbégamey', 'commune_id' => 1, 'arrondissement_id' => 1, 'quartier_id' => 1, 'superficie' => 500, 'latitude' => 6.367, 'longitude' => 2.425, 'valeur_estimee' => 45000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'CO-2024-002', 'titre_parcelle' => 'Terrain Agla', 'commune_id' => 1, 'arrondissement_id' => 1, 'quartier_id' => 2, 'superficie' => 350, 'latitude' => 6.382, 'longitude' => 2.445, 'valeur_estimee' => 30000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'CO-2024-003', 'titre_parcelle' => 'Terrain Fidjrossè', 'commune_id' => 1, 'arrondissement_id' => 2, 'quartier_id' => 3, 'superficie' => 800, 'latitude' => 6.345, 'longitude' => 2.388, 'valeur_estimee' => 65000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'CO-2024-004', 'titre_parcelle' => 'Terrain Lomé', 'commune_id' => 1, 'arrondissement_id' => 2, 'quartier_id' => 4, 'superficie' => 250, 'latitude' => 6.360, 'longitude' => 2.410, 'valeur_estimee' => 20000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'PN-2024-001', 'titre_parcelle' => 'Terrain Ouando', 'commune_id' => 2, 'arrondissement_id' => 3, 'quartier_id' => 5, 'superficie' => 600, 'latitude' => 6.478, 'longitude' => 2.608, 'valeur_estimee' => 35000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'PN-2024-002', 'titre_parcelle' => 'Terrain Oganla', 'commune_id' => 2, 'arrondissement_id' => 4, 'quartier_id' => 7, 'superficie' => 1000, 'latitude' => 6.490, 'longitude' => 2.630, 'valeur_estimee' => 55000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'PK-2024-001', 'titre_parcelle' => 'Terrain Zongo', 'commune_id' => 3, 'arrondissement_id' => 5, 'quartier_id' => 9, 'superficie' => 750, 'latitude' => 9.340, 'longitude' => 2.620, 'valeur_estimee' => 25000000, 'statut' => 'libre', 'type_acquisition' => 'achat'],
            ['code_parcelle' => 'PK-2024-002', 'titre_parcelle' => 'Terrain Kpébié', 'commune_id' => 3, 'arrondissement_id' => 6, 'quartier_id' => 11, 'superficie' => 1200, 'latitude' => 9.350, 'longitude' => 2.630, 'valeur_estimee' => 40000000, 'statut' => 'vendue', 'type_acquisition' => 'achat'],
        ];

        foreach ($parcelles as $data) {
            $parcelle = Parcelle::create($data);
            Propriete::create([
                'parcelle_id' => $parcelle->id,
                'user_id' => fake()->randomElement([2, 3, 4, 7]),
                'date_debut' => now()->subMonths(fake()->numberBetween(1, 36)),
            ]);
        }
    }
}
