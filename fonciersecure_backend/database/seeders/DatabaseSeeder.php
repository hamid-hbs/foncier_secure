<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LocalisationSeeder::class,
            UserSeeder::class,
            ProfessionnelSeeder::class,
            ParcelleSeeder::class,
            DemandeAchatSeeder::class,
            DossierTransactionSeeder::class,
            MissionSeeder::class,
            FactureSeeder::class,
            DocumentSeeder::class,
            MessageSeeder::class,
            RendezVousSeeder::class,
            SupportTicketSeeder::class,
        ]);
    }
}
