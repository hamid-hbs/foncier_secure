<?php

namespace Database\Seeders;

use App\Models\Parcelle;
use App\Models\ParcelleDocument;
use Illuminate\Database\Seeder;

class ParcelleSeeder extends Seeder
{
    public function run(): void
    {
        $p1 = Parcelle::create([
            'proprietaire_id' => 3,
            'code' => 'FS-00001',
            'titre' => 'Terrain résidentiel à Ganhi',
            'description' => 'Magnifique terrain de 500m² situé dans le quartier Ganhi, à proximité de toutes les commodités.',
            'commune_id' => 1,
            'arrondissement_id' => 1,
            'quartier_id' => 1,
            'superficie' => 500.00,
            'latitude' => 6.3589,
            'longitude' => 2.4256,
            'prix_estimatif' => 15000000,
            'statut' => 'libre',
        ]);

        ParcelleDocument::create([
            'parcelle_id' => $p1->id,
            'type_document' => 'tf',
            'nom_fichier' => 'TF_Ganhi.pdf',
            'chemin_fichier' => 'parcelles/1/TF_Ganhi.pdf',
            'hash_sha256' => hash('sha256', 'TF_Ganhi_content'),
            'taille' => 1024,
            'uploaded_at' => now(),
        ]);

        ParcelleDocument::create([
            'parcelle_id' => $p1->id,
            'type_document' => 'photo',
            'nom_fichier' => 'terrain_vue1.jpg',
            'chemin_fichier' => 'parcelles/1/terrain_vue1.jpg',
            'hash_sha256' => hash('sha256', 'photo_content_1'),
            'taille' => 2048,
            'uploaded_at' => now(),
        ]);

        $p2 = Parcelle::create([
            'proprietaire_id' => 6,
            'code' => 'FS-00002',
            'titre' => 'Parcelle à vendre - Akpakpa',
            'description' => 'Parcelle viabilisée de 300m² dans la zone résidentielle d\'Akpakpa.',
            'commune_id' => 1,
            'arrondissement_id' => 2,
            'quartier_id' => 4,
            'superficie' => 300.00,
            'latitude' => 6.3700,
            'longitude' => 2.4400,
            'prix_estimatif' => 10000000,
            'statut' => 'libre',
        ]);

        $p3 = Parcelle::create([
            'proprietaire_id' => 3,
            'code' => 'FS-00003',
            'titre' => 'Terrain familial à Calavi',
            'description' => 'Grand terrain familial de 800m² à Abomey-Calavi.',
            'commune_id' => 2,
            'arrondissement_id' => 3,
            'quartier_id' => 6,
            'superficie' => 800.00,
            'latitude' => 6.4500,
            'longitude' => 2.3400,
            'prix_estimatif' => 20000000,
            'statut' => 'libre',
        ]);

        $p4 = Parcelle::create([
            'proprietaire_id' => 5,
            'code' => 'FS-00004',
            'titre' => 'Terrain à Zogbadjè',
            'description' => 'Terrain constructible de 400m² à Zogbadjè, zone en plein développement.',
            'commune_id' => 1,
            'arrondissement_id' => 3,
            'quartier_id' => 9,
            'superficie' => 400.00,
            'latitude' => 6.4428,
            'longitude' => 2.3500,
            'prix_estimatif' => 12000000,
            'statut' => 'libre',
        ]);

        Parcelle::create([
            'proprietaire_id' => 5,
            'code' => 'FS-00005',
            'titre' => 'Terrain à Ouèdo',
            'description' => 'Terrain agricole de 2000m² à Ouèdo, idéal pour projet agricole.',
            'commune_id' => 2,
            'arrondissement_id' => 5,
            'quartier_id' => 15,
            'superficie' => 2000.00,
            'latitude' => 6.4845,
            'longitude' => 2.3145,
            'prix_estimatif' => 5000000,
            'statut' => 'libre',
        ]);
    }
}
