<?php

namespace Database\Seeders;

use App\Models\AnalyseAutomatique;
use App\Models\DocumentVerification;
use App\Models\InterventionGeometre;
use App\Models\Verification;
use Illuminate\Database\Seeder;

class VerificationSeeder extends Seeder
{
    public function run(): void
    {
        $v1 = Verification::create([
            'parcelle_id' => 1,
            'demandeur_id' => 4,
            'titre' => 'Vérification terrain Ganhi',
            'statut' => 'terminee',
            'score_risque' => 85,
            'niveau_risque' => 'faible',
            'rapport_path' => 'rapports/verification_1.pdf',
        ]);

        DocumentVerification::create([
            'verification_id' => $v1->id,
            'type_document' => 'tf',
            'nom_fichier' => 'TF_12345_Ganhi.pdf',
            'chemin_fichier' => 'verifications/1/TF_12345_Ganhi.pdf',
            'hash_sha256' => hash('sha256', 'contenu_fictif_tf_1'),
            'taille' => 204800,
            'uploaded_at' => now(),
        ]);

        DocumentVerification::create([
            'verification_id' => $v1->id,
            'type_document' => 'adc',
            'nom_fichier' => 'ADC_67890_Ganhi.pdf',
            'chemin_fichier' => 'verifications/1/ADC_67890_Ganhi.pdf',
            'hash_sha256' => hash('sha256', 'contenu_fictif_adc_1'),
            'taille' => 153600,
            'uploaded_at' => now(),
        ]);

        AnalyseAutomatique::create(['verification_id' => $v1->id, 'type_analyse' => 'coherence', 'resultat' => 'conforme', 'details' => json_encode(['incoherences' => [], 'documents_verifies' => 3])]);
        AnalyseAutomatique::create(['verification_id' => $v1->id, 'type_analyse' => 'doublon', 'resultat' => 'conforme', 'details' => json_encode(['doublons' => [], 'total_documents' => 3])]);
        AnalyseAutomatique::create(['verification_id' => $v1->id, 'type_analyse' => 'gps', 'resultat' => 'conforme', 'details' => json_encode(['gps_present' => true, 'latitude' => 6.3589, 'longitude' => 2.4256])]);
        AnalyseAutomatique::create(['verification_id' => $v1->id, 'type_analyse' => 'antecedent', 'resultat' => 'conforme', 'details' => json_encode(['anomalies_trouvees' => 0])]);

        $v2 = Verification::create([
            'parcelle_id' => 4,
            'demandeur_id' => 5,
            'titre' => 'Vérification terrain Zogbadjè',
            'statut' => 'terminee',
            'score_risque' => 60,
            'niveau_risque' => 'moyen',
            'rapport_path' => 'rapports/verification_2.pdf',
        ]);

        DocumentVerification::create([
            'verification_id' => $v2->id,
            'type_document' => 'tf',
            'nom_fichier' => 'TF_54321_Zogbadjè.pdf',
            'chemin_fichier' => 'verifications/2/TF_54321_Zogbadjè.pdf',
            'hash_sha256' => hash('sha256', 'contenu_fictif_tf_2'),
            'taille' => 180000,
            'uploaded_at' => now(),
        ]);

        AnalyseAutomatique::create(['verification_id' => $v2->id, 'type_analyse' => 'coherence', 'resultat' => 'alerte', 'details' => json_encode(['incoherences' => ['ADC manquante'], 'documents_verifies' => 1])]);
        AnalyseAutomatique::create(['verification_id' => $v2->id, 'type_analyse' => 'doublon', 'resultat' => 'conforme', 'details' => json_encode(['doublons' => [], 'total_documents' => 1])]);
        AnalyseAutomatique::create(['verification_id' => $v2->id, 'type_analyse' => 'gps', 'resultat' => 'conforme', 'details' => json_encode(['gps_present' => true, 'latitude' => 6.4428, 'longitude' => 2.3500])]);
        AnalyseAutomatique::create(['verification_id' => $v2->id, 'type_analyse' => 'antecedent', 'resultat' => 'alerte', 'details' => json_encode(['anomalies_trouvees' => 1])]);

        $v3 = Verification::create([
            'parcelle_id' => 2,
            'demandeur_id' => 6,
            'titre' => 'Vérification parcelle Akpakpa',
            'statut' => 'terminee',
            'score_risque' => 35,
            'niveau_risque' => 'eleve',
            'rapport_path' => 'rapports/verification_3.pdf',
        ]);

        AnalyseAutomatique::create(['verification_id' => $v3->id, 'type_analyse' => 'coherence', 'resultat' => 'non_conforme', 'details' => json_encode(['incoherences' => ['Le nom sur le TF ne correspond pas à celui sur l\'ADC'], 'documents_verifies' => 2])]);
        AnalyseAutomatique::create(['verification_id' => $v3->id, 'type_analyse' => 'doublon', 'resultat' => 'non_conforme', 'details' => json_encode(['doublons' => ['TF_98765_Akpakpa.pdf'], 'total_documents' => 2])]);
        AnalyseAutomatique::create(['verification_id' => $v3->id, 'type_analyse' => 'gps', 'resultat' => 'conforme', 'details' => json_encode(['gps_present' => true, 'latitude' => 6.3700, 'longitude' => 2.4400])]);
        AnalyseAutomatique::create(['verification_id' => $v3->id, 'type_analyse' => 'antecedent', 'resultat' => 'non_conforme', 'details' => json_encode(['anomalies_trouvees' => 2])]);

        $v4 = Verification::create([
            'parcelle_id' => 3,
            'demandeur_id' => 8,
            'titre' => 'Vérification terrain Calavi',
            'statut' => 'soumise',
        ]);

        $v5 = Verification::create([
            'parcelle_id' => 4,
            'demandeur_id' => 7,
            'titre' => 'Vérification terrain litigieux Calavi',
            'statut' => 'terminee',
            'score_risque' => 20,
            'niveau_risque' => 'eleve',
            'rapport_path' => 'rapports/verification_5.pdf',
        ]);

        AnalyseAutomatique::create(['verification_id' => $v5->id, 'type_analyse' => 'coherence', 'resultat' => 'non_conforme', 'details' => json_encode(['incoherences' => ['Aucun document fourni'], 'documents_verifies' => 0])]);
        AnalyseAutomatique::create(['verification_id' => $v5->id, 'type_analyse' => 'doublon', 'resultat' => 'non_conforme', 'details' => json_encode(['doublons' => ['TF_54321_Zogbadjè.pdf'], 'total_documents' => 0])]);
        AnalyseAutomatique::create(['verification_id' => $v5->id, 'type_analyse' => 'gps', 'resultat' => 'conforme', 'details' => json_encode(['gps_present' => true, 'latitude' => 6.4500, 'longitude' => 2.3400])]);
        AnalyseAutomatique::create(['verification_id' => $v5->id, 'type_analyse' => 'antecedent', 'resultat' => 'non_conforme', 'details' => json_encode(['anomalies_trouvees' => 3])]);
    }
}
