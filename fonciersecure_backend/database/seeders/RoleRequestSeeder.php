<?php

namespace Database\Seeders;

use App\Models\RoleRequest;
use Illuminate\Database\Seeder;

class RoleRequestSeeder extends Seeder
{
    public function run(): void
    {
        RoleRequest::create([
            'user_id' => 11,
            'role_demande' => 'geometre',
            'statut' => 'en_attente',
            'document_justificatif' => 'role-requests/diplome_geometre_djidjoho.pdf',
        ]);

        RoleRequest::create([
            'user_id' => 5,
            'role_demande' => 'notaire',
            'statut' => 'en_attente',
            'document_justificatif' => 'role-requests/certificat_notaire_hounkpatin.pdf',
        ]);

        RoleRequest::create([
            'user_id' => 6,
            'role_demande' => 'geometre',
            'statut' => 'rejete',
            'document_justificatif' => 'role-requests/diplome_koffi.pdf',
            'valide_par' => 1,
        ]);
    }
}
