<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockchainController;
use App\Http\Controllers\Api\CartographieController;
use App\Http\Controllers\Api\CoffreController;
use App\Http\Controllers\Api\DemandeAchatController;
use App\Http\Controllers\Api\FactureController;
use App\Http\Controllers\Api\LocalisationController;
use App\Http\Controllers\Api\ServiceGeometreController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ObservatoireController;
use App\Http\Controllers\Api\ParcelleController;
use App\Http\Controllers\Api\ProfessionnelController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════
// ROUTES PUBLIQUES (sans auth)
// ═══════════════════════════════════════════════

Route::get('login', fn () => response()->json(['message' => 'Non authentifie'], 401))->name('login');

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgot-password', [AuthController::class, 'sendOtp']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

Route::get('localisation/communes', [LocalisationController::class, 'communes']);
Route::get('localisation/arrondissements/{commune}', [LocalisationController::class, 'arrondissements']);
Route::get('localisation/quartiers/{arrondissement}', [LocalisationController::class, 'quartiers']);

Route::get('parcelles', [ParcelleController::class, 'index']);
Route::get('parcelles/{parcelle}', [ParcelleController::class, 'show']);

Route::get('cartographie/couches', [CartographieController::class, 'couches']);

Route::get('observatoire', [ObservatoireController::class, 'index']);

Route::get('professionnels', [ProfessionnelController::class, 'index']);
Route::get('professionnels/{professionnel}', [ProfessionnelController::class, 'show']);

Route::get('blockchain/verifier', [BlockchainController::class, 'verifier']);

