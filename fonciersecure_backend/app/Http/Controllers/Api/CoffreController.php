<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoffreDocument;
use App\Models\CoffreDossier;
use App\Helpers\EncryptionHelper;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CoffreController extends Controller
{
    public function indexDossiers(Request $request): JsonResponse
    {
        $dossiers = CoffreDossier::with('documents')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($dossiers);
    }

    public function creerDossier(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        $dossier = CoffreDossier::create([
            'user_id' => $request->user()->id,
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($dossier, 201);
    }

    public function uploadDocument(Request $request, CoffreDossier $coffreDossier): JsonResponse
    {
        if ($coffreDossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'fichier' => 'required|file|max:51200',
        ]);

        $file = $request->file('fichier');
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);
        $path = 'coffre/' . $coffreDossier->id . '/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $document = CoffreDocument::create([
            'dossier_id' => $coffreDossier->id,
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => $hash,
            'taille' => $file->getSize(),
            'type_mime' => $file->getMimeType(),
        ]);

        app(BlockchainService::class)->log(
            'coffre_upload',
            $request->user()->id,
            'coffre',
            $document->id,
            ['nom_fichier' => $file->getClientOriginalName(), 'dossier_id' => $coffreDossier->id]
        );

        return response()->json($document, 201);
    }

    public function telechargerDocument(CoffreDocument $coffreDocument)
    {
        if ($coffreDocument->dossier->user_id !== auth()->id()) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        if (!file_exists(storage_path('app/' . $coffreDocument->chemin_fichier))) {
            return response()->json(['message' => 'Fichier non trouvé'], 404);
        }

        $content = EncryptionHelper::readEncrypted($coffreDocument->chemin_fichier);
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $coffreDocument->nom_fichier, ['Content-Type' => 'application/octet-stream']);
    }

    public function partagerDocument(Request $request, CoffreDocument $coffreDocument): JsonResponse
    {
        if ($coffreDocument->dossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'expire_le' => 'nullable|date|after:now',
        ]);

        $partage = $coffreDocument->partages()->create([
            'partage_avec' => $validated['email'],
            'token' => Str::random(64),
            'expire_le' => $validated['expire_le'] ?? null,
        ]);

        app(BlockchainService::class)->log(
            'coffre_partage',
            $request->user()->id,
            'coffre',
            $coffreDocument->id,
            ['partage_avec' => $validated['email']]
        );

        return response()->json($partage, 201);
    }

    public function showDossier(Request $request, CoffreDossier $coffreDossier): JsonResponse
    {
        if ($coffreDossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $coffreDossier->load('documents');

        return response()->json($coffreDossier);
    }

    public function deleteDocument(Request $request, CoffreDocument $coffreDocument): JsonResponse
    {
        if ($coffreDocument->dossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $fullPath = storage_path('app/' . $coffreDocument->chemin_fichier);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $coffreDocument->delete();

        return response()->json(['message' => 'Document supprimé avec succès']);
    }

    public function comparerVersions(Request $request, CoffreDocument $coffreDocument): JsonResponse
    {
        if ($coffreDocument->dossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $version = $coffreDocument->version ?? 1;

        return response()->json([
            'document_id' => $coffreDocument->id,
            'version_actuelle' => $version,
            'comparaison_disponible' => $version > 1,
            'message' => $version > 1
                ? 'La comparaison d\'historique des versions sera disponible dans une prochaine mise à jour.'
                : 'Ce document n\'a qu\'une seule version. La comparaison est disponible pour les documents ayant plusieurs versions.',
        ]);
    }

    public function listPartages(Request $request, CoffreDocument $coffreDocument): JsonResponse
    {
        if ($coffreDocument->dossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $partages = $coffreDocument->partages()->latest()->get();

        return response()->json($partages);
    }

    public function showDocument(Request $request, CoffreDocument $coffreDocument): JsonResponse
    {
        if ($coffreDocument->dossier->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $coffreDocument->load('dossier');

        return response()->json($coffreDocument);
    }

    public function verifierIntegrite(CoffreDocument $coffreDocument): JsonResponse
    {
        return response()->json([
            'document_id' => $coffreDocument->id,
            'nom_fichier' => $coffreDocument->nom_fichier,
            'integrite' => $coffreDocument->verifierIntegrite(),
        ]);
    }
}
