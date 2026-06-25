<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FactureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $factures = Facture::with('emetteur')
            ->where('emetteur_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($factures);
    }

    public function show(Facture $facture): JsonResponse
    {
        return response()->json($facture->load('emetteur', 'facturable'));
    }

    public function store(Request $request): JsonResponse
    {
        $allowed = $request->user()->hasRole('notaire')
            ? 'App\Models\DossierTransaction,App\Models\Mission'
            : 'App\Models\Mission';

        $validated = $request->validate([
            'facturable_type' => 'required|in:' . $allowed,
            'facturable_id' => 'required|integer',
            'montant' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $facture = Facture::create([
            'facturable_type' => $validated['facturable_type'],
            'facturable_id' => $validated['facturable_id'],
            'emetteur_id' => $request->user()->id,
            'reference' => 'FAC-' . strtoupper(uniqid()),
            'montant' => $validated['montant'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($facture->load('emetteur'), 201);
    }

    public function update(Request $request, Facture $facture): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'statut' => 'nullable|in:brouillon,envoyee,payee,annulee',
        ]);

        $facture->update($validated);
        return response()->json($facture);
    }

    public function envoyer(Facture $facture): JsonResponse
    {
        $facture->update(['statut' => 'envoyee', 'envoyee_le' => now()]);
        return response()->json($facture);
    }

    public function marquerPayee(Facture $facture): JsonResponse
    {
        $facture->update(['statut' => 'payee', 'payee_le' => now()]);
        return response()->json($facture);
    }

    public function telechargerPdf(Facture $facture): Response
    {
        $pdf = Pdf::loadView('rapports.facture', ['facture' => $facture]);

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="facture-' . $facture->reference . '.pdf"');
    }
}
