<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeAchat;
use App\Models\Document;
use App\Models\DossierTransaction;
use App\Models\Mission;
use App\Models\Parcelle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $ids = collect();

        $parcelles = Parcelle::whereHas('proprietaireActuel', fn($q) => $q->where('user_id', $user->id))->pluck('id');
        $ids->put('App\Models\Parcelle', $parcelles);

        $demandes = DemandeAchat::where('acheteur_id', $user->id)->orWhere('vendeur_id', $user->id)->pluck('id');
        $ids->put('App\Models\DemandeAchat', $demandes);

        $dossiers = DossierTransaction::where('vendeur_id', $user->id)
            ->orWhere('acheteur_id', $user->id)
            ->orWhere('notaire_id', $user->id)
            ->pluck('id');
        $ids->put('App\Models\DossierTransaction', $dossiers);

        $missions = Mission::where('citoyen_id', $user->id)->orWhere('geometre_id', $user->id)->pluck('id');
        $ids->put('App\Models\Mission', $missions);

        $query = Document::query();
        $query->where(function ($q) use ($ids) {
            foreach ($ids as $type => $typeIds) {
                if ($typeIds->isNotEmpty()) {
                    $q->orWhere(function ($sub) use ($type, $typeIds) {
                        $sub->where('documentable_type', $type)->whereIn('documentable_id', $typeIds);
                    });
                }
            }
        });

        return response()->json($query->with('uploader')->orderBy('created_at', 'desc')->paginate(20));
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json($document->load(['documentable', 'uploader']));
    }

    public function download(Request $request, Document $document): Response
    {
        if (!$this->accesAutorise($request->user(), $document)) {
            abort(403, 'Accès refusé');
        }

        if (!Storage::exists($document->chemin_fichier)) {
            abort(404, 'Fichier introuvable');
        }

        return response(Storage::get($document->chemin_fichier), 200)
            ->header('Content-Type', Storage::mimeType($document->chemin_fichier) ?? 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $document->nom_original . '"');
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        if (!$this->accesAutorise($request->user(), $document)) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        Storage::delete($document->chemin_fichier);
        $document->delete();

        return response()->json(null, 204);
    }

    private function accesAutorise($user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $related = $document->documentable;

        if (!$related) {
            return false;
        }

        return match ($related::class) {
            Parcelle::class => $related->proprietaireActuel?->user_id === $user->id,
            DemandeAchat::class => in_array($user->id, [$related->acheteur_id, $related->vendeur_id, $related->notaire_id]),
            DossierTransaction::class => in_array($user->id, [$related->vendeur_id, $related->acheteur_id, $related->notaire_id]),
            Mission::class => in_array($user->id, [$related->citoyen_id, $related->geometre_id]),
            default => false,
        };
    }
}
