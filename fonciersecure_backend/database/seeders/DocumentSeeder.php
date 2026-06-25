<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        Document::create([
            'documentable_type' => 'Parcelle',
            'documentable_id' => 1,
            'type_document' => 'titre_foncier',
            'nom_original' => 'TF-Gbegamey-2024.pdf',
            'chemin_fichier' => 'documents/parcelles/1/TF-Gbegamey-2024.pdf',
            'hash_sha256' => hash('sha256', 'tf1'),
            'taille_bytes' => 245760,
            'mime_type' => 'application/pdf',
            'uploaded_by_id' => 2,
        ]);

        Document::create([
            'documentable_type' => 'Parcelle',
            'documentable_id' => 1,
            'type_document' => 'adc',
            'nom_original' => 'ADC-Gbegamey-2024.pdf',
            'chemin_fichier' => 'documents/parcelles/1/ADC-Gbegamey-2024.pdf',
            'hash_sha256' => hash('sha256', 'adc1'),
            'taille_bytes' => 102400,
            'mime_type' => 'application/pdf',
            'uploaded_by_id' => 2,
        ]);

        Document::create([
            'documentable_type' => 'DossierTransaction',
            'documentable_id' => 1,
            'type_document' => 'acte_vente',
            'nom_original' => 'Acte-Vente-Kpebie.pdf',
            'chemin_fichier' => 'documents/transactions/1/Acte-Vente-Kpebie.pdf',
            'hash_sha256' => hash('sha256', 'acte1'),
            'taille_bytes' => 512000,
            'mime_type' => 'application/pdf',
            'uploaded_by_id' => 9,
        ]);

        Document::create([
            'documentable_type' => 'Mission',
            'documentable_id' => 1,
            'type_document' => 'autre',
            'nom_original' => 'Rapport-Bornage-Gbegamey.pdf',
            'chemin_fichier' => 'rapports/missions/1/Rapport-Bornage-Gbegamey.pdf',
            'hash_sha256' => hash('sha256', 'rapport1'),
            'taille_bytes' => 1048576,
            'mime_type' => 'application/pdf',
            'uploaded_by_id' => 5,
        ]);
    }
}
