<?php

namespace Database\Seeders;

use App\Models\BlockchainLog;
use Illuminate\Database\Seeder;

class BlockchainSeeder extends Seeder
{
    public function run(): void
    {
        BlockchainLog::enregistrer('system_init', null, 'system', null, ['event' => 'Plateforme initialisée']);
        BlockchainLog::enregistrer('user_registered', 4, 'auth', 5, ['email' => 'citoyen@fonciersecure.bj', 'role' => 'citoyen']);
        BlockchainLog::enregistrer('user_registered', 5, 'auth', 6, ['email' => 'beatrice.hounkpatin@email.bj', 'role' => 'citoyen']);
        BlockchainLog::enregistrer('verification_created', 4, 'verification', 1, ['titre' => 'Terrain résidentiel à Ganhi', 'score' => 85]);
        BlockchainLog::enregistrer('verification_created', 5, 'verification', 2, ['titre' => 'Terrain à Zogbadjè', 'score' => 60]);
        BlockchainLog::enregistrer('geometre_sollicite', 5, 'verification', 2, ['geometre_id' => 2]);
        BlockchainLog::enregistrer('rapport_geometre_favorable', 2, 'verification', 2, ['avis' => 'favorable']);
        BlockchainLog::enregistrer('verification_created', 6, 'verification', 3, ['titre' => 'Parcelle à vendre - Akpakpa', 'score' => 35]);
        BlockchainLog::enregistrer('transaction_created', 4, 'transaction', 1, ['titre' => 'Vente terrain Ganhi - Akakpo / Sossou']);
        BlockchainLog::enregistrer('transaction_status_cloture', 4, 'transaction', 1, ['ancien_statut' => 'acte_signe', 'nouveau_statut' => 'cloture']);
        BlockchainLog::enregistrer('succession_created', 4, 'succession', 1, ['defunt' => 'Pierre Akakpo']);
        BlockchainLog::enregistrer('succession_partage_propose', 4, 'succession', 1, []);
        BlockchainLog::enregistrer('succession_approuvee', 7, 'succession', 1, ['heritier_id' => 1]);
        BlockchainLog::enregistrer('coffre_upload', 4, 'coffre', 1, ['nom_fichier' => 'TF_12345_Ganhi.pdf', 'dossier_id' => 1]);
        BlockchainLog::enregistrer('role_request_valide', 1, 'admin', 1, ['user_id' => 8, 'role' => 'geometre']);
    }
}
