<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        SupportTicket::create([
            'user_id' => 4,
            'sujet' => 'Problème de connexion',
            'message' => 'Je n\'arrive pas à me connecter à mon compte depuis hier.',
            'priorite' => 'haute',
            'statut' => 'resolu',
            'assigned_to' => 1,
            'reponse' => 'Le problème a été résolu. Veuillez réinitialiser votre mot de passe.',
            'closed_at' => now(),
        ]);

        SupportTicket::create([
            'user_id' => 5,
            'sujet' => 'Demande d\'information sur une parcelle',
            'message' => 'Je souhaite obtenir plus d\'informations sur la parcelle FS-00002.',
            'priorite' => 'normale',
        ]);

        SupportTicket::create([
            'user_id' => 6,
            'sujet' => 'Erreur lors du dépôt d\'un document',
            'message' => 'Le fichier PDF que j\'ai essayé de télécharger refuse de s\'uploader.',
            'priorite' => 'urgente',
            'statut' => 'en_cours',
            'assigned_to' => 1,
        ]);
    }
}
