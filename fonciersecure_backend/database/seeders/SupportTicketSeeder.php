<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        SupportTicket::create([
            'user_id' => 2,
            'sujet' => 'Problème de téléchargement document',
            'message' => 'Je n\'arrive pas à télécharger le plan topographique de ma parcelle.',
            'priorite' => 'normale',
            'statut' => 'resolu',
            'assigned_to_id' => 1,
            'reponse' => 'Le problème venait du format du fichier. Je vous ai renvoyé le lien par email.',
            'resolved_at' => now()->subDays(3),
        ]);

        SupportTicket::create([
            'user_id' => 3,
            'sujet' => 'Demande d\'information sur le statut',
            'message' => 'Ma transaction est en attente depuis une semaine. Qui dois-je contacter ?',
            'priorite' => 'haute',
            'statut' => 'en_cours',
            'assigned_to_id' => 1,
        ]);
    }
}
