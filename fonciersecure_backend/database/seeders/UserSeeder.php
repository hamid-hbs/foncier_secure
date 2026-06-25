<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nom' => 'Admin', 'prenom' => 'Super',
            'email' => 'admin@fonciersecure.bj', 'telephone' => '+22901000001',
            'password_hash' => Hash::make('password'),
            'role_id' => 4, 'is_active' => true, 'indice_confiance' => 100,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Houndéton', 'prenom' => 'Marcel',
            'email' => 'marcel@email.bj', 'telephone' => '+22901000002',
            'password_hash' => Hash::make('password'),
            'role_id' => 1, 'is_active' => true, 'indice_confiance' => 70,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Adjovi', 'prenom' => 'Sébastien',
            'email' => 'sebastien@email.bj', 'telephone' => '+22901000003',
            'password_hash' => Hash::make('password'),
            'role_id' => 1, 'is_active' => true, 'indice_confiance' => 45,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Bocco', 'prenom' => 'Cédric',
            'email' => 'cedric@email.bj', 'telephone' => '+22901000004',
            'password_hash' => Hash::make('password'),
            'role_id' => 1, 'is_active' => true, 'indice_confiance' => 30,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Gbaguidi', 'prenom' => 'Jean',
            'email' => 'jean.geometre@email.bj', 'telephone' => '+22901000005',
            'password_hash' => Hash::make('password'),
            'role_id' => 2, 'is_active' => true, 'indice_confiance' => 80,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Da Silva', 'prenom' => 'Paul',
            'email' => 'paul.notaire@email.bj', 'telephone' => '+22901000006',
            'password_hash' => Hash::make('password'),
            'role_id' => 3, 'is_active' => true, 'indice_confiance' => 90,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Koumassi', 'prenom' => 'Eunice',
            'email' => 'eunice@email.bj', 'telephone' => '+22901000007',
            'password_hash' => Hash::make('password'),
            'role_id' => 1, 'is_active' => true, 'indice_confiance' => 20,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Tokoudagba', 'prenom' => 'Emmanuel',
            'email' => 'emmanuel.geometre@email.bj', 'telephone' => '+22901000008',
            'password_hash' => Hash::make('password'),
            'role_id' => 2, 'is_active' => true, 'indice_confiance' => 65,
            'email_verified_at' => now(),
        ]);

        User::create([
            'nom' => 'Hounsou', 'prenom' => 'Martine',
            'email' => 'martine.notaire@email.bj', 'telephone' => '+22901000009',
            'password_hash' => Hash::make('password'),
            'role_id' => 3, 'is_active' => true, 'indice_confiance' => 95,
            'email_verified_at' => now(),
        ]);
    }
}
