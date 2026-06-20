<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvisProfessionnel;
use App\Models\DossierIntervenant;
use App\Models\InterventionGeometre;
use App\Models\Professionnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionnelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Professionnel::with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('cabinet', 'like', "%{$s}%")
                    ->orWhere('specialites', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($q2) use ($s) {
                        $q2->where('nom', 'like', "%{$s}%")
                            ->orWhere('prenom', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('zone')) {
            $query->where('zone_intervention', 'like', '%' . $request->zone . '%');
        }

        if ($request->filled('specialite')) {
            $query->where('specialites', 'like', '%' . $request->specialite . '%');
        }

        if ($request->filled('commune_id')) {
            $query->where('zone_intervention', 'like', '%' . $request->commune_id . '%');
        }

        $query->orderBy('note_moyenne', 'desc');

        return response()->json($query->paginate(20));
    }

    public function show(Professionnel $professionnel): JsonResponse
    {
        $professionnel->load(['user', 'avis.auteur']);
        return response()->json($professionnel);
    }

    public function updateProfile(Request $request, Professionnel $professionnel): JsonResponse
    {
        if ($professionnel->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'cabinet' => 'nullable|string|max:200',
            'zone_intervention' => 'nullable|string|max:200',
            'specialites' => 'nullable|string',
        ]);

        $professionnel->update($validated);

        return response()->json($professionnel);
    }

    public function donnerAvis(Request $request, Professionnel $professionnel): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string',
        ]);

        $avis = AvisProfessionnel::create([
            'professionnel_id' => $professionnel->id,
            'auteur_id' => $request->user()->id,
            'note' => $validated['note'],
            'commentaire' => $validated['commentaire'] ?? null,
        ]);

        $professionnel->recalculerNote();

        return response()->json($avis, 201);
    }

    public function statistiques(Professionnel $professionnel): JsonResponse
    {
        $nombreAvis = $professionnel->avis()->count();

        $nombreDossiers = DossierIntervenant::where('user_id', $professionnel->user_id)->count();

        $nombreVerifications = 0;
        if ($professionnel->type === 'geometre') {
            $nombreVerifications = InterventionGeometre::where('geometre_id', $professionnel->user_id)->count();
        }

        return response()->json([
            'nombre_avis' => $nombreAvis,
            'note_moyenne' => $professionnel->note_moyenne,
            'nombre_dossiers' => $nombreDossiers,
            'nombre_verifications' => $nombreVerifications,
        ]);
    }

}
