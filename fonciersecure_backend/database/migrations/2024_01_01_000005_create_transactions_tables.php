<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendeur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('acheteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notaire_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parcelle_id')->constrained()->cascadeOnDelete();
            $table->string('titre', 200);
            $table->enum('statut', ['en_attente', 'cree', 'en_verification', 'geometre_assigne', 'rendezvous_planifie', 'valide', 'acte_signe', 'mutation_en_cours', 'suspendu', 'cloture'])->default('en_attente');
            $table->text('motif_suspension')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('validations_dossier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('valide_le')->useCurrent();
            $table->timestamps();
            $table->unique(['dossier_id', 'user_id']);
        });

        Schema::create('dossier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('hash_sha256');
            $table->string('type_document')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('dossier_intervenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_dossier');
            $table->foreignId('invite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dossier_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->timestamps();
        });

        Schema::create('dossier_activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->json('details')->nullable();
            $table->timestamps();
        });

        Schema::create('verifications_identite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_verifie');
            $table->string('statut');
            $table->foreignId('verifie_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->string('type');
            $table->dateTime('date_prevue');
            $table->string('lieu', 255)->nullable();
            $table->string('statut')->default('planifie');
            $table->timestamps();
        });

        Schema::create('confirmations_rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rendez_vous_id')->constrained('rendez_vous')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('est_confirme')->default(false);
            $table->timestamps();
            $table->unique(['rendez_vous_id', 'user_id']);
        });

        Schema::create('interventions_geometre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->unsignedBigInteger('verification_id')->nullable();
            $table->foreignId('geometre_id')->constrained('users')->cascadeOnDelete();
            $table->string('mission')->nullable();
            $table->foreignId('assigne_par')->constrained('users')->cascadeOnDelete();
            $table->string('statut')->default('assigne');
            $table->string('rapport_path')->nullable();
            $table->string('avis')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_transaction')->cascadeOnDelete();
            $table->foreignId('emetteur_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('montant', 12, 2);
            $table->text('description')->nullable();
            $table->string('statut')->default('brouillon');
            $table->timestamp('envoyee_le')->nullable();
            $table->timestamp('payee_le')->nullable();
            $table->timestamps();
        });

        Schema::create('avis_professionnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professionnel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('auteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers_transaction')->nullOnDelete();
            $table->tinyInteger('note');
            $table->string('commentaire', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis_professionnel');
        Schema::dropIfExists('factures');
        Schema::dropIfExists('interventions_geometre');
        Schema::dropIfExists('confirmations_rendez_vous');
        Schema::dropIfExists('rendez_vous');
        Schema::dropIfExists('verifications_identite');
        Schema::dropIfExists('dossier_activites');
        Schema::dropIfExists('dossier_messages');
        Schema::dropIfExists('dossier_intervenants');
        Schema::dropIfExists('dossier_documents');
        Schema::dropIfExists('validations_dossier');
        Schema::dropIfExists('dossiers_transaction');
    }
};
