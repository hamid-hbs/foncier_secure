<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->nullableMorphs('notifiable');
            $table->string('titre', 255);
            $table->text('message');
            $table->enum('type_action', [
                'demande_reçue', 'demande_acceptee', 'dossier_actif',
                'facture_envoyee', 'rdv_confirme', 'autre',
            ]);
            $table->string('lien_redirection', 255)->nullable();
            $table->boolean('est_lue')->default(false)->index();
            $table->timestamp('date_lu')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_notifications_user_id')->references('id')->on('users');
            $table->index('user_id');
        });

        Schema::create('blockchain_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->index();
            $table->unsignedBigInteger('reference_id')->index();
            $table->string('action', 100);
            $table->json('donnees_hash_inputs');
            $table->string('hash_courant', 64);
            $table->string('hash_precedent', 64)->nullable();
            $table->unsignedBigInteger('acteur_id');
            $table->unsignedInteger('sequence');
            $table->boolean('est_verifie')->default(true);
            $table->timestamps();

            $table->foreign('acteur_id', 'fk_blockchain_acteur_id')->references('id')->on('users');
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('sujet', 255);
            $table->text('message');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->enum('statut', ['ouvert', 'en_cours', 'resolu', 'ferme'])->default('ouvert')->index();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->text('reponse')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_tickets_user_id')->references('id')->on('users');
            $table->foreign('assigned_to_id', 'fk_tickets_assigned_to_id')->references('id')->on('users');
        });

        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->morphs('analysable');
            $table->enum('type_analyse', ['coherence_docs', 'doublons', 'gps', 'risque', 'validite_pros']);
            $table->integer('score')->default(0);
            $table->enum('niveau_risque', ['faible', 'moyen', 'eleve']);
            $table->json('resultats')->nullable();
            $table->json('recommandations')->nullable();
            $table->string('analyseur', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('blockchain_logs');
        Schema::dropIfExists('notifications');
    }
};
