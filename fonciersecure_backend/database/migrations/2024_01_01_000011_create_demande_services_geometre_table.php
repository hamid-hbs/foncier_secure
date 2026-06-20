<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_services_geometre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citoyen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('geometre_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parcelle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('statut')->default('soumise');
            $table->string('rapport_path')->nullable();
            $table->text('commentaire_geometre')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_services_geometre');
    }
};
