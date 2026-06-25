<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DossierTransaction;
use App\Models\Parcelle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartographieController extends Controller
{
    public function couches(Request $request): JsonResponse
    {
        $query = Parcelle::whereNotNull('latitude')->whereNotNull('longitude')
            ->where('latitude', '!=', 0)->where('longitude', '!=', 0)
            ->with('commune:id,nom');

        if ($request->filled('commune_id')) {
            $query->where('commune_id', $request->commune_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $parcelles = $query->get();

        $terrainsSignales = Parcelle::whereIn('statut', ['en_demande', 'en_transaction'])
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->where('latitude', '!=', 0)->where('longitude', '!=', 0)
            ->get(['id', 'code', 'titre', 'latitude', 'longitude', 'statut']);

        $activiteRecente = collect();

        $recentParcelles = Parcelle::orderByDesc('created_at')->limit(5)->get();
        foreach ($recentParcelles as $p) {
            $activiteRecente->push([
                'type' => 'parcelle',
                'titre' => $p->titre_parcelle ?? $p->code_parcelle,
                'statut' => $p->statut,
                'date' => $p->created_at,
            ]);
        }

        $recentTransactions = DossierTransaction::orderByDesc('created_at')->limit(5)->get();
        foreach ($recentTransactions as $t) {
            $activiteRecente->push([
                'type' => 'transaction',
                'titre' => 'Transaction #' . $t->id,
                'statut' => $t->statut ?? 'en_cours',
                'date' => $t->created_at,
            ]);
        }

        $activiteRecente = $activiteRecente->sortByDesc('date')->values()->take(10);

        return response()->json([
            'parcelles' => $parcelles->map(fn($p) => [
                'id' => $p->id,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'titre' => $p->titre_parcelle ?? $p->code_parcelle,
                'commune' => $p->commune?->nom,
                'arrondissement' => null,
                'statut' => $p->statut,
                'superficie' => $p->superficie,
            ]),
            'terrains_signales' => $terrainsSignales->map(fn($p) => [
                'id' => $p->id,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'titre' => $p->titre_parcelle ?? $p->code_parcelle,
                'score' => $p->statut === 'en_transaction' ? 4 : 2,
            ]),
            'professionnels' => [],
            'activite_recente' => $activiteRecente,
        ]);
    }
}
