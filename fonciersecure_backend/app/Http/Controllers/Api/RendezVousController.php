<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfirmationRendezVous;
use App\Models\DossierTransaction;
use App\Models\RendezVous;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    public function store(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $user = $request->user();
        $estNotaire = $dossierTransaction->notaire_id === $user->id;
        $estPartie = $user->id === $dossierTransaction->vendeur_id
            || $user->id === $dossierTransaction->acheteur_id;

        if (!$estNotaire && !$estPartie) {
            return response()->json(['message' => 'Seul le notaire, le vendeur ou l\'acheteur peut planifier'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:signature,visite_terrain',
            'date_prevue' => 'required|date|after:now',
            'lieu' => 'nullable|string|max:255',
        ]);

        $rv = RendezVous::create([
            'dossier_id' => $dossierTransaction->id,
            'type' => $validated['type'],
            'date_prevue' => $validated['date_prevue'],
            'lieu' => $validated['lieu'] ?? null,
            'statut' => 'planifie',
        ]);

        if ($estNotaire) {
            $dossierTransaction->update(['statut' => 'rendezvous_planifie']);
        }

        $qui = $estNotaire ? 'Notaire' : ($user->id === $dossierTransaction->vendeur_id ? 'Vendeur' : 'Acheteur');

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $user->id,
            'action' => "Rendez-vous {$validated['type']} planifié par le $qui le {$validated['date_prevue']}",
        ]);

        app(BlockchainService::class)->log(
            'rdv_planifie',
            $user->id,
            'transaction',
            $dossierTransaction->id,
            ['type' => $validated['type'], 'date' => $validated['date_prevue'], 'planifie_par' => $qui]
        );

        return response()->json($rv, 201);
    }

    public function index(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json(
            $dossierTransaction->rendezVous()->with('confirmations.user')->latest()->get()
        );
    }

    public function confirmer(Request $request, RendezVous $rendezVous): JsonResponse
    {
        $dossier = $rendezVous->dossier;
        $partieIds = collect([$dossier->vendeur_id, $dossier->acheteur_id])
            ->filter()
            ->push($dossier->notaire_id);

        if (!$partieIds->contains($request->user()->id)) {
            return response()->json(['message' => 'Vous n\'etes pas partie a ce dossier'], 403);
        }

        $confirmation = ConfirmationRendezVous::updateOrCreate(
            [
                'rendez_vous_id' => $rendezVous->id,
                'user_id' => $request->user()->id,
            ],
            ['est_confirme' => true]
        );

        $toutesConfirmees = $partieIds->every(fn($id) =>
            ConfirmationRendezVous::where('rendez_vous_id', $rendezVous->id)
                ->where('user_id', $id)
                ->where('est_confirme', true)
                ->exists()
        );

        if ($toutesConfirmees && $partieIds->count() > 1) {
            $rendezVous->update(['statut' => 'confirme']);
        }

        return response()->json($confirmation);
    }

    public function updateStatut(Request $request, RendezVous $rendezVous): JsonResponse
    {
        if ($rendezVous->dossier->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire peut modifier'], 403);
        }

        $validated = $request->validate([
            'statut' => 'required|in:confirme,effectue,annule',
        ]);

        $rendezVous->update(['statut' => $validated['statut']]);

        return response()->json($rendezVous);
    }
}
