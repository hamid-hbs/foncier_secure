<?php

namespace Database\Seeders;

use App\Models\Professionnel;
use Illuminate\Database\Seeder;

class ProfessionnelSeeder extends Seeder
{
    public function run(): void
    {
        Professionnel::create([
            'user_id' => 5,
            'type' => 'geometre',
            'numero_enregistrement' => 'GEO-2024-001',
            'date_enregistrement' => '2024-03-15',
            'specialisation' => 'Bornage, Plan topographique, Division parcellaire',
            'commune_id' => 1,
            'adresse_bureau' => 'Cotonou, Gbégamey',
            'taux_horaire' => 25000,
            'note_moyenne' => 4.2,
            'is_verified' => true,
        ]);

        Professionnel::create([
            'user_id' => 6,
            'type' => 'notaire',
            'numero_enregistrement' => 'NOT-2023-012',
            'date_enregistrement' => '2023-06-20',
            'specialisation' => 'Vente immobilière, Succession, Donation',
            'commune_id' => 1,
            'adresse_bureau' => 'Cotonou, Fidjrossè',
            'taux_horaire' => 50000,
            'note_moyenne' => 4.5,
            'is_verified' => true,
        ]);

        Professionnel::create([
            'user_id' => 8,
            'type' => 'geometre',
            'numero_enregistrement' => 'GEO-2023-045',
            'date_enregistrement' => '2023-01-10',
            'specialisation' => 'Topographie, Cadastre, Aménagement foncier',
            'commune_id' => 3,
            'adresse_bureau' => 'Parakou, Zongo',
            'taux_horaire' => 20000,
            'note_moyenne' => 3.8,
            'is_verified' => true,
        ]);

        Professionnel::create([
            'user_id' => 9,
            'type' => 'notaire',
            'numero_enregistrement' => 'NOT-2022-008',
            'date_enregistrement' => '2022-11-05',
            'specialisation' => 'Transaction immobilière, Constitution de société',
            'commune_id' => 2,
            'adresse_bureau' => 'Porto-Novo, Ouando',
            'taux_horaire' => 45000,
            'note_moyenne' => 4.8,
            'is_verified' => true,
        ]);
    }
}
