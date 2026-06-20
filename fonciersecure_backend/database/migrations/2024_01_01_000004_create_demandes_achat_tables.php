<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_achat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcelle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('acheteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notaire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message')->nullable();
            $table->enum('statut', ['soumise', 'acceptee', 'refusee'])->default('soumise');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents_demande', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes_achat')->cascadeOnDelete();
            $table->string('type_document');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('hash_sha256');
            $table->integer('taille')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('demande_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes_achat')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('contenu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_messages');
        Schema::dropIfExists('documents_demande');
        Schema::dropIfExists('demandes_achat');
    }
};
