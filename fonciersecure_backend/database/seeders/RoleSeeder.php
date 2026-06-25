<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['nom' => 'citoyen', 'description' => 'Utilisateur de base']);
        Role::create(['nom' => 'geometre', 'description' => 'Professionnel géomètre']);
        Role::create(['nom' => 'notaire', 'description' => 'Professionnel notaire']);
        Role::create(['nom' => 'admin', 'description' => 'Administrateur système']);
    }
}
