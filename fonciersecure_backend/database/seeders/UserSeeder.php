<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['nom' => 'Admin', 'prenom' => 'Super', 'email' => 'admin@fonciersecure.bj', 'telephone' => '+22901000001', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true, 'indice_confiance' => 100, 'email_verified_at' => now()]);
        User::create(['nom' => 'Hounsou', 'prenom' => 'Patrick', 'email' => 'geometre@fonciersecure.bj', 'telephone' => '+22901000003', 'password' => Hash::make('password'), 'role' => 'geometre', 'is_active' => true, 'indice_confiance' => 78, 'email_verified_at' => now()]);
        User::create(['nom' => 'Dossou', 'prenom' => 'Martine', 'email' => 'notaire@fonciersecure.bj', 'telephone' => '+22901000004', 'password' => Hash::make('password'), 'role' => 'notaire', 'is_active' => true, 'indice_confiance' => 92, 'email_verified_at' => now()]);
        User::create(['nom' => 'Akakpo', 'prenom' => 'Jean', 'email' => 'citoyen@fonciersecure.bj', 'telephone' => '+22901000005', 'password' => Hash::make('password'), 'role' => 'citoyen', 'is_active' => true, 'indice_confiance' => 55, 'email_verified_at' => now()]);
        User::create(['nom' => 'Hounkpatin', 'prenom' => 'Béatrice', 'email' => 'beatrice.hounkpatin@email.bj', 'telephone' => '+22901000006', 'password' => Hash::make('password'), 'role' => 'citoyen', 'is_active' => true, 'indice_confiance' => 70, 'email_verified_at' => now()]);
        User::create(['nom' => 'Koffi', 'prenom' => 'Amadou', 'email' => 'amadou.koffi@email.bj', 'telephone' => '+22901000007', 'password' => Hash::make('password'), 'role' => 'citoyen', 'is_active' => true, 'indice_confiance' => 30, 'email_verified_at' => now()]);
        User::create(['nom' => 'Bio', 'prenom' => 'Fatima', 'email' => 'fatima.bio@email.bj', 'telephone' => '+22901000008', 'password' => Hash::make('password'), 'role' => 'citoyen', 'is_active' => true, 'indice_confiance' => 15, 'email_verified_at' => now()]);
        User::create(['nom' => 'Sossou', 'prenom' => 'David', 'email' => 'david.sossou@email.bj', 'telephone' => '+22901000009', 'password' => Hash::make('password'), 'role' => 'citoyen', 'is_active' => true, 'indice_confiance' => 88, 'email_verified_at' => now()]);
        User::create(['nom' => 'Gbaguidi', 'prenom' => 'Bénédicte', 'email' => 'benedicte.gbaguidi@email.bj', 'telephone' => '+22901000010', 'password' => Hash::make('password'), 'role' => 'notaire', 'is_active' => true, 'indice_confiance' => 95, 'email_verified_at' => now()]);
        User::create(['nom' => 'Tchibozo', 'prenom' => 'Cédric', 'email' => 'cedric.tchibozo@email.bj', 'telephone' => '+22901000011', 'password' => Hash::make('password'), 'role' => 'geometre', 'is_active' => true, 'indice_confiance' => 60, 'email_verified_at' => now()]);
        User::create(['nom' => 'Djidjoho', 'prenom' => 'Hermann', 'email' => 'hermann.djidjoho@email.bj', 'telephone' => '+22901000013', 'password' => Hash::make('password'), 'role' => 'citoyen', 'is_active' => true, 'indice_confiance' => 45, 'email_verified_at' => now()]);
        User::create(['nom' => 'Assogba', 'prenom' => 'Irène', 'email' => 'irene.assogba@email.bj', 'telephone' => '+22901000014', 'password' => Hash::make('password'), 'role' => 'geometre', 'is_active' => true, 'indice_confiance' => 67, 'email_verified_at' => now()]);
        User::create(['nom' => 'Ligan', 'prenom' => 'Jules', 'email' => 'jules.ligan@email.bj', 'telephone' => '+22901000015', 'password' => Hash::make('password'), 'role' => 'notaire', 'is_active' => true, 'indice_confiance' => 85, 'email_verified_at' => now()]);
    }
}
