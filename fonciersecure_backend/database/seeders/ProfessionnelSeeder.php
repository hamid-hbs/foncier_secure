<?php

namespace Database\Seeders;

use App\Models\AvisProfessionnel;
use App\Models\Professionnel;
use Illuminate\Database\Seeder;

class ProfessionnelSeeder extends Seeder
{
    public function run(): void
    {
        $geometre1 = Professionnel::create([
            'user_id' => 2,
            'type' => 'geometre',
            'cabinet' => 'Cabinet Topo-Bénin SARL',
            'zone_intervention' => 'Littoral, Atlantique, Ouémé',
            'specialites' => ['Bornage', 'levé topographique', 'plan parcellaire', 'morcellement'],
            'note_moyenne' => 3.5,
        ]);

        $notaire1 = Professionnel::create([
            'user_id' => 3,
            'type' => 'notaire',
            'cabinet' => 'Étude Me Martine Dossou',
            'zone_intervention' => 'Cotonou, Littoral',
            'specialites' => ['Droit foncier', 'successions', 'transactions immobilières', 'droit de la famille'],
            'note_moyenne' => 4.5,
        ]);

        $notaire2 = Professionnel::create([
            'user_id' => 9,
            'type' => 'notaire',
            'cabinet' => 'Étude Me Bénédicte Gbaguidi',
            'zone_intervention' => 'Cotonou, Abomey-Calavi, Porto-Novo',
            'specialites' => ['Foncier', 'immobilier', 'contrats'],
            'note_moyenne' => 4.8,
        ]);

        $geometre2 = Professionnel::create([
            'user_id' => 10,
            'type' => 'geometre',
            'cabinet' => 'Géo-Plan Services',
            'zone_intervention' => 'Abomey-Calavi, Allada, Ouidah',
            'specialites' => ['Géomètre expert', 'topographie', 'SIG', 'bornage judiciaire'],
            'note_moyenne' => 3.0,
        ]);

        $geometre3 = Professionnel::create([
            'user_id' => 12,
            'type' => 'geometre',
            'cabinet' => 'Topo-Expert Bénin',
            'zone_intervention' => 'Parakou, Borgou, Alibori',
            'specialites' => ['Géomètre topographe', 'lotissement', 'bornage'],
            'note_moyenne' => 3.2,
        ]);

        $notaire3 = Professionnel::create([
            'user_id' => 13,
            'type' => 'notaire',
            'cabinet' => 'Étude Me Jules Ligan',
            'zone_intervention' => 'Parakou, Borgou',
            'specialites' => ['Droit foncier', 'notariat', 'conseil juridique'],
            'note_moyenne' => 4.2,
        ]);

        AvisProfessionnel::create(['professionnel_id' => $geometre1->id, 'auteur_id' => 4, 'note' => 4, 'commentaire' => 'Bon travail sur le bornage']);
        AvisProfessionnel::create(['professionnel_id' => $geometre1->id, 'auteur_id' => 5, 'note' => 3, 'commentaire' => 'Rapport rendu avec retard']);
        AvisProfessionnel::create(['professionnel_id' => $geometre1->id, 'auteur_id' => 11, 'note' => 4, 'commentaire' => 'Plan topographique de qualité']);

        AvisProfessionnel::create(['professionnel_id' => $notaire1->id, 'auteur_id' => 4, 'note' => 5, 'commentaire' => 'Me Dossou est la meilleure notaire de Cotonou']);
        AvisProfessionnel::create(['professionnel_id' => $notaire1->id, 'auteur_id' => 8, 'note' => 4, 'commentaire' => 'Très compétente en droit foncier']);

        AvisProfessionnel::create(['professionnel_id' => $notaire2->id, 'auteur_id' => 4, 'note' => 5, 'commentaire' => 'Disponible et à l\'écoute']);
        AvisProfessionnel::create(['professionnel_id' => $notaire2->id, 'auteur_id' => 5, 'note' => 5, 'commentaire' => 'Je recommande vivement']);
        AvisProfessionnel::create(['professionnel_id' => $notaire2->id, 'auteur_id' => 8, 'note' => 4, 'commentaire' => 'Très bon conseil juridique']);

        AvisProfessionnel::create(['professionnel_id' => $geometre2->id, 'auteur_id' => 7, 'note' => 2, 'commentaire' => 'Travail approximatif sur le bornage']);
        AvisProfessionnel::create(['professionnel_id' => $geometre2->id, 'auteur_id' => 11, 'note' => 4, 'commentaire' => 'Bon professionnel']);

        AvisProfessionnel::create(['professionnel_id' => $notaire3->id, 'auteur_id' => 7, 'note' => 5, 'commentaire' => 'Excellent notaire à Parakou']);
    }
}
