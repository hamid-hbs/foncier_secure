<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;
use App\Models\RendezVousParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $rendezVous = RendezVous::with(['dossierTransaction', 'mission', 'participants.user.role'])
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->orWhereHas('dossierTransaction', function ($q) use ($user) {
                $q->where('notaire_id', $user->id)
                    ->orWhere('vendeur_id', $user->id)
                    ->orWhere('acheteur_id', $user->id);
            })
            ->orderBy('date_prevue', 'desc')
            ->paginate(20);

        return response()->json($rendezVous);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dossier_transaction_id' => 'nullable|exists:dossier_transactions,id',
            'mission_id' => 'nullable|exists:missions,id',
            'type' => 'required|in:signature,visite_terrain',
            'date_prevue' => 'required|date|after:now',
            'lieu' => 'nullable|string|max:255',
            'participants' => 'required|array',
            'participants.*' => 'exists:users,id',
        ]);

        $rv = RendezVous::create([
            'dossier_transaction_id' => $validated['dossier_transaction_id'] ?? null,
            'mission_id' => $validated['mission_id'] ?? null,
            'type' => $validated['type'],
            'date_prevue' => $validated['date_prevue'],
            'lieu' => $validated['lieu'] ?? null,
            'statut' => 'planifie',
        ]);

        foreach ($validated['participants'] as $userId) {
            RendezVousParticipant::create(['rendez_vous_id' => $rv->id, 'user_id' => $userId]);
        }

        return response()->json($rv->load('participants.user'), 201);
    }

    public function confirmer(Request $request, RendezVous $rendezVous): JsonResponse
    {
        $participant = RendezVousParticipant::where('rendez_vous_id', $rendezVous->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $participant->update(['est_confirme' => true]);

        if ($rendezVous->participants()->where('est_confirme', false)->doesntExist()) {
            $rendezVous->update(['statut' => 'confirme']);
        }

        return response()->json($rendezVous->fresh()->load('participants.user'));
    }

    public function updateStatut(Request $request, RendezVous $rendezVous): JsonResponse
    {
        $validated = $request->validate(['statut' => 'required|in:confirme,effectue,annule']);
        $rendezVous->update(['statut' => $validated['statut']]);
        return response()->json($rendezVous);
    }
}
