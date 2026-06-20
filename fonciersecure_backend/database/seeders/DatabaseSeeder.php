<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            LocalisationSeeder::class,
            UserSeeder::class,
            RoleRequestSeeder::class,
            ProfessionnelSeeder::class,
            ParcelleSeeder::class,
            DemandeAchatSeeder::class,
            VerificationSeeder::class,
            TransactionSeeder::class,
            CoffreSeeder::class,
            BlockchainSeeder::class,
            SupportTicketSeeder::class,
        ]);
    }
}
