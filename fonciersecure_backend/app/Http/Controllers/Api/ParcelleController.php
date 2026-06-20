<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parcelle;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($token = $request->bearerToken()) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        }

        $user = $request->user();

        $query = Parcelle::with(['proprietaire', 'commune', 'arrondissement', 'quartier']);

        if ($user && $user->role === 'citoyen') {
            $query->where('proprietaire_id', $user->id);
        }

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

    public function show(Request $request, Parcelle $parcelle): JsonResponse
    {
        if ($token = $request->bearerToken()) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        }

        $user = $request->user();

        $parcelle->load([
            'proprietaire',
            'commune', 'arrondissement', 'quartier',
            'verifications.analyses', 'verifications.intervention',
        ]);

        $documentsVisibles = false;
        if ($user) {
            if ($parcelle->proprietaire_id === $user->id || $user->isAdmin()) {
                $documentsVisibles = true;
            } else {
                $verificationActive = $parcelle->verifications()
                    ->where('demandeur_id', $user->id)
                    ->exists();
                if ($verificationActive) {
                    $documentsVisibles = true;
                }
            }
        }

        if ($documentsVisibles) {
            $parcelle->load('documents');
        }

        $data = $parcelle->toArray();
        $data['documents_visibles'] = $documentsVisibles;

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string',
            'commune_id' => 'nullable|exists:communes,id',
            'arrondissement_id' => 'nullable|exists:arrondissements,id',
            'quartier_id' => 'nullable|exists:quartiers,id',
            'superficie' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'prix_estimatif' => 'nullable|numeric|min:0',
        ]);

        $validated['proprietaire_id'] = $request->user()->id;

        $parcelle = Parcelle::create($validated);

        app(BlockchainService::class)->log(
            'parcelle_created',
            $request->user()->id,
            'parcelle',
            $parcelle->id,
            ['titre' => $parcelle->titre]
        );

        return response()->json($parcelle->load('proprietaire'), 201);
    }

    public function update(Request $request, Parcelle $parcelle): JsonResponse
    {
        if ($parcelle->proprietaire_id !== $request->user()->id && !$request->user()->isAdmin()) {
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

        app(BlockchainService::class)->log(
            'parcelle_updated',
            $request->user()->id,
            'parcelle',
            $parcelle->id,
            ['titre' => $parcelle->titre]
        );

        return response()->json($parcelle->load('proprietaire'));
    }

    public function updateStatut(Request $request, Parcelle $parcelle): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $parcelle->proprietaire_id !== $user->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'statut' => 'required|in:libre,en_verification,en_transaction,vendue',
        ]);

        $parcelle->update(['statut' => $validated['statut']]);

        app(BlockchainService::class)->log(
            'parcelle_statut_' . $validated['statut'],
            $user->id,
            'parcelle',
            $parcelle->id,
            ['ancien_statut' => $parcelle->getOriginal('statut')]
        );

        return response()->json($parcelle);
    }

    public function uploadDocument(Request $request, Parcelle $parcelle): JsonResponse
    {
        if ($parcelle->proprietaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'type_document' => 'required|in:tf,adc,plan_topo,certificat_admin,photo,autre',
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $file = $request->file('fichier');
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);
        $path = 'parcelles/' . $parcelle->id . '/' . uniqid() . '.' . $file->extension();

        $parcelle->documents()->create([
            'type_document' => $validated['type_document'],
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => $hash,
            'taille' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        return response()->json($parcelle->load('documents'), 201);
    }

    public function deleteDocument(Request $request, Parcelle $parcelle, $documentId): JsonResponse
    {
        if ($parcelle->proprietaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $document = $parcelle->documents()->findOrFail($documentId);
        $document->delete();

        return response()->json(null, 204);
    }
}
