<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DossierTransaction;
use App\Models\Facture;
use App\Services\BlockchainService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function index(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json(
            $dossierTransaction->factures()->with('emetteur')->latest()->get()
        );
    }

    public function store(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire responsable peut creer une facture'], 403);
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $facture = Facture::create([
            'dossier_id' => $dossierTransaction->id,
            'emetteur_id' => $request->user()->id,
            'montant' => $validated['montant'],
            'description' => $validated['description'] ?? null,
            'statut' => 'brouillon',
        ]);

        app(BlockchainService::class)->log(
            'facture_creer',
            $request->user()->id,
            'facture',
            $facture->id,
            ['dossier_id' => $dossierTransaction->id, 'montant' => $facture->montant]
        );

        return response()->json($facture->load('emetteur'), 201);
    }

    public function show(Facture $facture): JsonResponse
    {
        return response()->json($facture->load(['emetteur', 'dossier']));
    }

    public function update(Request $request, Facture $facture): JsonResponse
    {
        if ($facture->emetteur_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul l\'emetteur peut modifier'], 403);
        }

        if ($facture->statut !== 'brouillon') {
            return response()->json(['message' => 'Impossible de modifier une facture envoyee ou payee'], 400);
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $facture->update($validated);

        return response()->json($facture->load('emetteur'));
    }

    public function envoyer(Request $request, Facture $facture): JsonResponse
    {
        if ($facture->emetteur_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul l\'emetteur peut envoyer'], 403);
        }

        $facture->update([
            'statut' => 'envoyee',
            'envoyee_le' => now(),
        ]);

        app(BlockchainService::class)->log(
            'facture_envoyee',
            $request->user()->id,
            'facture',
            $facture->id,
            ['dossier_id' => $facture->dossier_id]
        );

        return response()->json($facture->load('emetteur'));
    }

    public function marquerPayee(Request $request, Facture $facture): JsonResponse
    {
        if ($facture->emetteur_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul l\'emetteur peut confirmer le paiement'], 403);
        }

        $facture->update([
            'statut' => 'payee',
            'payee_le' => now(),
        ]);

        app(BlockchainService::class)->log(
            'facture_payee',
            $request->user()->id,
            'facture',
            $facture->id,
            ['dossier_id' => $facture->dossier_id]
        );

        return response()->json($facture->load('emetteur'));
    }

    public function telechargerPdf(Facture $facture)
    {
        $facture->load(['emetteur', 'dossier.parcelle', 'dossier.vendeur', 'dossier.acheteur']);

        $pdf = Pdf::loadView('rapports.facture', ['facture' => $facture]);
        return $pdf->download('facture_' . $facture->reference . '.pdf');
    }
}
