<?php

namespace Database\Seeders;

use App\Models\DemandeAchat;
use App\Models\DocumentDemande;
use App\Models\Parcelle;
use Illuminate\Database\Seeder;

class DemandeAchatSeeder extends Seeder
{
    public function run(): void
    {
        $da1 = DemandeAchat::create([
            'parcelle_id' => 1,
            'acheteur_id' => 8,
            'message' => 'Bonjour, je suis interessé par votre terrain à Ganhi. Pouvons-nous discuter du prix ?',
            'statut' => 'acceptee',
            'notaire_id' => 3,
        ]);

        Parcelle::where('id', 1)->update(['statut' => 'en_demande']);

        DocumentDemande::create([
            'demande_id' => $da1->id,
            'type_document' => 'autre',
            'nom_fichier' => 'offre_achat_ganhi.pdf',
            'chemin_fichier' => 'demandes/1/offre_achat_ganhi.pdf',
            'hash_sha256' => hash('sha256', 'offre_1'),
            'taille' => 102400,
            'uploaded_at' => now(),
        ]);

        $da2 = DemandeAchat::create([
            'parcelle_id' => 4,
            'acheteur_id' => 6,
            'message' => 'Je souhaite acquerir votre terrain à Zogbadjè. Voici mon offre.',
            'statut' => 'acceptee',
            'notaire_id' => 9,
        ]);

        Parcelle::where('id', 4)->update(['statut' => 'en_demande']);

        $da3 = DemandeAchat::create([
            'parcelle_id' => 2,
            'acheteur_id' => 8,
            'message' => 'Je suis interessé par la parcelle à Gbégamey.',
            'statut' => 'acceptee',
            'notaire_id' => 3,
        ]);

        Parcelle::where('id', 2)->update(['statut' => 'en_demande']);

        $da4 = DemandeAchat::create([
            'parcelle_id' => 3,
            'acheteur_id' => 7,
            'message' => 'Offre d\'achat pour le terrain familial à Calavi.',
            'statut' => 'acceptee',
            'notaire_id' => 13,
        ]);

        Parcelle::where('id', 3)->update(['statut' => 'en_demande']);

        $da5 = DemandeAchat::create([
            'parcelle_id' => 1,
            'acheteur_id' => 6,
            'message' => 'Je propose 12 millions pour le terrain Ganhi.',
            'statut' => 'refusee',
        ]);

        DocumentDemande::create([
            'demande_id' => $da5->id,
            'type_document' => 'autre',
            'nom_fichier' => 'contre_offre_ganhi.pdf',
            'chemin_fichier' => 'demandes/5/contre_offre_ganhi.pdf',
            'hash_sha256' => hash('sha256', 'offre_5'),
            'taille' => 51200,
            'uploaded_at' => now(),
        ]);
    }
}
