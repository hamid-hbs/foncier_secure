<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Mission::with(['citoyen', 'geometre', 'parcelle.commune']);

        if ($user->hasRole('citoyen')) {
            $query->where('citoyen_id', $user->id);
        } elseif ($user->hasRole('geometre')) {
            $query->where('geometre_id', $user->id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function show(Mission $mission): JsonResponse
    {
        return response()->json($mission->load(['citoyen', 'geometre', 'parcelle.commune', 'documents', 'messages.sender']));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'geometre_id' => 'required|exists:users,id',
            'parcelle_id' => 'nullable|exists:parcelles,id',
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        $mission = Mission::create([
            'citoyen_id' => $request->user()->id,
            'geometre_id' => $validated['geometre_id'],
            'parcelle_id' => $validated['parcelle_id'] ?? null,
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($mission->load(['citoyen', 'geometre', 'parcelle']), 201);
    }

    public function accepter(Mission $mission): JsonResponse
    {
        if ($mission->statut !== 'soumise') {
            return response()->json(['message' => 'Mission déjà traitée.'], 400);
        }

        $mission->update(['statut' => 'acceptee']);
        return response()->json($mission);
    }

    public function refuser(Mission $mission): JsonResponse
    {
        if ($mission->statut !== 'soumise') {
            return response()->json(['message' => 'Mission déjà traitée.'], 400);
        }

        $mission->update(['statut' => 'refusee']);
        return response()->json($mission);
    }

    public function deposerRapport(Request $request, Mission $mission): JsonResponse
    {
        $validated = $request->validate([
            'rapport' => 'required|file|mimes:pdf|max:20480',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('rapport');
        $path = $file->store('rapports/missions/' . $mission->id);

        $mission->update([
            'statut' => 'terminee',
            'rapport_path' => $path,
            'commentaire_geometre' => $validated['commentaire'] ?? null,
            'completed_at' => now(),
        ]);

        return response()->json($mission);
    }
}
