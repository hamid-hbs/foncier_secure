<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coffre_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('coffre_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('coffre_dossiers')->cascadeOnDelete();
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('hash_sha256');
            $table->integer('version')->default(1);
            $table->integer('taille')->nullable();
            $table->string('type_mime')->nullable();
            $table->timestamps();
        });

        Schema::create('coffre_partages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('coffre_documents')->cascadeOnDelete();
            $table->string('partage_avec');
            $table->string('token')->unique();
            $table->timestamp('expire_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coffre_partages');
        Schema::dropIfExists('coffre_documents');
        Schema::dropIfExists('coffre_dossiers');
    }
};
