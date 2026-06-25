<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeAchat;
use App\Models\Parcelle;
use App\Models\Propriete;
use App\Services\AnalyseDocumentaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DemandeAchatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $demandes = DemandeAchat::with(['parcelle', 'acheteur', 'vendeur', 'notaire'])
            ->where('acheteur_id', $user->id)
            ->orWhere('vendeur_id', $user->id)
            ->orWhere('notaire_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($demandes);
    }

    public function show(DemandeAchat $demandeAchat): JsonResponse
    {
        return response()->json($demandeAchat->load(['parcelle', 'acheteur', 'vendeur', 'notaire', 'dossierTransaction']));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parcelle_id' => 'required|exists:parcelles,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $parcelle = Parcelle::findOrFail($validated['parcelle_id']);
        $proprio = $parcelle->proprietaireActuel;

        if (!$proprio) {
            return response()->json(['message' => 'Cette parcelle n\'a pas de propriétaire.'], 400);
        }

        if ($proprio->user_id === $request->user()->id) {
            return response()->json(['message' => 'Vous êtes déjà propriétaire de cette parcelle.'], 400);
        }

        if ($parcelle->statut !== 'libre') {
            return response()->json(['message' => 'Cette parcelle n\'est pas disponible.'], 400);
        }

        $demande = DemandeAchat::create([
            'parcelle_id' => $parcelle->id,
            'acheteur_id' => $request->user()->id,
            'vendeur_id' => $proprio->user_id,
            'message_acheteur' => $validated['message'] ?? null,
            'statut' => 'soumise',
        ]);

        $parcelle->update(['statut' => 'en_demande']);

        return response()->json($demande->load(['parcelle', 'acheteur', 'vendeur']), 201);
    }

    public function accepter(DemandeAchat $demandeAchat): JsonResponse
    {
        if ($demandeAchat->statut !== 'soumise') {
            return response()->json(['message' => 'Demande déjà traitée.'], 400);
        }

        $demandeAchat->update([
            'statut' => 'acceptee',
            'code' => strtoupper(Str::random(8)),
        ]);

        return response()->json($demandeAchat->fresh()->load(['parcelle', 'acheteur', 'vendeur']));
    }

    public function refuser(DemandeAchat $demandeAchat): JsonResponse
    {
        if ($demandeAchat->statut !== 'soumise') {
            return response()->json(['message' => 'Demande déjà traitée.'], 400);
        }

        $demandeAchat->update(['statut' => 'refusee']);
        $demandeAchat->parcelle->update(['statut' => 'libre']);

        return response()->json($demandeAchat->fresh());
    }

    public function solliciterNotaire(Request $request, DemandeAchat $demandeAchat): JsonResponse
    {
        $validated = $request->validate(['notaire_id' => 'required|exists:users,id']);

        if ($demandeAchat->statut !== 'acceptee') {
            return response()->json(['message' => 'La demande doit d\'abord être acceptée.'], 400);
        }

        $demandeAchat->update([
            'statut' => 'notaire_sollicite',
            'notaire_id' => $validated['notaire_id'],
        ]);

        return response()->json($demandeAchat->fresh()->load(['parcelle', 'acheteur', 'vendeur', 'notaire']));
    }

    public function messages(DemandeAchat $demandeAchat): JsonResponse
    {
        return response()->json($demandeAchat->messages()->with('sender')->orderBy('created_at')->get());
    }

    public function envoyerMessage(Request $request, DemandeAchat $demandeAchat): JsonResponse
    {
        $validated = $request->validate(['contenu' => 'required|string|max:2000']);

        $message = $demandeAchat->messages()->create([
            'sender_id' => $request->user()->id,
            'contenu' => $validated['contenu'],
        ]);

        return response()->json($message->load('sender'), 201);
    }

    public function documents(DemandeAchat $demandeAchat): JsonResponse
    {
        return response()->json($demandeAchat->documents);
    }

    public function uploadDocument(Request $request, DemandeAchat $demandeAchat): JsonResponse
    {
        $validated = $request->validate([
            'type_document' => 'required|string|max:50',
            'fichier' => 'required|file|max:10240',
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents/demandes/' . $demandeAchat->id);

        $document = $demandeAchat->documents()->create([
            'type_document' => $validated['type_document'],
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => hash_file('sha256', $file->getRealPath()),
            'taille' => $file->getSize(),
            'uploader_id' => $request->user()->id,
            'uploaded_at' => now(),
        ]);

        app(AnalyseDocumentaireService::class)->analyser($demandeAchat, [$document->id]);

        return response()->json($document, 201);
    }
}
