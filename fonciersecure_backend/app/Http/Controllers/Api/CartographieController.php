<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parcelle;
use App\Models\Professionnel;
use App\Models\Verification;
use App\Models\DossierTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartographieController extends Controller
{
    public function couches(Request $request): JsonResponse
    {
        $periode = $request->input('periode', 'all');

        $dateFilter = match ($periode) {
            '1mois' => now()->subMonth(),
            '6mois' => now()->subMonths(6),
            '1an' => now()->subYear(),
            default => null,
        };

        $verificationsQuery = Verification::with('parcelle');
        $transactionsQuery = DossierTransaction::query();
        $parcellesQuery = Parcelle::with(['proprietaire', 'commune', 'arrondissement'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($dateFilter) {
            $verificationsQuery->where('verifications.created_at', '>=', $dateFilter);
            $transactionsQuery->where('created_at', '>=', $dateFilter);
            $parcellesQuery->where('created_at', '>=', $dateFilter);
        }

        $parcelles = $parcellesQuery->get()
            ->filter(fn($p) => (float) $p->latitude !== 0.0 && (float) $p->longitude !== 0.0)
            ->map(fn($p) => [
                'id' => $p->id,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'titre' => $p->titre,
                'statut' => $p->statut,
                'superficie' => $p->superficie,
                'commune' => $p->commune?->nom,
                'arrondissement' => $p->arrondissement?->nom,
                'proprietaire' => trim(($p->proprietaire?->prenom ?? '') . ' ' . ($p->proprietaire?->nom ?? '')),
            ])
            ->values();

        $terrainsSignales = $verificationsQuery
            ->where('niveau_risque', 'eleve')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'lat' => (float) ($v->parcelle->latitude ?? 0),
                'lng' => (float) ($v->parcelle->longitude ?? 0),
                'titre' => $v->titre,
                'score' => $v->score_risque,
            ]);

        $professionnels = Professionnel::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nom' => $p->user->nom . ' ' . $p->user->prenom,
                'type' => $p->type,
                'note' => (float) $p->note_moyenne,
                'cabinet' => $p->cabinet,
            ]);

        $activiteRecente = DossierTransaction::latest()->take(30)->get()->map(fn($d) => [
            'type' => 'transaction',
            'titre' => $d->titre,
            'statut' => $d->statut,
            'date' => $d->created_at,
        ]);

        return response()->json([
            'parcelles' => $parcelles,
            'terrains_signales' => $terrainsSignales,
            'professionnels' => $professionnels,
            'activite_recente' => $activiteRecente,
        ]);
    }
}
