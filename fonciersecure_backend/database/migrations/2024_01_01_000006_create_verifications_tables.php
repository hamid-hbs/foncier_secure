<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcelle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('demandeur_id')->constrained('users')->cascadeOnDelete();
            $table->string('titre');
            $table->string('statut')->default('en_cours');
            $table->integer('score_risque')->nullable();
            $table->string('niveau_risque')->nullable();
            $table->string('rapport_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents_verification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained()->cascadeOnDelete();
            $table->string('type_document');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('hash_sha256');
            $table->integer('taille')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('analyses_automatiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained()->cascadeOnDelete();
            $table->string('type_analyse');
            $table->string('resultat');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses_automatiques');
        Schema::dropIfExists('documents_verification');
        Schema::dropIfExists('verifications');
    }
};
