<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvisProfessionnel;
use App\Models\Professionnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionnelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Professionnel::with('user.role');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('cabinet', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($q2) use ($s) {
                        $q2->where('nom', 'like', "%{$s}%")->orWhere('prenom', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('zone')) {
            $query->where('zone_intervention', 'like', '%' . $request->zone . '%');
        }

        return response()->json($query->orderBy('note_moyenne', 'desc')->paginate(20));
    }

    public function show(Professionnel $professionnel): JsonResponse
    {
        return response()->json($professionnel->load(['user.role', 'avis.auteur']));
    }

    public function updateProfile(Request $request, Professionnel $professionnel): JsonResponse
    {
        if ($professionnel->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'cabinet' => 'nullable|string|max:200',
            'zone_intervention' => 'nullable|string|max:200',
            'specialites' => 'nullable|array',
            'numero_agrement' => 'nullable|string|max:50',
            'adresse_professionnelle' => 'nullable|string|max:255',
        ]);

        $professionnel->update($validated);
        return response()->json($professionnel);
    }

    public function donnerAvis(Request $request, Professionnel $professionnel): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:500',
            'dossier_transaction_id' => 'nullable|exists:dossier_transactions,id',
        ]);

        $avis = AvisProfessionnel::create([
            'professionnel_id' => $professionnel->id,
            'auteur_id' => $request->user()->id,
            'dossier_transaction_id' => $validated['dossier_transaction_id'] ?? null,
            'note' => $validated['note'],
            'commentaire' => $validated['commentaire'] ?? null,
        ]);

        $professionnel->recalculerNote();

        return response()->json($avis->load('auteur'), 201);
    }

    public function statistiques(Professionnel $professionnel): JsonResponse
    {
        return response()->json([
            'nombre_avis' => $professionnel->avis()->count(),
            'note_moyenne' => $professionnel->note_moyenne,
            'nombre_dossiers' => $professionnel->user->dossiersNotaire()->count(),
            'nombre_missions' => $professionnel->type === 'geometre' ? $professionnel->user->missionsGeometre()->count() : 0,
        ]);
    }
}
