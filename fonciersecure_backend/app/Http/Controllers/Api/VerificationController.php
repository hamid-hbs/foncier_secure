<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyseAutomatique;
use App\Models\InterventionGeometre;
use App\Models\User;
use App\Models\Verification;
use App\Helpers\EncryptionHelper;
use App\Services\AnalyseDocumentaireService;
use App\Services\BlockchainService;
use App\Services\IndiceConfianceService;
use App\Services\RisqueService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Verification::with(['demandeur', 'parcelle', 'documents', 'analyses', 'intervention.geometre']);

        if ($request->user()->role === 'citoyen') {
            $query->where('demandeur_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('niveau_risque')) {
            $query->where('niveau_risque', $request->niveau_risque);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parcelle_id' => 'required|exists:parcelles,id',
            'titre' => 'required|string|max:200',
            'documents' => 'nullable|array',
            'documents.*.type' => 'required|in:tf,adc,plan_topo,certificat_admin,photo,autre',
            'documents.*.fichier' => 'required|file|mimes:pdf,jpg,png|max:20480',
        ]);

        $verification = Verification::create([
            'parcelle_id' => $validated['parcelle_id'],
            'demandeur_id' => $request->user()->id,
            'titre' => $validated['titre'],
            'statut' => 'soumise',
        ]);

        if ($request->has('documents')) {
            foreach ($request->file('documents', []) as $i => $docFile) {
                $type = $validated['documents'][$i]['type'];
                $content = file_get_contents($docFile->getRealPath());
                $hash = hash('sha256', $content);
                $path = 'verifications/' . $verification->id . '/' . uniqid() . '.' . $docFile->extension();
                EncryptionHelper::storeEncrypted($path, $content);

                $verification->documents()->create([
                    'type_document' => $type,
                    'nom_fichier' => $docFile->getClientOriginalName(),
                    'chemin_fichier' => $path,
                    'hash_sha256' => $hash,
                    'taille' => $docFile->getSize(),
                    'uploaded_at' => now(),
                ]);
            }
        }

        $verification->update(['statut' => 'en_analyse']);
        app(AnalyseDocumentaireService::class)->analyser($verification);
        app(RisqueService::class)->calculer($verification);

        $this->genererRapport($verification);

        app(BlockchainService::class)->log(
            'verification_created',
            $request->user()->id,
            'verification',
            $verification->id,
            ['titre' => $verification->titre, 'score' => $verification->score_risque]
        );

        return response()->json(
            $verification->load(['documents', 'analyses', 'intervention']),
            201
        );
    }

    public function show(Verification $verification): JsonResponse
    {
        $verification->load(['demandeur', 'parcelle', 'documents', 'analyses', 'intervention.geometre']);

        return response()->json($verification);
    }

    public function downloadRapport(Verification $verification)
    {
        if (!$verification->rapport_path || !file_exists(storage_path('app/' . $verification->rapport_path))) {
            return response()->json(['message' => 'Rapport non trouvé'], 404);
        }

        $content = EncryptionHelper::readEncrypted($verification->rapport_path);
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'rapport_' . $verification->id . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    public function solliciterGeometre(Request $request, Verification $verification): JsonResponse
    {
        $validated = $request->validate([
            'geometre_id' => 'required|exists:users,id',
        ]);

        $geometre = User::find($validated['geometre_id']);
        if ($geometre->role !== 'geometre') {
            return response()->json(['message' => 'L\'utilisateur doit être un géomètre'], 400);
        }

        $intervention = InterventionGeometre::create([
            'verification_id' => $verification->id,
            'geometre_id' => $geometre->id,
            'statut' => 'assigne',
        ]);

        app(BlockchainService::class)->log(
            'geometre_sollicite',
            $request->user()->id,
            'verification',
            $verification->id,
            ['geometre_id' => $geometre->id]
        );

        return response()->json($intervention, 201);
    }

    public function missionsGeometre(Request $request): JsonResponse
    {
        $interventions = InterventionGeometre::with(['verification.demandeur', 'verification.documents'])
            ->where('geometre_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($interventions);
    }

    public function rapportGeometre(Request $request, Verification $verification): JsonResponse
    {
        $validated = $request->validate([
            'avis' => 'required|in:favorable,defavorable',
            'commentaire' => 'nullable|string',
            'rapport' => 'required|file|mimes:pdf|max:20480',
        ]);

        $intervention = $verification->intervention;
        if (!$intervention || $intervention->geometre_id !== $request->user()->id) {
            return response()->json(['message' => 'Intervention non trouvée'], 404);
        }

        $file = $request->file('rapport');
        $content = file_get_contents($file->getRealPath());
        $path = 'rapports-geometres/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $intervention->update([
            'statut' => 'termine',
            'avis' => $validated['avis'],
            'commentaire' => $validated['commentaire'] ?? null,
            'rapport_path' => $path,
        ]);

        if ($validated['avis'] === 'favorable') {
            app(RisqueService::class)->calculer($verification);
        }

        app(BlockchainService::class)->log(
            'rapport_geometre_' . $validated['avis'],
            $request->user()->id,
            'verification',
            $verification->id,
            ['avis' => $validated['avis']]
        );

        return response()->json($intervention);
    }

    public function validerVerification(Request $request, Verification $verification): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $verification->update(['statut' => 'terminee']);

        app(IndiceConfianceService::class)->ajouterPoints($verification->demandeur_id, 5, 'Documents complets et cohérents');

        app(RisqueService::class)->calculer($verification);
        $this->genererRapport($verification);

        app(BlockchainService::class)->log(
            'verification_validee',
            $request->user()->id,
            'verification',
            $verification->id,
            ['statut' => 'terminee']
        );

        return response()->json(
            $verification->load(['documents', 'analyses', 'intervention'])
        );
    }

    private function genererRapport(Verification $verification): void
    {
        $pdf = Pdf::loadView('rapports.verification', [
            'verification' => $verification->load(['demandeur', 'documents', 'analyses', 'intervention']),
        ]);

        $content = $pdf->output();
        $path = 'rapports/verification_' . $verification->id . '.pdf';
        EncryptionHelper::storeEncrypted($path, $content);

        $verification->update(['rapport_path' => $path]);
    }
}
