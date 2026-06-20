<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\DossierTransaction;
use App\Models\Verification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ObservatoireController extends Controller
{
    public function index(): JsonResponse
    {
        $transactionsTotal = DossierTransaction::count();
        $transactionsEnCours = DossierTransaction::where('statut', '!=', 'cloture')->count();
        $transactionsCloturees = DossierTransaction::where('statut', 'cloture')->count();
        $transactionsCeMois = DossierTransaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $verifications = Verification::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN niveau_risque = 'faible' THEN 1 ELSE 0 END) as faible,
            SUM(CASE WHEN niveau_risque = 'moyen' THEN 1 ELSE 0 END) as moyen,
            SUM(CASE WHEN niveau_risque = 'eleve' THEN 1 ELSE 0 END) as eleve
        ")->first();

        $evolutionMensuelle = collect(range(1, 12))->map(function ($mois) {
            $transactions = DossierTransaction::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $mois)
                ->count();
            $verifications = Verification::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $mois)
                ->count();

            return [
                'mois' => $mois,
                'transactions' => $transactions,
                'verifications' => $verifications,
            ];
        });

        $prixMoyen = Commune::withAvg('parcelles as superficie_moyenne', 'superficie')->get()->map(fn($c) => [
            'commune' => $c->nom,
            'superficie_moyenne' => (float) ($c->superficie_moyenne ?? 0),
        ]);

        return response()->json([
            'transactions' => [
                'total' => $transactionsTotal,
                'en_cours' => $transactionsEnCours,
                'cloturees' => $transactionsCloturees,
                'ce_mois' => $transactionsCeMois,
            ],
            'verifications' => [
                'total' => (int) ($verifications->total ?? 0),
                'faible' => (int) ($verifications->faible ?? 0),
                'moyen' => (int) ($verifications->moyen ?? 0),
                'eleve' => (int) ($verifications->eleve ?? 0),
            ],
            'evolution_mensuelle' => $evolutionMensuelle,
            'prix_moyen' => [
                'par_commune' => $prixMoyen,
            ],
        ]);
    }
}
