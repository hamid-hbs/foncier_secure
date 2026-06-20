<?php

namespace App\Services;

use App\Models\DossierTransaction;
use App\Models\Verification;

class RisqueService
{
    public function calculer(Verification $verification): array
    {
        $score = 50;

        $documents = $verification->documents;
        $analyses = $verification->analyses;
        $intervention = $verification->intervention;

        $hasTF = $documents->where('type_document', 'tf')->isNotEmpty();
        $hasADC = $documents->where('type_document', 'adc')->isNotEmpty();
        $hasPlanTopo = $documents->where('type_document', 'plan_topo')->isNotEmpty();
        $hasGPS = $verification->latitude && $verification->longitude;

        $coherenceOk = $analyses->where('type_analyse', 'coherence')->first()?->resultat === 'conforme';
        $doublonOk = $analyses->where('type_analyse', 'doublon')->first()?->resultat === 'conforme';
        $antecedentOk = $analyses->where('type_analyse', 'antecedent')->first()?->resultat === 'conforme';

        $geometreFavorable = $intervention && $intervention->avis === 'favorable';

        $hasNotaireAssocie = DossierTransaction::where(function ($q) use ($verification) {
            $q->where('vendeur_id', $verification->demandeur_id)
                ->orWhere('acheteur_id', $verification->demandeur_id);
        })->whereHas('intervenants', fn($q) => $q->where('role_dossier', 'notaire'))->exists();

        $vendeur = $verification->demandeur;

        if ($hasTF) $score += 20;
        if ($hasADC) $score += 10;
        if ($hasPlanTopo) $score += 10;
        if ($geometreFavorable) $score += 15;
        if ($hasNotaireAssocie) $score += 15;
        if ($hasGPS) $score += 10;
        if ($vendeur && $vendeur->indice_confiance >= 50) $score += 10;

        if (!$hasTF) $score -= 20;
        if (!$hasADC) $score -= 10;
        if (!$coherenceOk) $score -= 25;
        if (!$doublonOk) $score -= 30;
        if (!$antecedentOk) $score -= 30;
        if (!$hasGPS) $score -= 15;
        if ($vendeur && $vendeur->indice_confiance < 20) $score -= 20;

        $score = max(0, min(100, $score));

        $niveau = $score >= 70 ? 'faible' : ($score >= 40 ? 'moyen' : 'eleve');

        $verification->update([
            'score_risque' => $score,
            'niveau_risque' => $niveau,
            'statut' => 'terminee',
        ]);

        return [
            'score' => $score,
            'niveau' => $niveau,
        ];
    }
}
