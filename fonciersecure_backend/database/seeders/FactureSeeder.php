<?php

namespace Database\Seeders;

use App\Models\Facture;
use Illuminate\Database\Seeder;

class FactureSeeder extends Seeder
{
    public function run(): void
    {
        Facture::create([
            'facturable_type' => 'DossierTransaction',
            'facturable_id' => 1,
            'prestataire_id' => 6,
            'client_id' => 2,
            'reference' => 'FAC-NOT-2025-001',
            'montant_total' => 250000,
            'montant_paye' => 0,
            'description' => 'Frais de notaire pour transaction Kpébié',
            'statut' => 'brouillon',
            'date_emission' => now()->subDays(2),
        ]);

        Facture::create([
            'facturable_type' => 'Mission',
            'facturable_id' => 1,
            'prestataire_id' => 5,
            'client_id' => 2,
            'reference' => 'FAC-GEO-2025-001',
            'montant_total' => 150000,
            'montant_paye' => 150000,
            'description' => 'Honoraires géomètre - Bornage Gbégamey',
            'statut' => 'payee',
            'date_emission' => now()->subDays(10),
            'date_paiement' => now()->subDays(3),
            'moyen_paiement' => 'virement',
        ]);
    }
}
