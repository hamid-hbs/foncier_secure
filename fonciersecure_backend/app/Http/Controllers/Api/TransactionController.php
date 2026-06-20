<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AvisProfessionnel;
use App\Models\DossierActivite;
use App\Models\DossierDocument;
use App\Models\DossierIntervenant;
use App\Models\DossierMessage;
use App\Models\DossierTransaction;
use App\Models\InterventionGeometre;
use App\Models\Parcelle;
use App\Models\Professionnel;
use App\Models\User;
use App\Models\ValidationDossier;
use App\Models\VerificationIdentite;
use App\Helpers\EncryptionHelper;
use App\Services\AnalyseDocumentaireService;
use App\Services\BlockchainService;
use App\Services\IndiceConfianceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DossierTransaction::with(['vendeur', 'acheteur', 'notaire', 'parcelle', 'intervenants.user']);

        $user = $request->user();

        if ($user->role === 'notaire') {
            $query->where('notaire_id', $user->id);
        } elseif ($user->role === 'citoyen') {
            $query->where(function ($q) use ($user) {
                $q->where('vendeur_id', $user->id)
                    ->orWhere('acheteur_id', $user->id);
            });
        } elseif ($user->role === 'geometre') {
            $query->where(function ($q) use ($user) {
                $q->whereHas('intervenants', fn($q2) => $q2->where('user_id', $user->id))
                    ->orWhere('vendeur_id', $user->id)
                    ->orWhere('acheteur_id', $user->id);
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('titre', 'like', "%{$s}%"));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'notaire') {
            return response()->json(['message' => 'Seul un notaire peut creer un dossier'], 403);
        }

        $validated = $request->validate([
            'parcelle_id' => 'required|exists:parcelles,id',
            'vendeur_id' => 'required|exists:users,id',
            'acheteur_id' => 'required|exists:users,id',
            'titre' => 'required|string|max:200',
        ]);

        $parcelle = Parcelle::findOrFail($validated['parcelle_id']);
        if ($parcelle->proprietaire_id !== (int) $validated['vendeur_id']) {
            return response()->json(['message' => 'Le vendeur doit etre le proprietaire de la parcelle'], 400);
        }

        if ($parcelle->statut !== 'en_demande') {
            return response()->json(['message' => 'La parcelle doit etre en demande'], 400);
        }

        $dossier = DossierTransaction::create([
            'vendeur_id' => $validated['vendeur_id'],
            'acheteur_id' => $validated['acheteur_id'],
            'notaire_id' => $user->id,
            'parcelle_id' => $validated['parcelle_id'],
            'titre' => $validated['titre'],
            'statut' => 'en_attente',
        ]);

        DossierActivite::create([
            'dossier_id' => $dossier->id,
            'user_id' => $user->id,
            'action' => 'Dossier ouvert par le notaire, en attente de validation des parties',
        ]);

        return response()->json($dossier->load(['vendeur', 'acheteur', 'notaire', 'parcelle']), 201);
    }

    public function validerPartie(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $user = $request->user();

        if (!$dossierTransaction->estEnAttente()) {
            return response()->json(['message' => 'Le dossier n\'est pas en attente de validation'], 400);
        }

        $estPartie = $user->id === $dossierTransaction->vendeur_id
            || $user->id === $dossierTransaction->acheteur_id;

        if (!$estPartie) {
            return response()->json(['message' => 'Seul le vendeur ou l\'acheteur peut valider'], 403);
        }

        $existe = $dossierTransaction->validations()
            ->where('user_id', $user->id)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Vous avez deja valide ce dossier'], 409);
        }

        ValidationDossier::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $user->id,
        ]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $user->id,
            'action' => ($user->id === $dossierTransaction->vendeur_id ? 'Vendeur' : 'Acheteur') . ' a valide le dossier',
        ]);

        if ($dossierTransaction->estValideParLesDeux()) {
            $dossierTransaction->update(['statut' => 'cree']);
            $dossierTransaction->parcelle->update(['statut' => 'en_transaction']);

            DossierActivite::create([
                'dossier_id' => $dossierTransaction->id,
                'user_id' => $dossierTransaction->notaire_id,
                'action' => 'Dossier confirme par les deux parties',
            ]);

            app(BlockchainService::class)->log(
                'transaction_created',
                $dossierTransaction->notaire_id,
                'transaction',
                $dossierTransaction->id,
                ['titre' => $dossierTransaction->titre, 'parcelle_id' => $dossierTransaction->parcelle_id]
            );

            return response()->json([
                'message' => 'Dossier valide et cree avec succes',
                'dossier' => $dossierTransaction->fresh()->load(['vendeur', 'acheteur', 'notaire', 'parcelle']),
            ]);
        }

        return response()->json([
            'message' => 'Validation enregistree, en attente de l\'autre partie',
            'dossier' => $dossierTransaction->fresh()->load('validations.user'),
        ]);
    }

    public function show(DossierTransaction $dossierTransaction): JsonResponse
    {
        $dossierTransaction->load([
            'vendeur',
            'acheteur',
            'notaire',
            'parcelle.commune',
            'parcelle.arrondissement',
            'parcelle.quartier',
            'intervenants.user',
            'documents.uploader',
            'activites.user',
            'messages.sender',
            'verificationsIdentite.user',
            'verificationsIdentite.verifiePar',
            'rendezVous.confirmations.user',
            'assignationsGeometre.geometre',
            'assignationsGeometre.assigneur',
            'factures',
        ]);

        return response()->json($dossierTransaction);
    }

    public function assignerGeometre(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire responsable peut assigner'], 403);
        }

        $validated = $request->validate([
            'geometre_id' => 'required|exists:users,id',
            'mission' => 'nullable|string|max:255',
        ]);

        $geometre = User::findOrFail($validated['geometre_id']);
        if ($geometre->role !== 'geometre') {
            return response()->json(['message' => 'L\'utilisateur doit etre un geometre'], 400);
        }

        $intervention = InterventionGeometre::create([
            'dossier_id' => $dossierTransaction->id,
            'geometre_id' => $geometre->id,
            'mission' => $validated['mission'] ?? 'Verification technique',
            'assigne_par' => $request->user()->id,
            'statut' => 'assigne',
        ]);

        if ($dossierTransaction->statut === 'cree' || $dossierTransaction->statut === 'en_verification') {
            $dossierTransaction->update(['statut' => 'geometre_assigne']);
        }

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Geometre assigne: ' . ($geometre->nom ?? $geometre->email),
        ]);

        app(BlockchainService::class)->log(
            'geometre_assigne',
            $request->user()->id,
            'transaction',
            $dossierTransaction->id,
            ['geometre_id' => $geometre->id]
        );

        return response()->json($intervention->load('geometre'), 201);
    }

    public function rapportGeometre(Request $request, DossierTransaction $dossierTransaction, InterventionGeometre $intervention): JsonResponse
    {
        if ($intervention->geometre_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le geometre assigne peut deposer'], 403);
        }

        $validated = $request->validate([
            'avis' => 'required|in:favorable,defavorable',
            'commentaire' => 'nullable|string',
            'rapport' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('rapport');
        $content = file_get_contents($file->getRealPath());
        $path = 'rapports-geometres/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $intervention->update([
            'statut' => 'rapport_recu',
            'avis' => $validated['avis'],
            'commentaire' => $validated['commentaire'] ?? null,
            'rapport_path' => $path,
            'completed_at' => now(),
        ]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Rapport geometre depose - Avis: ' . $validated['avis'],
        ]);

        app(BlockchainService::class)->log(
            'rapport_geometre_' . $validated['avis'],
            $request->user()->id,
            'transaction',
            $dossierTransaction->id,
            ['avis' => $validated['avis']]
        );

        return response()->json($intervention);
    }

    public function verifierIdentite(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire peut verifier'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'statut' => 'required|in:verifie,rejete',
            'document_verifie' => 'required|string|max:255',
        ]);

        $verif = VerificationIdentite::updateOrCreate(
            [
                'dossier_id' => $dossierTransaction->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'document_verifie' => $validated['document_verifie'],
                'statut' => $validated['statut'],
                'verifie_par' => $request->user()->id,
            ]
        );

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Identite verifiée pour ' . $validated['user_id'] . ': ' . $validated['statut'],
        ]);

        return response()->json($verif->load(['user', 'verifiePar']));
    }

    public function validerDossier(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire peut valider'], 403);
        }

        if (!in_array($dossierTransaction->statut, ['en_verification', 'rendezvous_planifie'])) {
            return response()->json(['message' => 'Le dossier doit etre en verification ou rendez-vous planifie'], 400);
        }

        // Delegue a avancerEtape pour centraliser la logique
        return $this->avancerEtape($request, $dossierTransaction);
    }

    public function avancerEtape(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire responsable peut avancer'], 403);
        }

        $statusFlow = ['cree', 'en_verification', 'geometre_assigne', 'rendezvous_planifie', 'valide', 'acte_signe', 'mutation_en_cours', 'cloture'];

        $currentIndex = array_search($dossierTransaction->statut, $statusFlow);
        if ($currentIndex === false || $currentIndex >= count($statusFlow) - 1) {
            return response()->json(['message' => 'Impossible d\'avancer le dossier'], 400);
        }

        $newStatus = $statusFlow[$currentIndex + 1];

        // Si aucun géomètre n'est assigné, sauter 'geometre_assigne' mais garder 'rendezvous_planifie'
        if ($dossierTransaction->statut === 'en_verification') {
            $aGeometre = $dossierTransaction->assignationsGeometre()
                ->where('statut', '!=', 'annule')
                ->exists();
            if (!$aGeometre) {
                $newStatus = 'rendezvous_planifie';
            }
        }

        // Vérifications notaire avant de passer à 'valide'
        if ($newStatus === 'valide') {
            $allIdentitesVerifiees = $dossierTransaction->verificationsIdentite()
                ->where('statut', 'verifie')
                ->count() >= 2;

            if (!$allIdentitesVerifiees) {
                return response()->json(['message' => 'Les identites du vendeur et de l\'acheteur doivent etre verifiees'], 400);
            }

            app(AnalyseDocumentaireService::class)->analyser(
                $dossierTransaction->parcelle->verifications()->firstOrCreate(
                    ['parcelle_id' => $dossierTransaction->parcelle_id],
                    ['demandeur_id' => $dossierTransaction->vendeur_id, 'titre' => 'Analyse auto - ' . $dossierTransaction->titre, 'statut' => 'terminee']
                )
            );
        }

        $data = ['statut' => $newStatus];

        if ($newStatus === 'cloture') {
            $data['closed_at'] = now();
            $dossierTransaction->parcelle->update(['statut' => 'vendue']);

            $service = app(IndiceConfianceService::class);
            if ($dossierTransaction->vendeur_id) {
                $service->ajouterPoints($dossierTransaction->vendeur_id, 15, 'Transaction reussie');
            }
            if ($dossierTransaction->acheteur_id) {
                $service->ajouterPoints($dossierTransaction->acheteur_id, 15, 'Transaction reussie');
            }
            if ($dossierTransaction->notaire_id) {
                $service->ajouterPoints($dossierTransaction->notaire_id, 10, 'Transaction notariale reussie');
            }
        }

        $dossierTransaction->update($data);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Dossier avance a: ' . $newStatus,
        ]);

        app(BlockchainService::class)->log(
            'transaction_status_' . $newStatus,
            $request->user()->id,
            'transaction',
            $dossierTransaction->id,
            ['ancien_statut' => $statusFlow[$currentIndex], 'nouveau_statut' => $newStatus]
        );

        return response()->json($dossierTransaction->load(['vendeur', 'acheteur', 'notaire', 'parcelle']));
    }

    public function genererActeVente(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire peut generer l\'acte'], 403);
        }

        if ($dossierTransaction->statut !== 'valide') {
            return response()->json(['message' => 'Le dossier doit etre valide pour generer l\'acte'], 400);
        }

        $dossierTransaction->load(['vendeur', 'acheteur', 'notaire', 'parcelle.commune', 'parcelle.arrondissement', 'parcelle.quartier']);

        $pdf = Pdf::loadView('rapports.transaction', ['dossier' => $dossierTransaction]);

        $content = $pdf->output();
        $path = 'actes/acte_vente_' . $dossierTransaction->id . '.pdf';
        EncryptionHelper::storeEncrypted($path, $content);

        $doc = DossierDocument::create([
            'dossier_id' => $dossierTransaction->id,
            'uploaded_by' => $request->user()->id,
            'nom_fichier' => 'acte_vente_' . $dossierTransaction->id . '.pdf',
            'chemin_fichier' => $path,
            'hash_sha256' => hash('sha256', $content),
            'type_document' => 'acte_vente',
        ]);

        $dossierTransaction->update(['statut' => 'acte_signe']);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Acte de vente genere et signe',
        ]);

        return response()->json($doc, 201);
    }

    public function exportPdf(DossierTransaction $dossierTransaction)
    {
        $dossierTransaction->load([
            'vendeur', 'acheteur', 'notaire', 'parcelle',
            'intervenants.user', 'documents.uploader',
            'activites.user', 'messages.sender',
        ]);

        $pdf = Pdf::loadView('rapports.transaction', ['dossier' => $dossierTransaction]);
        return $pdf->download('dossier_transaction_' . $dossierTransaction->id . '.pdf');
    }

    // --- Methods preserved from v6 with minor updates ---

    public function inviter(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire responsable peut inviter'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email|exists:users,email',
            'role_dossier' => 'required|in:notaire,geometre',
        ]);

        if (!$request->filled('user_id') && !$request->filled('email')) {
            return response()->json(['message' => 'Fournissez user_id ou email'], 400);
        }

        $userId = $validated['user_id'] ?? User::where('email', $validated['email'])->first()->id;

        $exists = DossierIntervenant::where('dossier_id', $dossierTransaction->id)
            ->where('user_id', $userId)->exists();

        if ($exists) {
            return response()->json(['message' => 'Cet utilisateur est deja invite sur ce dossier'], 409);
        }

        $intervenant = DossierIntervenant::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $userId,
            'role_dossier' => $validated['role_dossier'],
            'invite_par' => $request->user()->id,
        ]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Invitation envoyee a ' . ($intervenant->user->nom ?? 'utilisateur'),
        ]);

        return response()->json($intervenant->load('user'), 201);
    }

    public function intervenants(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json($dossierTransaction->intervenants()->with('user')->get());
    }

    public function accepterInvitation(Request $request, DossierTransaction $dossierTransaction, DossierIntervenant $intervenant): JsonResponse
    {
        if ($intervenant->dossier_id !== $dossierTransaction->id) abort(404);
        if ($intervenant->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul l\'utilisateur invite peut accepter'], 403);
        }

        $intervenant->update(['accepted_at' => now()]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Invitation acceptee par ' . ($intervenant->user->nom ?? 'utilisateur'),
        ]);

        return response()->json($intervenant->load('user'));
    }

    public function refuserInvitation(Request $request, DossierTransaction $dossierTransaction, DossierIntervenant $intervenant): JsonResponse
    {
        if ($intervenant->dossier_id !== $dossierTransaction->id) abort(404);
        if ($intervenant->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul l\'utilisateur invite peut refuser'], 403);
        }

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Invitation refusee par ' . ($intervenant->user->nom ?? 'utilisateur'),
        ]);

        $intervenant->delete();
        return response()->json(['message' => 'Invitation refusee']);
    }

    public function ajouterDocument(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $validated = $request->validate([
            'type_document' => 'nullable|string|max:50',
            'fichier' => 'required|file|max:20480',
        ]);

        $file = $request->file('fichier');
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);
        $path = 'dossiers/' . $dossierTransaction->id . '/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $document = DossierDocument::create([
            'dossier_id' => $dossierTransaction->id,
            'uploaded_by' => $request->user()->id,
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'hash_sha256' => $hash,
            'type_document' => $validated['type_document'] ?? 'autre',
        ]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Document ajoute: ' . $file->getClientOriginalName(),
        ]);

        return response()->json($document, 201);
    }

    public function documents(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json($dossierTransaction->documents()->with('uploader')->get());
    }

    public function telechargerDocument(Request $request, DossierTransaction $dossierTransaction, DossierDocument $dossierDocument)
    {
        if ($dossierDocument->dossier_id !== $dossierTransaction->id) abort(404);

        if (!file_exists(storage_path('app/' . $dossierDocument->chemin_fichier))) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        $content = EncryptionHelper::readEncrypted($dossierDocument->chemin_fichier);
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $dossierDocument->nom_fichier, ['Content-Type' => 'application/octet-stream']);
    }

    public function envoyerMessage(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        $validated = $request->validate(['contenu' => 'required|string']);

        $message = DossierMessage::create([
            'dossier_id' => $dossierTransaction->id,
            'sender_id' => $request->user()->id,
            'contenu' => $validated['contenu'],
        ]);

        return response()->json($message, 201);
    }

    public function messages(DossierTransaction $dossierTransaction): JsonResponse
    {
        return response()->json($dossierTransaction->messages()->with('sender')->get());
    }

    public function suspendre(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire responsable peut suspendre'], 403);
        }

        if ($dossierTransaction->statut === 'cloture' || $dossierTransaction->statut === 'suspendu') {
            return response()->json(['message' => 'Impossible de suspendre un dossier ' . $dossierTransaction->statut], 400);
        }

        $validated = $request->validate([
            'motif' => 'required|string|max:1000',
        ]);

        $dossierTransaction->update([
            'statut' => 'suspendu',
            'motif_suspension' => $validated['motif'],
        ]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Dossier suspendu: ' . $validated['motif'],
        ]);

        app(BlockchainService::class)->log(
            'transaction_suspendue',
            $request->user()->id,
            'transaction',
            $dossierTransaction->id,
            ['motif' => $validated['motif']]
        );

        return response()->json($dossierTransaction->fresh()->load(['vendeur', 'acheteur', 'notaire', 'parcelle']));
    }

    public function reouvrir(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->notaire_id !== $request->user()->id) {
            return response()->json(['message' => 'Seul le notaire responsable peut réouvrir'], 403);
        }

        if ($dossierTransaction->statut !== 'suspendu') {
            return response()->json(['message' => 'Seul un dossier suspendu peut être réouvert'], 400);
        }

        $dossierTransaction->update([
            'statut' => 'en_verification',
            'motif_suspension' => null,
        ]);

        DossierActivite::create([
            'dossier_id' => $dossierTransaction->id,
            'user_id' => $request->user()->id,
            'action' => 'Dossier réouvert après suspension',
        ]);

        app(BlockchainService::class)->log(
            'transaction_reouverte',
            $request->user()->id,
            'transaction',
            $dossierTransaction->id
        );

        return response()->json($dossierTransaction->fresh()->load(['vendeur', 'acheteur', 'notaire', 'parcelle']));
    }

    public function noterProfessionnel(Request $request, DossierTransaction $dossierTransaction): JsonResponse
    {
        if ($dossierTransaction->statut !== 'cloture') {
            return response()->json(['message' => 'Le dossier doit être clôturé pour noter'], 400);
        }

        $user = $request->user();
        $estPartie = $user->id === $dossierTransaction->vendeur_id
            || $user->id === $dossierTransaction->acheteur_id;

        if (!$estPartie) {
            return response()->json(['message' => 'Seul le vendeur ou l\'acheteur peut noter'], 403);
        }

        $validated = $request->validate([
            'professionnel_user_id' => 'required|exists:users,id',
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $professionnel = Professionnel::where('user_id', $validated['professionnel_user_id'])->first();
        if (!$professionnel) {
            return response()->json(['message' => 'Ce professionnel n\'existe pas'], 404);
        }

        $existe = AvisProfessionnel::where('professionnel_id', $professionnel->id)
            ->where('auteur_id', $user->id)
            ->where('dossier_id', $dossierTransaction->id)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Vous avez déjà noté ce professionnel pour ce dossier'], 409);
        }

        $avis = AvisProfessionnel::create([
            'professionnel_id' => $professionnel->id,
            'auteur_id' => $user->id,
            'note' => $validated['note'],
            'commentaire' => $validated['commentaire'] ?? null,
            'dossier_id' => $dossierTransaction->id,
        ]);

        $professionnel->recalculerNote();

        return response()->json($avis, 201);
    }
}