// ═══════════════════════════════════════════════
// ROUTES AUTHENTIFIEES (Sanctum)
// ═══════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ─── Tous les utilisateurs connectes ──
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/request-role', [AuthController::class, 'requestRole']);

    // ─── Notifications ──────────────────
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::get('notifications/non-lues', [NotificationController::class, 'unreadCount']);

    // ─── Demandes d'achat (tous) ───────
    Route::get('demandes-achat', [DemandeAchatController::class, 'index']);
    Route::get('demandes-achat/{demandeAchat}', [DemandeAchatController::class, 'show']);
    Route::get('demandes-achat/{demandeAchat}/messages', [DemandeAchatController::class, 'messages']);
    Route::post('demandes-achat/{demandeAchat}/messages', [DemandeAchatController::class, 'envoyerMessage']);

    // ─── Citoyen ──────────────────────────
    Route::middleware('role:citoyen')->group(function () {
        Route::post('parcelles', [ParcelleController::class, 'store']);
        Route::put('parcelles/{parcelle}', [ParcelleController::class, 'update']);
        Route::post('parcelles/{parcelle}/documents', [ParcelleController::class, 'uploadDocument']);
        Route::delete('parcelles/{parcelle}/documents/{documentId}', [ParcelleController::class, 'deleteDocument']);
        Route::patch('parcelles/{parcelle}/statut', [ParcelleController::class, 'updateStatut']);

        Route::post('demandes-achat', [DemandeAchatController::class, 'store']);
        Route::patch('demandes-achat/{demandeAchat}/repondre', [DemandeAchatController::class, 'repondre']);
        Route::post('demandes-achat/{demandeAchat}/documents', [DemandeAchatController::class, 'uploadDocument']);

        Route::post('professionnels/{professionnel}/avis', [ProfessionnelController::class, 'donnerAvis']);

        Route::get('coffre/dossiers', [CoffreController::class, 'indexDossiers']);
        Route::post('coffre/dossiers', [CoffreController::class, 'creerDossier']);
        Route::post('coffre/dossiers/{coffreDossier}/documents', [CoffreController::class, 'uploadDocument']);
        Route::get('coffre/documents/{coffreDocument}/telecharger', [CoffreController::class, 'telechargerDocument']);
        Route::post('coffre/documents/{coffreDocument}/partager', [CoffreController::class, 'partagerDocument']);
        Route::get('coffre/documents/{coffreDocument}/integrite', [CoffreController::class, 'verifierIntegrite']);
    });

    // ─── Citoyen + Geometre ───────────────
    Route::middleware('role:citoyen,geometre')->group(function () {
        Route::get('verifications', [VerificationController::class, 'index']);
        Route::get('verifications/{verification}', [VerificationController::class, 'show']);
        Route::get('verifications/{verification}/rapport', [VerificationController::class, 'downloadRapport']);
    });

    // ─── Geometre ─────────────────────────
    Route::middleware('role:geometre')->group(function () {
        Route::get('geometre/missions', [VerificationController::class, 'missionsGeometre']);
        Route::post('verifications/{verification}/rapport-geometre', [VerificationController::class, 'rapportGeometre']);
    });

    // ─── Transactions (tous les roles lies) ─
    Route::middleware('role:citoyen,notaire,geometre')->group(function () {
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::get('transactions/{dossierTransaction}', [TransactionController::class, 'show']);
        Route::post('transactions/{dossierTransaction}/messages', [TransactionController::class, 'envoyerMessage']);
        Route::get('transactions/{dossierTransaction}/messages', [TransactionController::class, 'messages']);
        Route::post('transactions/{dossierTransaction}/documents', [TransactionController::class, 'ajouterDocument']);
        Route::get('transactions/{dossierTransaction}/documents', [TransactionController::class, 'documents']);
        Route::get('transactions/{dossierTransaction}/documents/{dossierDocument}/telecharger', [TransactionController::class, 'telechargerDocument']);
        Route::post('transactions/{dossierTransaction}/valider-partie', [TransactionController::class, 'validerPartie']);
    });

    // ─── Notaire (gestion des dossiers) ───
    Route::middleware('role:notaire')->group(function () {
        Route::post('transactions', [TransactionController::class, 'store']);
        Route::post('transactions/{dossierTransaction}/assigner-geometre', [TransactionController::class, 'assignerGeometre']);
        Route::post('transactions/{dossierTransaction}/verifier-identite', [TransactionController::class, 'verifierIdentite']);
        Route::post('transactions/{dossierTransaction}/valider', [TransactionController::class, 'validerDossier']);
        Route::post('transactions/{dossierTransaction}/avancer', [TransactionController::class, 'avancerEtape']);
        Route::post('transactions/{dossierTransaction}/generer-acte', [TransactionController::class, 'genererActeVente']);
        Route::post('transactions/{dossierTransaction}/inviter', [TransactionController::class, 'inviter']);
        Route::get('transactions/{dossierTransaction}/intervenants', [TransactionController::class, 'intervenants']);
        Route::post('transactions/{dossierTransaction}/export-pdf', [TransactionController::class, 'exportPdf']);

        // Factures
        Route::get('transactions/{dossierTransaction}/factures', [FactureController::class, 'index']);
        Route::post('transactions/{dossierTransaction}/factures', [FactureController::class, 'store']);
        Route::get('factures/{facture}', [FactureController::class, 'show']);
        Route::put('factures/{facture}', [FactureController::class, 'update']);
        Route::post('factures/{facture}/envoyer', [FactureController::class, 'envoyer']);
        Route::post('factures/{facture}/payer', [FactureController::class, 'marquerPayee']);
        Route::get('factures/{facture}/pdf', [FactureController::class, 'telechargerPdf']);

        // Suspension / réouverture
        Route::post('transactions/{dossierTransaction}/suspendre', [TransactionController::class, 'suspendre']);
        Route::post('transactions/{dossierTransaction}/reouvrir', [TransactionController::class, 'reouvrir']);

        // Rendez-vous (notaire peut modifier statut)
        Route::patch('rendez-vous/{rendezVous}', [RendezVousController::class, 'updateStatut']);
    });

    // ─── Service géomètre (citoyen → géomètre) ─
    Route::middleware('role:citoyen,geometre')->group(function () {
        Route::get('services-geometre', [ServiceGeometreController::class, 'index']);
        Route::get('services-geometre/{demandeServiceGeometre}', [ServiceGeometreController::class, 'show']);
    });
    Route::middleware('role:citoyen')->group(function () {
        Route::post('services-geometre', [ServiceGeometreController::class, 'store']);
    });
    Route::middleware('role:geometre')->group(function () {
        Route::post('services-geometre/{demandeServiceGeometre}/accepter', [ServiceGeometreController::class, 'accepter']);
        Route::post('services-geometre/{demandeServiceGeometre}/refuser', [ServiceGeometreController::class, 'refuser']);
        Route::post('services-geometre/{demandeServiceGeometre}/rapport', [ServiceGeometreController::class, 'deposerRapport']);
    });

    // ─── Rendez-vous (parties + notaire) ─
    Route::middleware('role:citoyen,notaire')->group(function () {
        Route::post('transactions/{dossierTransaction}/rendez-vous', [RendezVousController::class, 'store']);
        Route::get('transactions/{dossierTransaction}/rendez-vous', [RendezVousController::class, 'index']);
        Route::post('rendez-vous/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer']);
    });

    // ─── Notation (citoyen note notaire/géomètre après clôture) ─
    Route::middleware('role:citoyen')->group(function () {
        Route::post('transactions/{dossierTransaction}/noter', [TransactionController::class, 'noterProfessionnel']);
    });

    // ─── Geometre (rapport sur assignation) ─
    Route::middleware('role:geometre')->group(function () {
        Route::post('transactions/{dossierTransaction}/interventions/{intervention}/rapport', [TransactionController::class, 'rapportGeometre']);
    });

    // ─── Acceptation invitation ──────────
    Route::post('transactions/{dossierTransaction}/invitations/{intervenant}/accepter', [TransactionController::class, 'accepterInvitation']);
    Route::post('transactions/{dossierTransaction}/invitations/{intervenant}/refuser', [TransactionController::class, 'refuserInvitation']);

    // ─── Geometre + Notaire ───────────────
    Route::middleware('role:geometre,notaire')->group(function () {
        Route::put('professionnels/{professionnel}', [ProfessionnelController::class, 'updateProfile']);
    });

    // ─── Support Tickets (tous utilisateurs) ─
    Route::get('support/tickets', [SupportTicketController::class, 'index']);
    Route::post('support/tickets', [SupportTicketController::class, 'store']);
    Route::get('support/tickets/{supportTicket}', [SupportTicketController::class, 'show']);

    // ─── Support Tickets (admin) ──────────
    Route::middleware('role:admin')->group(function () {
        Route::post('support/tickets/{supportTicket}/repondre', [SupportTicketController::class, 'repondre']);
        Route::patch('support/tickets/{supportTicket}/statut', [SupportTicketController::class, 'updateStatut']);
    });

    // ─── Admin ────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('admin/users', [AdminController::class, 'users']);
        Route::patch('admin/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus']);
        Route::get('admin/role-requests', [AdminController::class, 'roleRequests']);
        Route::patch('admin/role-requests/{roleRequest}', [AdminController::class, 'approveRoleRequest']);

        Route::get('admin/localisation/communes', [LocalisationController::class, 'communes']);
        Route::post('admin/localisation/communes', [AdminController::class, 'createCommune']);
        Route::put('admin/localisation/communes/{commune}', [AdminController::class, 'updateCommune']);
        Route::delete('admin/localisation/communes/{commune}', [AdminController::class, 'deleteCommune']);
        Route::get('admin/localisation/arrondissements/{commune}', [LocalisationController::class, 'arrondissements']);
        Route::post('admin/localisation/arrondissements', [AdminController::class, 'createArrondissement']);
        Route::put('admin/localisation/arrondissements/{arrondissement}', [AdminController::class, 'updateArrondissement']);
        Route::delete('admin/localisation/arrondissements/{arrondissement}', [AdminController::class, 'deleteArrondissement']);
        Route::get('admin/localisation/quartiers/{arrondissement}', [LocalisationController::class, 'quartiers']);
        Route::post('admin/localisation/quartiers', [AdminController::class, 'createQuartier']);
        Route::put('admin/localisation/quartiers/{quartier}', [AdminController::class, 'updateQuartier']);
        Route::delete('admin/localisation/quartiers/{quartier}', [AdminController::class, 'deleteQuartier']);

        Route::get('blockchain', [BlockchainController::class, 'index']);
        Route::get('blockchain/module/{module}/{referenceId?}', [BlockchainController::class, 'historiqueModule']);
    });
});
