<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeAchat;
use App\Models\DossierTransaction;
use App\Services\AnalyseDocumentaireService;
use App\Services\RisqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DossierTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $dossiers = DossierTransaction::with(['parcelle', 'vendeur', 'acheteur', 'notaire'])
            ->where('vendeur_id', $user->id)
            ->orWhere('acheteur_id', $user->id)
            ->orWhere('notaire_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($dossiers);
    }

    public function show(DossierTransaction $dossierTransaction): JsonResponse
    {
        $dossierTransaction->load([
            'demandeAchat', 'parcelle', 'vendeur', 'acheteur', 'notaire',
            'rendezVous.participants', 'factures', 'avis', 'analyses',
        ]);

        $risque = app(RisqueService::class)->calculer($dossierTransaction);

        return response()->json([
            'dossier' => $dossierTransaction,
            'analyse_risque' => $risque,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['demande_achat_id' => 'required|exists:demande_achat,id']);

        $demande = DemandeAchat::with(['parcelle', 'acheteur', 'vendeur'])->findOrFail($validated['demande_achat_id']);

        if ($demande->statut !== 'notaire_sollicite') {
            return response()->json(['message' => 'Le notaire n\'a pas encore été sollicité.'], 400);
        }

        $dossier = DossierTransaction::create([
            'demande_achat_id' => $demande->id,
            'parcelle_id' => $demande->parcelle_id,
            'notaire_id' => $request->user()->id,
            'vendeur_id' => $demande->vendeur_id,
            'acheteur_id' => $demande->acheteur_id,
            'prix_vente' => $demande->parcelle->valeur_estimee,
            'statut' => 'en_attente',
        ]);

        $demande->update(['statut' => 'dossier_cree', 'dossier_transaction_id' => $dossier->id]);
        $demande->parcelle->update(['statut' => 'en_transaction']);

        app(AnalyseDocumentaireService::class)->analyser($dossier);

        return response()->json($dossier->load(['demandeAchat', 'parcelle', 'vendeur', 'acheteur', 'notaire']), 201);
    }

    public function validerPartie(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $user = $request->user();
        $now = now();

        if (!in_array($dossierTransaction->statut, ['en_attente', 'actif'])) {
            return response()->json(['message' => 'Le dossier ne peut pas être validé.'], 400);
        }

        if ($user->id === $dossierTransaction->vendeur_id) {
            $dossierTransaction->update(['validation_vendeur' => true, 'date_validation_vendeur' => $now]);
        } elseif ($user->id === $dossierTransaction->acheteur_id) {
            $dossierTransaction->update(['validation_acheteur' => true, 'date_validation_acheteur' => $now]);
        } else {
            return response()->json(['message' => 'Vous n\'êtes pas partie à cette transaction.'], 403);
        }

        $dossierTransaction->refresh();

        if ($dossierTransaction->validation_vendeur && $dossierTransaction->validation_acheteur) {
            $dossierTransaction->update(['statut' => 'actif']);
        }

        return response()->json($dossierTransaction->fresh());
    }

    public function suspendre(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $validated = $request->validate(['motif' => 'required|string|max:1000']);
        $dossierTransaction->update(['statut' => 'suspendu', 'motif_suspension' => $validated['motif']]);
        return response()->json($dossierTransaction);
    }

    public function reouvrir(DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->statut !== 'suspendu') {
            return response()->json(['message' => 'Le dossier n\'est pas suspendu.'], 400);
        }
        $dossierTransaction->update(['statut' => 'actif', 'motif_suspension' => null]);
        return response()->json($dossierTransaction);
    }

    public function cloturer(DossierTransaction $dossierTransaction): JsonResponse
    {
        $dossierTransaction->update(['statut' => 'cloture', 'closed_at' => now()]);
        $dossierTransaction->parcelle->update(['statut' => 'vendue']);
        return response()->json($dossierTransaction);
    }

    public function messages(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json($dossierTransaction->messages()->with('sender')->orderBy('created_at')->get());
    }

    public function envoyerMessage(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $validated = $request->validate(['contenu' => 'required|string|max:2000']);

        $message = $dossierTransaction->messages()->create([
            'sender_id' => $request->user()->id,
            'contenu' => $validated['contenu'],
        ]);

        return response()->json($message->load('sender'), 201);
    }

    public function documents(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json($dossierTransaction->documents);
    }

    public function ajouterDocument(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $validated = $request->validate([
            'type_document' => 'required|string|max:50',
            'fichier' => 'required|file|max:20480',
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents/transactions/' . $dossierTransaction->id);

        $document = $dossierTransaction->documents()->create([
            'type_document' => $validated['type_document'],
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => hash_file('sha256', $file->getRealPath()),
            'taille' => $file->getSize(),
            'uploader_id' => $request->user()->id,
            'uploaded_at' => now(),
        ]);

        app(AnalyseDocumentaireService::class)->analyser($dossierTransaction, [$document->id]);

        return response()->json($document, 201);
    }
}
