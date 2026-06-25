<?php

namespace App\Services;

use App\Models\Analyse;
use App\Models\Document;
use App\Models\DossierTransaction;
use App\Models\Parcelle;
use App\Models\Professionnel;
use Illuminate\Database\Eloquent\Model;

class AnalyseDocumentaireService
{
    public function analyser(Model $analysable, array $documentIds = []): array
    {
        $documents = Document::whereIn('id', $documentIds)->get();

        $resultats = [];

        if ($documents->isNotEmpty()) {
            $resultats[] = $this->analyserCoherence($analysable, $documents);
            $resultats[] = $this->analyserDoublon($analysable, $documents);
        }

        if ($analysable instanceof Parcelle || $analysable->parcelle ?? null) {
            $parcelle = $analysable instanceof Parcelle ? $analysable : $analysable->parcelle;
            $resultats[] = $this->analyserGPS($analysable, $parcelle);
        }

        if ($analysable instanceof DossierTransaction) {
            $resultats[] = $this->analyserValiditeProfessionnels($analysable, $documents);
        }

        return $resultats;
    }

    public function analyserCoherence(Model $analysable, $documents): Analyse
    {
        $incoherences = [];

        $tfDocs = $documents->where('type_document', 'tf');
        $adcDocs = $documents->where('type_document', 'adc');

        foreach ($tfDocs as $tf) {
            foreach ($adcDocs as $adc) {
                $tfBase = pathinfo($tf->nom_fichier, PATHINFO_FILENAME);
                $adcBase = pathinfo($adc->nom_fichier, PATHINFO_FILENAME);
                if (!str_contains($tfBase, $adcBase) && !str_contains($adcBase, $tfBase)) {
                    $incoherences[] = "Le TF ({$tf->nom_fichier}) et l'ADC ({$adc->nom_fichier}) ne correspondent pas";
                }
            }
        }

        $resultat = empty($incoherences) ? 'conforme' : 'non_conforme';

        return $analysable->analyses()->create([
            'type_analyse' => 'coherence',
            'resultat' => $resultat,
            'details' => ['incoherences' => $incoherences, 'documents_verifies' => $documents->count()],
        ]);
    }

    public function analyserDoublon(Model $analysable, $documents): Analyse
    {
        $doublons = [];

        foreach ($documents as $doc) {
            $exists = Document::where('hash_sha256', $doc->hash_sha256)
                ->where('id', '!=', $doc->id)
                ->exists();

            if ($exists) {
                $doublons[] = $doc->nom_fichier;
            }
        }

        $resultat = empty($doublons) ? 'conforme' : 'non_conforme';

        return $analysable->analyses()->create([
            'type_analyse' => 'doublon',
            'resultat' => $resultat,
            'details' => ['doublons' => $doublons, 'total_documents' => $documents->count()],
        ]);
    }

    public function analyserGPS(Model $analysable, Parcelle $parcelle): Analyse
    {
        if ($parcelle->latitude && $parcelle->longitude) {
            $resultat = 'conforme';
            $details = [
                'gps_present' => true,
                'latitude' => $parcelle->latitude,
                'longitude' => $parcelle->longitude,
            ];
        } else {
            $resultat = 'alerte';
            $details = ['gps_present' => false, 'message' => 'Coordonnées GPS manquantes sur la parcelle'];
        }

        return $analysable->analyses()->create([
            'type_analyse' => 'gps',
            'resultat' => $resultat,
            'details' => $details,
        ]);
    }

    public function analyserValiditeProfessionnels(Model $analysable, $documents): Analyse
    {
        $problemes = [];

        $notaire = $analysable->notaire;
        if ($notaire && !Professionnel::where('user_id', $notaire->id)->where('type', 'notaire')->exists()) {
            $problemes[] = 'Le notaire assigné n\'est pas inscrit au registre des professionnels';
        }

        $geometre = $analysable->parcelle?->missions()?->first()?->geometre;
        if ($geometre && !Professionnel::where('user_id', $geometre->id)->where('type', 'geometre')->exists()) {
            $problemes[] = 'Le géomètre intervenant n\'est pas inscrit au registre des professionnels';
        }

        $resultat = empty($problemes) ? 'conforme' : 'alerte';

        return $analysable->analyses()->create([
            'type_analyse' => 'validite_professionnels',
            'resultat' => $resultat,
            'details' => ['problemes' => $problemes, 'professionnels_verifies' => $notaire ? 1 : 0],
        ]);
    }
}
