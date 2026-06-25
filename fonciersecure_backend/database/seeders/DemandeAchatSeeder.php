<?php

namespace Database\Seeders;

use App\Models\DemandeAchat;
use Illuminate\Database\Seeder;

class DemandeAchatSeeder extends Seeder
{
    public function run(): void
    {
        DemandeAchat::create([
            'parcelle_id' => 3,
            'acheteur_id' => 2,
            'vendeur_id' => 4,
            'message_acheteur' => 'Bonjour, je suis intéressé par votre terrain à Fidjrossè. Souhaitez-vous le vendre ?',
            'statut' => 'soumise',
            'date_soumise' => now()->subDays(5),
        ]);

        DemandeAchat::create([
            'parcelle_id' => 5,
            'acheteur_id' => 3,
            'vendeur_id' => 7,
            'message_acheteur' => 'Je souhaite acquérir votre parcelle à Ouando.',
            'statut' => 'acceptee',
            'code_secret' => 'AC-2B4F9A',
            'date_soumise' => now()->subDays(10),
            'date_acceptee' => now()->subDays(3),
        ]);

        DemandeAchat::create([
            'parcelle_id' => 8,
            'acheteur_id' => 2,
            'vendeur_id' => 3,
            'message_acheteur' => 'Intéressé par votre terrain à Kpébié.',
            'statut' => 'notaire_sollicite',
            'code_secret' => 'AC-7D3E11',
            'notaire_id' => 9,
            'date_soumise' => now()->subDays(7),
        ]);

        DemandeAchat::create([
            'parcelle_id' => 1,
            'acheteur_id' => 7,
            'vendeur_id' => 2,
            'statut' => 'refusee',
            'date_soumise' => now()->subDays(15),
            'date_refusee' => now()->subDays(10),
            'raison_refus' => 'Le vendeur a trouvé un autre acheteur.',
        ]);
    }
}
