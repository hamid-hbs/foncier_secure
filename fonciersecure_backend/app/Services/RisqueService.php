<?php

namespace App\Services;

use App\Models\DossierTransaction;
use App\Models\Professionnel;

class RisqueService
{
    public function calculer(DossierTransaction $dossier): array
    {
        $score = 50;
        $analyses = $dossier->analyses;
        $documents = $dossier->documents;

        $hasTF = $documents->where('type_document', 'tf')->isNotEmpty();
        $hasADC = $documents->where('type_document', 'adc')->isNotEmpty();
        $hasPlanTopo = $documents->where('type_document', 'plan_topo')->isNotEmpty();

        $coherenceOk = $analyses->where('type_analyse', 'coherence')->first()?->resultat === 'conforme';
        $doublonOk = $analyses->where('type_analyse', 'doublon')->first()?->resultat === 'conforme';
        $gpsOk = $analyses->where('type_analyse', 'gps')->first()?->resultat === 'conforme';
        $prosOk = $analyses->where('type_analyse', 'validite_professionnels')->first()?->resultat === 'conforme';

        $vendeur = $dossier->vendeur;
        $acheteur = $dossier->acheteur;
        $notaire = $dossier->notaire;

        if ($hasTF) $score += 20;
        if ($hasADC) $score += 10;
        if ($hasPlanTopo) $score += 10;
        if ($coherenceOk) $score += 15;
        if ($doublonOk) $score += 15;
        if ($gpsOk) $score += 10;
        if ($prosOk) $score += 10;
        if ($vendeur && $vendeur->indice_confiance >= 50) $score += 10;

        if ($vendeur && $vendeur->indice_confiance < 20) $score -= 20;
        if (!$hasTF) $score -= 20;

        $score = max(0, min(100, $score));

        $niveau = $score >= 70 ? 'faible' : ($score >= 40 ? 'moyen' : 'eleve');

        return [
            'score' => $score,
            'niveau' => $niveau,
            'details' => [
                'documents_complets' => $hasTF && $hasADC,
                'plan_topo_present' => $hasPlanTopo,
                'coherence_ok' => $coherenceOk,
                'doublon_ok' => $doublonOk,
                'gps_ok' => $gpsOk,
                'professionnels_valides' => $prosOk,
            ],
        ];
    }
}
