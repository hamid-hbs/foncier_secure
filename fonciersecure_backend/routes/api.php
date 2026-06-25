<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockchainController;
use App\Http\Controllers\Api\CartographieController;
use App\Http\Controllers\Api\DemandeAchatController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DossierTransactionController;
use App\Http\Controllers\Api\FactureController;
use App\Http\Controllers\Api\LocalisationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ObservatoireController;
use App\Http\Controllers\Api\ParcelleController;
use App\Http\Controllers\Api\ProfessionnelController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\SupportTicketController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════
// ROUTES PUBLIQUES
// ═══════════════════════════════════════════════

Route::get('login', fn () => response()->json(['message' => 'Non authentifié'], 401))->name('login');

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::get('localisation/communes', [LocalisationController::class, 'communes']);
Route::get('localisation/arrondissements/{commune}', [LocalisationController::class, 'arrondissements']);
Route::get('localisation/quartiers/{arrondissement}', [LocalisationController::class, 'quartiers']);

Route::get('parcelles', [ParcelleController::class, 'index']);
Route::get('parcelles/{parcelle}', [ParcelleController::class, 'show']);

Route::get('professionnels', [ProfessionnelController::class, 'index']);
Route::get('professionnels/{professionnel}', [ProfessionnelController::class, 'show']);

Route::get('cartographie/couches', [CartographieController::class, 'couches']);
Route::get('observatoire', [ObservatoireController::class, 'index']);
Route::get('blockchain/verifier', [BlockchainController::class, 'verifier']);

