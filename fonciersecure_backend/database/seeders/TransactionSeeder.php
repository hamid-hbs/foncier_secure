<?php

namespace Database\Seeders;

use App\Models\ConfirmationRendezVous;
use App\Models\DossierActivite;
use App\Models\DossierDocument;
use App\Models\DossierIntervenant;
use App\Models\DossierMessage;
use App\Models\DossierTransaction;
use App\Models\InterventionGeometre;
use App\Models\Parcelle;
use App\Models\RendezVous;
use App\Models\ValidationDossier;
use App\Models\VerificationIdentite;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $d1 = DossierTransaction::create([
            'vendeur_id' => 4,
            'acheteur_id' => 8,
            'notaire_id' => 3,
            'parcelle_id' => 1,
            'titre' => 'Vente terrain Ganhi - Akakpo / Sossou',
            'statut' => 'cloture',
            'closed_at' => now()->subDays(15),
        ]);

        Parcelle::where('id', 1)->update(['statut' => 'vendue']);

        VerificationIdentite::create(['dossier_id' => $d1->id, 'user_id' => 4, 'document_verifie' => 'CNI_vendeur.pdf', 'statut' => 'verifie', 'verifie_par' => 3]);
        VerificationIdentite::create(['dossier_id' => $d1->id, 'user_id' => 8, 'document_verifie' => 'CNI_acheteur.pdf', 'statut' => 'verifie', 'verifie_par' => 3]);

        ValidationDossier::create(['dossier_id' => $d1->id, 'user_id' => 4, 'valide_le' => now()->subDays(30)]);
        ValidationDossier::create(['dossier_id' => $d1->id, 'user_id' => 8, 'valide_le' => now()->subDays(30)]);

        DossierIntervenant::create(['dossier_id' => $d1->id, 'user_id' => 3, 'role_dossier' => 'notaire', 'invite_par' => 4, 'accepted_at' => now()->subDays(30)]);
        DossierIntervenant::create(['dossier_id' => $d1->id, 'user_id' => 2, 'role_dossier' => 'geometre', 'invite_par' => 3, 'accepted_at' => now()->subDays(28)]);

        InterventionGeometre::create([
            'dossier_id' => $d1->id,
            'geometre_id' => 2,
            'mission' => 'Bornage et verification limites terrain Ganhi',
            'assigne_par' => 3,
            'statut' => 'rapport_recu',
            'avis' => 'favorable',
            'commentaire' => 'Limites conformes au TF.',
            'completed_at' => now()->subDays(20),
        ]);

        $rv1 = RendezVous::create(['dossier_id' => $d1->id, 'type' => 'signature', 'date_prevue' => now()->subDays(18), 'lieu' => 'Étude Me Dossou, Cotonou', 'statut' => 'effectue']);
        ConfirmationRendezVous::create(['rendez_vous_id' => $rv1->id, 'user_id' => 4, 'est_confirme' => true]);
        ConfirmationRendezVous::create(['rendez_vous_id' => $rv1->id, 'user_id' => 8, 'est_confirme' => true]);
        ConfirmationRendezVous::create(['rendez_vous_id' => $rv1->id, 'user_id' => 3, 'est_confirme' => true]);

        DossierDocument::create(['dossier_id' => $d1->id, 'uploaded_by' => 4, 'nom_fichier' => 'TF_12345_Ganhi.pdf', 'chemin_fichier' => 'dossiers/1/TF_12345_Ganhi.pdf', 'hash_sha256' => hash('sha256', 'd1_doc1')]);
        DossierDocument::create(['dossier_id' => $d1->id, 'uploaded_by' => 4, 'nom_fichier' => 'ADC_67890_Ganhi.pdf', 'chemin_fichier' => 'dossiers/1/ADC_67890_Ganhi.pdf', 'hash_sha256' => hash('sha256', 'd1_doc2')]);
        DossierDocument::create(['dossier_id' => $d1->id, 'uploaded_by' => 8, 'nom_fichier' => 'CNI_acheteur.pdf', 'chemin_fichier' => 'dossiers/1/CNI_acheteur.pdf', 'hash_sha256' => hash('sha256', 'd1_doc3')]);

        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Dossier ouvert par le notaire']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 4, 'action' => 'Vendeur a valide le dossier']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 8, 'action' => 'Acheteur a valide le dossier']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Dossier confirmé par les deux parties']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Géomètre assigné : Patrick Hounsou']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Identités vérifiées']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Dossier validé']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Rendez-vous signature planifié']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Acte signé']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Mutation effectuée']);
        DossierActivite::create(['dossier_id' => $d1->id, 'user_id' => 3, 'action' => 'Dossier clôturé']);

        DossierMessage::create(['dossier_id' => $d1->id, 'sender_id' => 4, 'contenu' => 'Bonjour à tous, je confirme la vente de mon terrain à Ganhi.']);
        DossierMessage::create(['dossier_id' => $d1->id, 'sender_id' => 8, 'contenu' => 'Merci Jean. J\'ai hâte de finaliser cette acquisition.']);
        DossierMessage::create(['dossier_id' => $d1->id, 'sender_id' => 3, 'contenu' => 'J\'ai examiné les documents. Tout est en ordre.']);
        DossierMessage::create(['dossier_id' => $d1->id, 'sender_id' => 2, 'contenu' => 'Le bornage est fait. Rapport disponible.']);

        $d2 = DossierTransaction::create([
            'vendeur_id' => 5,
            'acheteur_id' => 6,
            'notaire_id' => 9,
            'parcelle_id' => 4,
            'titre' => 'Vente terrain Zogbadjè - Hounkpatin / Koffi',
            'statut' => 'valide',
        ]);

        Parcelle::where('id', 4)->update(['statut' => 'en_transaction']);

        VerificationIdentite::create(['dossier_id' => $d2->id, 'user_id' => 5, 'document_verifie' => 'CNI_vendeur.pdf', 'statut' => 'verifie', 'verifie_par' => 9]);
        VerificationIdentite::create(['dossier_id' => $d2->id, 'user_id' => 6, 'document_verifie' => 'CNI_acheteur.pdf', 'statut' => 'verifie', 'verifie_par' => 9]);

        ValidationDossier::create(['dossier_id' => $d2->id, 'user_id' => 5, 'valide_le' => now()->subDays(10)]);
        ValidationDossier::create(['dossier_id' => $d2->id, 'user_id' => 6, 'valide_le' => now()->subDays(10)]);

        DossierIntervenant::create(['dossier_id' => $d2->id, 'user_id' => 9, 'role_dossier' => 'notaire', 'invite_par' => 5, 'accepted_at' => now()->subDays(5)]);
        DossierIntervenant::create(['dossier_id' => $d2->id, 'user_id' => 10, 'role_dossier' => 'geometre', 'invite_par' => 9, 'accepted_at' => now()->subDays(4)]);

        InterventionGeometre::create([
            'dossier_id' => $d2->id,
            'geometre_id' => 10,
            'mission' => 'Levé topographique terrain Zogbadjè',
            'assigne_par' => 9,
            'statut' => 'rapport_recu',
            'avis' => 'favorable',
            'commentaire' => 'Parcelle conforme.',
            'completed_at' => now()->subDays(2),
        ]);

        DossierDocument::create(['dossier_id' => $d2->id, 'uploaded_by' => 5, 'nom_fichier' => 'TF_54321_Zogbadjè.pdf', 'chemin_fichier' => 'dossiers/2/TF_54321_Zogbadjè.pdf', 'hash_sha256' => hash('sha256', 'd2_doc1')]);

        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 9, 'action' => 'Dossier ouvert par le notaire']);
        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 5, 'action' => 'Vendeur a valide le dossier']);
        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 6, 'action' => 'Acheteur a valide le dossier']);
        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 9, 'action' => 'Dossier confirmé par les deux parties']);
        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 9, 'action' => 'Géomètre assigné : Cédric Tchibozo']);
        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 9, 'action' => 'Identités vérifiées']);
        DossierActivite::create(['dossier_id' => $d2->id, 'user_id' => 9, 'action' => 'Dossier validé']);

        DossierMessage::create(['dossier_id' => $d2->id, 'sender_id' => 5, 'contenu' => 'Bienvenue sur ce dossier de vente.']);
        DossierMessage::create(['dossier_id' => $d2->id, 'sender_id' => 9, 'contenu' => 'Les vérifications sont terminées, dossier validé.']);

        $d3 = DossierTransaction::create([
            'vendeur_id' => 8,
            'acheteur_id' => 7,
            'notaire_id' => 3,
            'parcelle_id' => 2,
            'titre' => 'Vente parcelle Gbégamey - Sossou / Bio',
            'statut' => 'en_attente',
        ]);

        DossierActivite::create(['dossier_id' => $d3->id, 'user_id' => 3, 'action' => 'Dossier ouvert par le notaire, en attente de validation des parties']);

        $d4 = DossierTransaction::create([
            'vendeur_id' => 11,
            'acheteur_id' => 7,
            'notaire_id' => 13,
            'parcelle_id' => 3,
            'titre' => 'Vente terrain Parakou - Djidjoho / Bio',
            'statut' => 'rendezvous_planifie',
        ]);

        Parcelle::where('id', 3)->update(['statut' => 'en_transaction']);

        VerificationIdentite::create(['dossier_id' => $d4->id, 'user_id' => 11, 'document_verifie' => 'CNI_vendeur.pdf', 'statut' => 'verifie', 'verifie_par' => 13]);
        VerificationIdentite::create(['dossier_id' => $d4->id, 'user_id' => 7, 'document_verifie' => 'CNI_acheteur.pdf', 'statut' => 'verifie', 'verifie_par' => 13]);

        ValidationDossier::create(['dossier_id' => $d4->id, 'user_id' => 11, 'valide_le' => now()->subDays(12)]);
        ValidationDossier::create(['dossier_id' => $d4->id, 'user_id' => 7, 'valide_le' => now()->subDays(12)]);

        DossierIntervenant::create(['dossier_id' => $d4->id, 'user_id' => 13, 'role_dossier' => 'notaire', 'invite_par' => 11, 'accepted_at' => now()->subDays(10)]);
        DossierIntervenant::create(['dossier_id' => $d4->id, 'user_id' => 12, 'role_dossier' => 'geometre', 'invite_par' => 13, 'accepted_at' => now()->subDays(8)]);

        InterventionGeometre::create([
            'dossier_id' => $d4->id,
            'geometre_id' => 12,
            'mission' => 'Levé topographique et bornage terrain Parakou',
            'assigne_par' => 13,
            'statut' => 'rapport_recu',
            'avis' => 'favorable',
            'commentaire' => 'Parcelle bien délimitée.',
            'completed_at' => now()->subDays(5),
        ]);

        $rv4 = RendezVous::create(['dossier_id' => $d4->id, 'type' => 'signature', 'date_prevue' => now()->addDays(7), 'lieu' => 'Étude Me Ligan, Parakou', 'statut' => 'confirme']);
        ConfirmationRendezVous::create(['rendez_vous_id' => $rv4->id, 'user_id' => 11, 'est_confirme' => true]);
        ConfirmationRendezVous::create(['rendez_vous_id' => $rv4->id, 'user_id' => 7, 'est_confirme' => true]);
        ConfirmationRendezVous::create(['rendez_vous_id' => $rv4->id, 'user_id' => 13, 'est_confirme' => true]);

        DossierDocument::create(['dossier_id' => $d4->id, 'uploaded_by' => 11, 'nom_fichier' => 'TF_Parakou_1122.pdf', 'chemin_fichier' => 'dossiers/4/TF_Parakou_1122.pdf', 'hash_sha256' => hash('sha256', 'd4_doc1')]);
        DossierDocument::create(['dossier_id' => $d4->id, 'uploaded_by' => 11, 'nom_fichier' => 'ADC_Parakou_3344.pdf', 'chemin_fichier' => 'dossiers/4/ADC_Parakou_3344.pdf', 'hash_sha256' => hash('sha256', 'd4_doc2')]);
        DossierDocument::create(['dossier_id' => $d4->id, 'uploaded_by' => 12, 'nom_fichier' => 'Rapport_technique_parakou.pdf', 'chemin_fichier' => 'dossiers/4/Rapport_technique_parakou.pdf', 'hash_sha256' => hash('sha256', 'd4_doc3')]);

        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 13, 'action' => 'Dossier ouvert par le notaire']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 11, 'action' => 'Vendeur a valide le dossier']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 7, 'action' => 'Acheteur a valide le dossier']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 13, 'action' => 'Dossier confirmé par les deux parties']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 13, 'action' => 'Géomètre assigné : Irène Assogba']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 13, 'action' => 'Identités vérifiées']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 13, 'action' => 'Dossier validé']);
        DossierActivite::create(['dossier_id' => $d4->id, 'user_id' => 13, 'action' => 'Rendez-vous signature planifié']);

        DossierMessage::create(['dossier_id' => $d4->id, 'sender_id' => 11, 'contenu' => 'Bienvenue à toutes et à tous sur ce dossier.']);
        DossierMessage::create(['dossier_id' => $d4->id, 'sender_id' => 12, 'contenu' => 'Le levé topographique est terminé. Avis favorable.']);
        DossierMessage::create(['dossier_id' => $d4->id, 'sender_id' => 13, 'contenu' => 'Je vais examiner les documents cette semaine.']);
        DossierMessage::create(['dossier_id' => $d4->id, 'sender_id' => 7, 'contenu' => 'Merci à tous pour votre travail.']);
    }
}
