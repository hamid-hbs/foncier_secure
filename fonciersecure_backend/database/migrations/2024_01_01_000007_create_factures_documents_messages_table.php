<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->enum('facturable_type', ['DossierTransaction', 'Mission']);
            $table->unsignedBigInteger('facturable_id');
            $table->unsignedBigInteger('prestataire_id');
            $table->unsignedBigInteger('client_id');
            $table->decimal('montant_total', 15, 2);
            $table->decimal('montant_paye', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('statut', ['brouillon', 'envoyee', 'payee', 'partiellement_payee', 'annulee'])->default('brouillon')->index();
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->date('date_paiement')->nullable();
            $table->enum('moyen_paiement', ['especes', 'virement', 'cheque', 'mobile_money'])->nullable();
            $table->timestamps();

            $table->foreign('prestataire_id', 'fk_factures_prestataire_id')->references('id')->on('users');
            $table->foreign('client_id', 'fk_factures_client_id')->references('id')->on('users');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('documentable_type', ['Parcelle', 'DemandeAchat', 'DossierTransaction', 'Mission']);
            $table->unsignedBigInteger('documentable_id')->index();
            $table->enum('type_document', ['titre_foncier', 'adc', 'plan_cadastral', 'plan_topo', 'acte_vente', 'expertise', 'autre']);
            $table->string('nom_original', 255);
            $table->string('chemin_fichier', 255);
            $table->string('hash_sha256', 64)->unique();
            $table->integer('taille_bytes');
            $table->string('mime_type', 50);
            $table->string('proprietaire_nom', 255)->nullable();
            $table->string('proprietaire_prenoms', 255)->nullable();
            $table->string('numero_titre', 50)->nullable();
            $table->date('date_document')->nullable();
            $table->boolean('est_verifiee')->default(false);
            $table->text('commentaires_verification')->nullable();
            $table->unsignedBigInteger('uploaded_by_id');
            $table->timestamps();

            $table->foreign('uploaded_by_id', 'fk_documents_uploader_id')->references('id')->on('users');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->nullableMorphs('messageable');
            $table->text('contenu');
            $table->boolean('est_lu')->default(false);
            $table->timestamp('date_lu')->nullable();
            $table->timestamps();

            $table->foreign('sender_id', 'fk_messages_sender_id')->references('id')->on('users');
            $table->foreign('receiver_id', 'fk_messages_receiver_id')->references('id')->on('users');
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('factures');
    }
};
