<?php

namespace App\Services;

use App\Models\AnalyseAutomatique;
use App\Models\DocumentVerification;
use App\Models\Professionnel;
use App\Models\Verification;

class AnalyseDocumentaireService
{
    public function analyser(Verification $verification): array
    {
        $resultats = [];

        $resultats[] = $this->analyseCoherence($verification);
        $resultats[] = $this->analyseDoublon($verification);
        $resultats[] = $this->analyseGPS($verification);
        $resultats[] = $this->analyseValiditeProfessionnels($verification);

        return $resultats;
    }

    private function analyseCoherence(Verification $verification): AnalyseAutomatique
    {
        $documents = $verification->documents;
        $incoherences = [];

        $tfNames = $documents->where('type_document', 'tf')->pluck('nom_fichier');
        $adcNames = $documents->where('type_document', 'adc')->pluck('nom_fichier');

        if ($tfNames->isNotEmpty() && $adcNames->isNotEmpty()) {
            $tfBase = $tfNames->first();
            $adcBase = $adcNames->first();
            if (!str_contains($tfBase, explode('.', $adcBase)[0] ?? '')
                && !str_contains($adcBase, explode('.', $tfBase)[0] ?? '')) {
                $incoherences[] = 'Les noms sur le TF et l\'ADC ne correspondent pas';
            }
        }

        $resultat = empty($incoherences) ? 'conforme' : 'non_conforme';

        return AnalyseAutomatique::create([
            'verification_id' => $verification->id,
            'type_analyse' => 'coherence',
            'resultat' => $resultat,
            'details' => ['incoherences' => $incoherences, 'documents_verifies' => $documents->count()],
        ]);
    }

    private function analyseDoublon(Verification $verification): AnalyseAutomatique
    {
        $documents = $verification->documents;
        $doublons = [];

        foreach ($documents as $doc) {
            $exists = DocumentVerification::where('hash_sha256', $doc->hash_sha256)
                ->where('verification_id', '!=', $verification->id)
                ->exists();

            if ($exists) {
                $doublons[] = $doc->nom_fichier;
            }
        }

        $resultat = empty($doublons) ? 'conforme' : 'non_conforme';

        return AnalyseAutomatique::create([
            'verification_id' => $verification->id,
            'type_analyse' => 'doublon',
            'resultat' => $resultat,
            'details' => ['doublons' => $doublons, 'total_documents' => $documents->count()],
        ]);
    }

    private function analyseGPS(Verification $verification): AnalyseAutomatique
    {
        $parcelle = $verification->parcelle;
        if ($parcelle && $parcelle->latitude && $parcelle->longitude) {
            $resultat = 'conforme';
            $details = ['gps_present' => true, 'latitude' => $parcelle->latitude, 'longitude' => $parcelle->longitude];
        } else {
            $resultat = 'alerte';
            $details = ['gps_present' => false, 'message' => 'Coordonnées GPS manquantes sur la parcelle'];
        }

        return AnalyseAutomatique::create([
            'verification_id' => $verification->id,
            'type_analyse' => 'gps',
            'resultat' => $resultat,
            'details' => $details,
        ]);
    }

    private function analyseValiditeProfessionnels(Verification $verification): AnalyseAutomatique
    {
        $problemes = [];

        $intervention = $verification->intervention;
        if ($intervention && $intervention->geometre_id) {
            $pro = Professionnel::where('user_id', $intervention->geometre_id)
                ->where('type', 'geometre')
                ->first();
            if (!$pro) {
                $problemes[] = 'Le géomètre intervenant n\'est pas inscrit au registre des professionnels';
            }
        }

        $resultat = empty($problemes) ? 'conforme' : 'alerte';

        return AnalyseAutomatique::create([
            'verification_id' => $verification->id,
            'type_analyse' => 'validite_professionnels',
            'resultat' => $resultat,
            'details' => ['problemes' => $problemes, 'professionnels_verifies' => $intervention ? 1 : 0],
        ]);
    }

}
