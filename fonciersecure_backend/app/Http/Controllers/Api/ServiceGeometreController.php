<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeServiceGeometre;
use App\Models\Parcelle;
use App\Helpers\EncryptionHelper;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceGeometreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'citoyen') {
            $query = DemandeServiceGeometre::where('citoyen_id', $user->id);
        } elseif ($user->role === 'geometre') {
            $query = DemandeServiceGeometre::where('geometre_id', $user->id);
        } else {
            $query = DemandeServiceGeometre::query();
        }

        return response()->json(
            $query->with(['citoyen', 'geometre', 'parcelle'])->latest()->paginate(15)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'geometre_id' => 'required|exists:users,id',
            'parcelle_id' => 'nullable|exists:parcelles,id',
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
        ]);

        $demande = DemandeServiceGeometre::create([
            'citoyen_id' => $request->user()->id,
            'geometre_id' => $validated['geometre_id'],
            'parcelle_id' => $validated['parcelle_id'] ?? null,
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'statut' => 'soumise',
        ]);

        return response()->json($demande->load(['citoyen', 'geometre']), 201);
    }

    public function show(DemandeServiceGeometre $demandeServiceGeometre): JsonResponse
    {
        return response()->json(
            $demandeServiceGeometre->load(['citoyen', 'geometre', 'parcelle'])
        );
    }

    public function accepter(Request $request, DemandeServiceGeometre $demandeServiceGeometre): JsonResponse
    {
        if ($demandeServiceGeometre->geometre_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le géomètre concerné peut accepter'], 403);
        }

        if ($demandeServiceGeometre->statut !== 'soumise') {
            return response()->json(['message' => 'Demande déjà traitée'], 400);
        }

        $demandeServiceGeometre->update(['statut' => 'acceptee']);

        return response()->json($demandeServiceGeometre->load(['citoyen', 'geometre']));
    }

    public function refuser(Request $request, DemandeServiceGeometre $demandeServiceGeometre): JsonResponse
    {
        if ($demandeServiceGeometre->geometre_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le géomètre concerné peut refuser'], 403);
        }

        if ($demandeServiceGeometre->statut !== 'soumise') {
            return response()->json(['message' => 'Demande déjà traitée'], 400);
        }

        $demandeServiceGeometre->update(['statut' => 'refusee']);

        return response()->json($demandeServiceGeometre);
    }

    public function deposerRapport(Request $request, DemandeServiceGeometre $demandeServiceGeometre): JsonResponse
    {
        if ($demandeServiceGeometre->geometre_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le géomètre peut déposer un rapport'], 403);
        }

        if ($demandeServiceGeometre->statut !== 'acceptee') {
            return response()->json(['message' => 'La demande doit être acceptée'], 400);
        }

        $validated = $request->validate([
            'rapport' => 'required|file|mimes:pdf|max:20480',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('rapport');
        $content = file_get_contents($file->getRealPath());
        $path = 'services-geometres/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $demandeServiceGeometre->update([
            'statut' => 'terminee',
            'rapport_path' => $path,
            'commentaire_geometre' => $validated['commentaire'] ?? null,
            'completed_at' => now(),
        ]);

        app(BlockchainService::class)->log(
            'service_geometre_termine',
            $request->user()->id,
            'service_geometre',
            $demandeServiceGeometre->id
        );

        return response()->json($demandeServiceGeometre->load(['citoyen', 'geometre', 'parcelle']));
    }
}
