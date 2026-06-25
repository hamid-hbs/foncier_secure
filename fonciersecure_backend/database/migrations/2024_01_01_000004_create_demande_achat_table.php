<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_achat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parcelle_id');
            $table->unsignedBigInteger('acheteur_id');
            $table->unsignedBigInteger('vendeur_id');
            $table->enum('statut', ['soumise', 'acceptee', 'refusee', 'notaire_sollicite', 'dossier_cree'])->default('soumise')->index();
            $table->text('message_acheteur')->nullable();
            $table->string('code_secret', 20)->nullable();
            $table->unsignedBigInteger('notaire_id')->nullable();
            $table->string('raison_refus', 500)->nullable();
            $table->integer('tentatives_approbation')->default(1);
            $table->timestamp('date_soumise')->useCurrent();
            $table->timestamp('date_acceptee')->nullable();
            $table->timestamp('date_refusee')->nullable();
            $table->softDeletes();

            $table->foreign('parcelle_id', 'fk_demande_parcelle_id')->references('id')->on('parcelles');
            $table->foreign('acheteur_id', 'fk_demande_acheteur_id')->references('id')->on('users');
            $table->foreign('vendeur_id', 'fk_demande_vendeur_id')->references('id')->on('users');
            $table->foreign('notaire_id', 'fk_demande_notaire_id')->references('id')->on('users');
            $table->index('parcelle_id');
            $table->index('acheteur_id');
            $table->index('vendeur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_achat');
    }
};