// ═══════════════════════════════════════════════
// ROUTES AUTHENTIFIEES
// ═══════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // ─── Sans validation requise ──
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::get('auth/dashboard', [AuthController::class, 'dashboard']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::delete('auth/account', [AuthController::class, 'deleteAccount']);

    // ─── Routes nécessitant compte approuvé ──
    Route::middleware('account.approved')->group(function () {

        // ─── Parcelles ──
        Route::middleware('role:citoyen')->group(function () {
            Route::post('parcelles', [ParcelleController::class, 'store']);
            Route::put('parcelles/{parcelle}', [ParcelleController::class, 'update']);
            Route::patch('parcelles/{parcelle}/statut', [ParcelleController::class, 'updateStatut']);
            Route::post('parcelles/{parcelle}/documents', [ParcelleController::class, 'uploadDocument']);
            Route::delete('parcelles/{parcelle}/documents/{documentId}', [ParcelleController::class, 'deleteDocument']);
            Route::get('parcelles/{parcelle}/historique', [ParcelleController::class, 'historique']);
        });

        // ─── Demandes d'achat ──
        Route::middleware('role:citoyen')->group(function () {
            Route::post('demandes-achat', [DemandeAchatController::class, 'store']);
            Route::post('demandes-achat/{demandeAchat}/solliciter-notaire', [DemandeAchatController::class, 'solliciterNotaire']);
            Route::post('demandes-achat/{demandeAchat}/documents', [DemandeAchatController::class, 'uploadDocument']);
        });

        Route::middleware('role:citoyen,notaire')->group(function () {
            Route::get('demandes-achat', [DemandeAchatController::class, 'index']);
            Route::get('demandes-achat/{demandeAchat}', [DemandeAchatController::class, 'show']);
            Route::get('demandes-achat/{demandeAchat}/messages', [DemandeAchatController::class, 'messages']);
            Route::post('demandes-achat/{demandeAchat}/messages', [DemandeAchatController::class, 'envoyerMessage']);
            Route::get('demandes-achat/{demandeAchat}/documents', [DemandeAchatController::class, 'documents']);
        });

        Route::middleware('role:citoyen,geometre,notaire')->group(function () {
            Route::patch('demandes-achat/{demandeAchat}/accepter', [DemandeAchatController::class, 'accepter']);
            Route::patch('demandes-achat/{demandeAchat}/refuser', [DemandeAchatController::class, 'refuser']);
        });

        // ─── Dossiers de transaction ──
        Route::middleware('role:notaire')->group(function () {
            Route::post('dossiers-transaction', [DossierTransactionController::class, 'store']);
            Route::post('dossiers-transaction/{dossierTransaction}/suspendre', [DossierTransactionController::class, 'suspendre']);
            Route::post('dossiers-transaction/{dossierTransaction}/reouvrir', [DossierTransactionController::class, 'reouvrir']);
            Route::post('dossiers-transaction/{dossierTransaction}/cloturer', [DossierTransactionController::class, 'cloturer']);
            Route::post('dossiers-transaction/{dossierTransaction}/documents', [DossierTransactionController::class, 'ajouterDocument']);
        });

        Route::middleware('role:citoyen,notaire')->group(function () {
            Route::get('dossiers-transaction', [DossierTransactionController::class, 'index']);
            Route::get('dossiers-transaction/{dossierTransaction}', [DossierTransactionController::class, 'show']);
            Route::get('dossiers-transaction/{dossierTransaction}/messages', [DossierTransactionController::class, 'messages']);
            Route::post('dossiers-transaction/{dossierTransaction}/messages', [DossierTransactionController::class, 'envoyerMessage']);
            Route::get('dossiers-transaction/{dossierTransaction}/documents', [DossierTransactionController::class, 'documents']);
        });

        Route::middleware('role:citoyen')->group(function () {
            Route::post('dossiers-transaction/{dossierTransaction}/valider', [DossierTransactionController::class, 'validerPartie']);
        });

        // ─── Missions (services géomètre) ──
        Route::middleware('role:citoyen,geometre')->group(function () {
            Route::get('missions', [MissionController::class, 'index']);
            Route::get('missions/{mission}', [MissionController::class, 'show']);
        });

        Route::middleware('role:citoyen')->group(function () {
            Route::post('missions', [MissionController::class, 'store']);
        });

        Route::middleware('role:geometre')->group(function () {
            Route::post('missions/{mission}/accepter', [MissionController::class, 'accepter']);
            Route::post('missions/{mission}/refuser', [MissionController::class, 'refuser']);
            Route::post('missions/{mission}/rapport', [MissionController::class, 'deposerRapport']);
        });

        // ─── Rendez-vous ──
        Route::middleware('role:notaire,geometre')->group(function () {
            Route::post('rendez-vous', [RendezVousController::class, 'store']);
            Route::patch('rendez-vous/{rendezVous}', [RendezVousController::class, 'updateStatut']);
        });

        Route::middleware('role:citoyen,notaire,geometre')->group(function () {
            Route::get('rendez-vous', [RendezVousController::class, 'index']);
            Route::post('rendez-vous/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer']);
        });

        // ─── Factures ──
        Route::middleware('role:notaire,geometre')->group(function () {
            Route::post('factures', [FactureController::class, 'store']);
            Route::put('factures/{facture}', [FactureController::class, 'update']);
            Route::post('factures/{facture}/envoyer', [FactureController::class, 'envoyer']);
            Route::post('factures/{facture}/payer', [FactureController::class, 'marquerPayee']);
            Route::get('factures/{facture}/pdf', [FactureController::class, 'telechargerPdf']);
        });

        Route::middleware('role:citoyen,notaire,geometre')->group(function () {
            Route::get('factures', [FactureController::class, 'index']);
            Route::get('factures/{facture}', [FactureController::class, 'show']);
        });

        // ─── Notifications ──
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::get('notifications/non-lues', [NotificationController::class, 'unreadCount']);

        // ─── Avis ──
        Route::middleware('role:citoyen')->group(function () {
            Route::post('professionnels/{professionnel}/avis', [ProfessionnelController::class, 'donnerAvis']);
            Route::get('professionnels/{professionnel}/statistiques', [ProfessionnelController::class, 'statistiques']);
            Route::put('professionnels/{professionnel}', [ProfessionnelController::class, 'updateProfile']);
        });

        // ─── Documents ──
        Route::get('documents', [DocumentController::class, 'index']);
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);
        Route::get('documents/{document}', [DocumentController::class, 'show']);
        Route::delete('documents/{document}', [DocumentController::class, 'destroy']);

        // ─── Messages directs ──
        Route::get('messages/conversations', [MessageController::class, 'conversations']);
        Route::get('messages/conversations/{contact}', [MessageController::class, 'conversation']);
        Route::post('messages', [MessageController::class, 'envoyer']);

        // ─── Support ──
        Route::get('support/tickets', [SupportTicketController::class, 'index']);
        Route::post('support/tickets', [SupportTicketController::class, 'store']);
        Route::get('support/tickets/{supportTicket}', [SupportTicketController::class, 'show']);

        Route::middleware('role:admin')->group(function () {
            Route::post('support/tickets/{supportTicket}/repondre', [SupportTicketController::class, 'repondre']);
            Route::patch('support/tickets/{supportTicket}/statut', [SupportTicketController::class, 'updateStatut']);
        });

        // ─── Admin ──
        Route::middleware('role:admin')->group(function () {
            Route::get('admin/dashboard', [AdminController::class, 'dashboard']);
            Route::get('admin/users', [AdminController::class, 'users']);
            Route::post('admin/users', [AdminController::class, 'createUser']);
            Route::get('admin/users/pending', [AdminController::class, 'pendingUsers']);
            Route::patch('admin/users/{user}/approve', [AdminController::class, 'approveUser']);
            Route::patch('admin/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus']);
            Route::patch('admin/users/{user}/role', [AdminController::class, 'updateUserRole']);
            Route::post('admin/professionnels', [AdminController::class, 'createProfessionnel']);

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

    }); // fin account.approved
});
