<?php

namespace Database\Seeders;

use App\Models\CoffreDocument;
use App\Models\CoffreDossier;
use App\Models\CoffrePartage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoffreSeeder extends Seeder
{
    public function run(): void
    {
        $dossier1 = CoffreDossier::create([
            'user_id' => 4,
            'titre' => 'Mes documents fonciers',
            'description' => 'Tous les documents relatifs à mes terrains',
        ]);

        CoffreDocument::create([
            'dossier_id' => $dossier1->id,
            'nom_fichier' => 'TF_12345_Ganhi.pdf',
            'chemin_fichier' => 'coffre/1/TF_12345_Ganhi.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_1'),
            'version' => 2,
            'taille' => 204800,
            'type_mime' => 'application/pdf',
        ]);

        CoffreDocument::create([
            'dossier_id' => $dossier1->id,
            'nom_fichier' => 'ADC_67890_Ganhi.pdf',
            'chemin_fichier' => 'coffre/1/ADC_67890_Ganhi.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_2'),
            'version' => 1,
            'taille' => 153600,
            'type_mime' => 'application/pdf',
        ]);

        CoffreDocument::create([
            'dossier_id' => $dossier1->id,
            'nom_fichier' => 'Plan_maison.pdf',
            'chemin_fichier' => 'coffre/1/Plan_maison.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_3'),
            'version' => 1,
            'taille' => 512000,
            'type_mime' => 'application/pdf',
        ]);

        $dossier2 = CoffreDossier::create([
            'user_id' => 4,
            'titre' => 'Succession père',
            'description' => 'Documents relatifs à la succession de mon père',
        ]);

        CoffreDocument::create([
            'dossier_id' => $dossier2->id,
            'nom_fichier' => 'Acte_deces_pere.pdf',
            'chemin_fichier' => 'coffre/2/Acte_deces_pere.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_4'),
            'version' => 1,
            'taille' => 102400,
            'type_mime' => 'application/pdf',
        ]);

        $dossier3 = CoffreDossier::create([
            'user_id' => 8,
            'titre' => 'Documents achats terrains',
            'description' => 'Archives de tous mes achats immobiliers',
        ]);

        $docPartage = CoffreDocument::create([
            'dossier_id' => $dossier3->id,
            'nom_fichier' => 'Compromis_vente_Ganhi.pdf',
            'chemin_fichier' => 'coffre/3/Compromis_vente_Ganhi.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_5'),
            'version' => 1,
            'taille' => 256000,
            'type_mime' => 'application/pdf',
        ]);

        CoffrePartage::create([
            'document_id' => $docPartage->id,
            'partage_avec' => 'notaire@fonciersecure.bj',
            'token' => Str::random(64),
            'expire_le' => now()->addDays(30),
        ]);

        $dossier4 = CoffreDossier::create([
            'user_id' => 5,
            'titre' => 'Titres de propriété',
        ]);

        CoffreDocument::create([
            'dossier_id' => $dossier4->id,
            'nom_fichier' => 'TF_Zogbadjè.pdf',
            'chemin_fichier' => 'coffre/4/TF_Zogbadjè.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_6'),
            'version' => 1,
            'taille' => 180000,
            'type_mime' => 'application/pdf',
        ]);

        $dossier5 = CoffreDossier::create([
            'user_id' => 7,
            'titre' => 'Dossier familial',
            'description' => 'Documents de la famille Bio',
        ]);

        CoffreDocument::create([
            'dossier_id' => $dossier5->id,
            'nom_fichier' => 'Certificat_coutume.pdf',
            'chemin_fichier' => 'coffre/5/Certificat_coutume.pdf',
            'hash_sha256' => hash('sha256', 'coffre_doc_7'),
            'version' => 1,
            'taille' => 204800,
            'type_mime' => 'application/pdf',
        ]);
    }
}
