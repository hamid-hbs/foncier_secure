<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demande_achat_id')->unique();
            $table->unsignedBigInteger('notaire_id');
            $table->unsignedBigInteger('geometre_assigne_id')->nullable();
            $table->unsignedBigInteger('vendeur_id');
            $table->unsignedBigInteger('acheteur_id');
            $table->unsignedBigInteger('parcelle_id');
            $table->enum('statut', ['en_attente', 'actif', 'suspendu', 'cloture'])->default('en_attente')->index();
            $table->text('motif_suspension')->nullable();
            $table->boolean('vendeur_valide')->default(false);
            $table->boolean('acheteur_valide')->default(false);
            $table->timestamp('date_validation_vendeur')->nullable();
            $table->timestamp('date_validation_acheteur')->nullable();
            $table->timestamp('date_actif')->nullable();
            $table->date('date_signature_effective')->nullable();
            $table->decimal('prix_vente', 15, 2)->nullable();
            $table->decimal('frais_enregistrement', 10, 2)->nullable();
            $table->string('acte_notarie_path', 255)->nullable();
            $table->string('numero_enregistrement', 50)->nullable();
            $table->timestamp('date_cloture')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('demande_achat_id', 'fk_dossier_demande_id')->references('id')->on('demande_achat');
            $table->foreign('notaire_id', 'fk_dossier_notaire_id')->references('id')->on('users');
            $table->foreign('geometre_assigne_id', 'fk_dossier_geometre_id')->references('id')->on('users');
            $table->foreign('vendeur_id', 'fk_dossier_vendeur_id')->references('id')->on('users');
            $table->foreign('acheteur_id', 'fk_dossier_acheteur_id')->references('id')->on('users');
            $table->foreign('parcelle_id', 'fk_dossier_parcelle_id')->references('id')->on('parcelles');
            $table->index('parcelle_id');
        });

        Schema::table('avis_professionnels', function (Blueprint $table) {
            $table->unsignedBigInteger('dossier_transaction_id')->nullable();
            $table->foreign('dossier_transaction_id', 'fk_avis_dossier_id')->references('id')->on('dossier_transactions')->cascadeOnDelete();
        });

        Schema::table('demande_achat', function (Blueprint $table) {
            $table->unsignedBigInteger('dossier_transaction_id')->nullable();
            $table->foreign('dossier_transaction_id', 'fk_demande_dossier_id')->references('id')->on('dossier_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avis_professionnels', function (Blueprint $table) {
            $table->dropForeign('fk_avis_dossier_id');
            $table->dropColumn('dossier_transaction_id');
        });
        Schema::table('demande_achat', function (Blueprint $table) {
            $table->dropForeign('fk_demande_dossier_id');
            $table->dropColumn('dossier_transaction_id');
        });
        Schema::dropIfExists('dossier_transactions');
    }
};
