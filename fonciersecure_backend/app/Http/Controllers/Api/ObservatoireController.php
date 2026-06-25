<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DossierTransaction;
use App\Models\Parcelle;
use App\Models\Mission;
use App\Models\Professionnel;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ObservatoireController extends Controller
{
    public function index(): JsonResponse
    {
        $parcelles = Parcelle::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut');

        $parcellesParCommune = Parcelle::selectRaw('commune_id, count(*) as total')
            ->whereNotNull('commune_id')
            ->groupBy('commune_id')
            ->with('commune:id,nom')
            ->get()
            ->pluck('total', 'commune.nom');

        return response()->json([
            'total_parcelles' => Parcelle::count(),
            'parcelles_libres' => $parcelles['libre'] ?? 0,
            'parcelles_vendues' => $parcelles['vendue'] ?? 0,
            'total_transactions' => DossierTransaction::count(),
            'total_verifications' => Mission::count(),
            'total_professionnels' => Professionnel::count(),
            'total_utilisateurs' => User::count(),
            'total_geometres' => User::whereHas('role', fn($q) => $q->where('nom', 'geometre'))->count(),
            'total_notaires' => User::whereHas('role', fn($q) => $q->where('nom', 'notaire'))->count(),
            'parcelles_par_statut' => $parcelles,
            'parcelles_par_commune' => $parcellesParCommune,
        ]);
    }
}
