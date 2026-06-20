<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('arrondissement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quartier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('superficie', 10, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('prix_estimatif', 15, 2)->nullable();
            $table->enum('statut', ['libre', 'en_demande', 'en_transaction', 'vendue'])->default('libre');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parcelle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcelle_id')->constrained()->cascadeOnDelete();
            $table->string('type_document');
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('hash_sha256');
            $table->integer('taille')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelle_documents');
        Schema::dropIfExists('parcelles');
    }
};
