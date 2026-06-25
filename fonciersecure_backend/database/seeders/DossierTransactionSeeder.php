<?php

namespace Database\Seeders;

use App\Models\DossierTransaction;
use Illuminate\Database\Seeder;

class DossierTransactionSeeder extends Seeder
{
    public function run(): void
    {
        DossierTransaction::create([
            'demande_achat_id' => 3,
            'parcelle_id' => 8,
            'notaire_id' => 9,
            'geometre_assigne_id' => 5,
            'vendeur_id' => 3,
            'acheteur_id' => 2,
            'statut' => 'en_attente',
            'prix_vente' => 40000000,
            'frais_enregistrement' => 1000000,
        ]);
    }
}
