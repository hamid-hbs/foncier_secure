<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analyse;
use App\Models\Parcelle;
use App\Models\Propriete;
use App\Services\AnalyseDocumentaireService;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Parcelle::with(['proprietaireActuel.user', 'commune', 'arrondissement', 'quartier']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('commune_id')) {
            $query->where('commune_id', $request->commune_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('titre', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function show(Parcelle $parcelle): JsonResponse
    {
        return response()->json(
            $parcelle->load([
                'proprietaireActuel.user', 'commune', 'arrondissement', 'quartier',
                'proprietes.proprietaire', 'documents',
            ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:parcelles',
            'titre' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'commune_id' => 'nullable|exists:communes,id',
            'arrondissement_id' => 'nullable|exists:arrondissements,id',
            'quartier_id' => 'nullable|exists:quartiers,id',
            'superficie' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'prix_estimatif' => 'nullable|numeric|min:0',
        ]);

        $parcelle = Parcelle::create($validated);

        Propriete::create([
            'parcelle_id' => $parcelle->id,
            'user_id' => $request->user()->id,
            'date_debut' => now(),
        ]);

        app(BlockchainService::class)->log('parcelle_created', $request->user()->id, 'parcelle', $parcelle->id, ['code' => $parcelle->code_parcelle]);
        app(AnalyseDocumentaireService::class)->analyser($parcelle);

        return response()->json($parcelle->load('proprietaireActuel.user'), 201);
    }
    
    public function update(Request $request, Parcelle $parcelle): JsonResponse
    {
        $proprio = $parcelle->proprietaireActuel;
        if ($proprio && $proprio->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'titre' => 'string|max:200',
            'description' => 'nullable|string',
            'superficie' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'prix_estimatif' => 'nullable|numeric|min:0',
            'commune_id' => 'nullable|exists:communes,id',
            'arrondissement_id' => 'nullable|exists:arrondissements,id',
            'quartier_id' => 'nullable|exists:quartiers,id',
        ]);

        $parcelle->update($validated);
        return response()->json($parcelle->load('proprietaireActuel.user'));
    }

    public function updateStatut(Request $request, Parcelle $parcelle): JsonResponse
    {
        $validated = $request->validate(['statut' => 'required|in:libre,en_demande,en_transaction,vendue']);
        $parcelle->update(['statut' => $validated['statut']]);
        return response()->json($parcelle);
    }

    public function uploadDocument(Request $request, Parcelle $parcelle): JsonResponse
    {
        $proprio = $parcelle->proprietaireActuel;
        if (!$proprio || $proprio->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'type_document' => 'required|string|max:50',
            'fichier' => 'required|file|max:20480',
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents/parcelles/' . $parcelle->id);

        $document = $parcelle->documents()->create([
            'type_document' => $validated['type_document'],
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => hash_file('sha256', $file->getRealPath()),
            'taille' => $file->getSize(),
            'uploader_id' => $request->user()->id,
            'uploaded_at' => now(),
        ]);

        app(AnalyseDocumentaireService::class)->analyser($parcelle, [$document->id]);

        return response()->json($document, 201);
    }

    public function deleteDocument(Request $request, Parcelle $parcelle, $documentId): JsonResponse
    {
        $document = $parcelle->documents()->findOrFail($documentId);
        $document->delete();
        return response()->json(null, 204);
    }

    public function historique(Parcelle $parcelle): JsonResponse
    {
        return response()->json($parcelle->proprietes()->with('proprietaire')->orderBy('date_debut', 'desc')->get());
    }
}
