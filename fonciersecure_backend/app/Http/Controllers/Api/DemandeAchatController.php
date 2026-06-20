<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeAchat;
use App\Models\DemandeMessage;
use App\Models\DocumentDemande;
use App\Models\Parcelle;
use App\Helpers\EncryptionHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemandeAchatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parcelle_id' => 'required|exists:parcelles,id',
            'message' => 'nullable|string',
        ]);

        $existe = DemandeAchat::where('parcelle_id', $validated['parcelle_id'])
            ->where('acheteur_id', $request->user()->id)
            ->whereIn('statut', ['soumise', 'acceptee'])
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Vous avez deja soumis une demande pour cette parcelle'], 409);
        }

        $demande = DemandeAchat::create([
            'parcelle_id' => $validated['parcelle_id'],
            'acheteur_id' => $request->user()->id,
            'message' => $validated['message'] ?? null,
            'statut' => 'soumise',
        ]);

        $parcelle = Parcelle::find($validated['parcelle_id']);
        $parcelle->update(['statut' => 'en_demande']);

        return response()->json($demande->load(['parcelle', 'acheteur']), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'citoyen') {
            $query = DemandeAchat::where('acheteur_id', $user->id)
                ->orWhereHas('parcelle', fn($q) => $q->where('proprietaire_id', $user->id));
        } else {
            $query = DemandeAchat::query();
        }

        return response()->json($query->with(['parcelle.commune', 'acheteur', 'notaire'])
            ->latest()
            ->paginate(15));
    }

    public function show(DemandeAchat $demandeAchat): JsonResponse
    {
        $demandeAchat->load(['parcelle.commune', 'parcelle.arrondissement', 'parcelle.quartier', 'acheteur', 'notaire', 'documents']);
        return response()->json($demandeAchat);
    }

    public function repondre(Request $request, DemandeAchat $demandeAchat): JsonResponse
    {
        $validated = $request->validate([
            'statut' => 'required|in:acceptee,refusee',
            'notaire_id' => 'required_if:statut,acceptee|exists:users,id',
        ]);

        $parcelle = $demandeAchat->parcelle;

        if ($parcelle->proprietaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le proprietaire peut repondre'], 403);
        }

        $demandeAchat->update([
            'statut' => $validated['statut'],
            'notaire_id' => $validated['notaire_id'] ?? null,
        ]);

        if ($validated['statut'] === 'acceptee') {
            // Parcelle reste en 'en_demande' — le notaire ouvrira un dossier,
            // et le statut passera a 'en_transaction' uniquement apres validation des deux parties
        } else {
            $hasOther = DemandeAchat::where('parcelle_id', $parcelle->id)
                ->where('statut', 'soumise')
                ->exists();
            if (!$hasOther) {
                $parcelle->update(['statut' => 'libre']);
            }
        }

        return response()->json($demandeAchat->load(['parcelle', 'acheteur', 'notaire']));
    }

    public function uploadDocument(Request $request, DemandeAchat $demandeAchat): JsonResponse
    {
        if ($demandeAchat->acheteur_id !== $request->user()->id) {
            return response()->json(['message' => 'Acces refuse'], 403);
        }

        $validated = $request->validate([
            'fichier' => 'required|file|max:20480',
            'type_document' => 'required|in:piece_identite,autre',
        ]);

        $file = $request->file('fichier');
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);
        $path = 'demandes-achat/' . $demandeAchat->id . '/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $doc = DocumentDemande::create([
            'demande_id' => $demandeAchat->id,
            'type_document' => $validated['type_document'],
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => $hash,
            'taille' => $file->getSize(),
        ]);

        return response()->json($doc, 201);
    }

    public function messages(DemandeAchat $demandeAchat): JsonResponse
    {
        $demandeAchat->load('parcelle');
        abort_if($this->nEstPasPartie($demandeAchat, request()->user()), 403, 'Acces refuse');

        return response()->json(
            $demandeAchat->messages()->with('sender')->latest()->get()
        );
    }

    public function envoyerMessage(Request $request, DemandeAchat $demandeAchat): JsonResponse
    {
        $demandeAchat->load('parcelle');
        abort_if($this->nEstPasPartie($demandeAchat, $request->user()), 403, 'Acces refuse');

        $validated = $request->validate(['contenu' => 'required|string']);

        $message = DemandeMessage::create([
            'demande_id' => $demandeAchat->id,
            'sender_id' => $request->user()->id,
            'contenu' => $validated['contenu'],
        ]);

        return response()->json($message->load('sender'), 201);
    }

    private function nEstPasPartie(DemandeAchat $demandeAchat, $user): bool
    {
        return $user->id !== $demandeAchat->acheteur_id
            && $user->id !== $demandeAchat->parcelle->proprietaire_id;
    }
}
