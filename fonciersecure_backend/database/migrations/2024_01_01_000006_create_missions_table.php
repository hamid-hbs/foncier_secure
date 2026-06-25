<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geometre_id');
            $table->unsignedBigInteger('citoyen_id');
            $table->unsignedBigInteger('parcelle_id')->nullable();
            $table->string('titre', 255);
            $table->text('description');
            $table->enum('type_mission', ['leve_terrain', 'plan_topo', 'expertise', 'expertise_conflit', 'autre']);
            $table->enum('statut', ['soumise', 'acceptee', 'refusee', 'en_cours', 'terminee'])->default('soumise')->index();
            $table->decimal('prix_estime', 10, 2)->nullable();
            $table->string('rapport_path', 255)->nullable();
            $table->timestamp('date_soumise')->useCurrent();
            $table->timestamp('date_acceptee')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('geometre_id', 'fk_missions_geometre_id')->references('id')->on('users');
            $table->foreign('citoyen_id', 'fk_missions_citoyen_id')->references('id')->on('users');
            $table->foreign('parcelle_id', 'fk_missions_parcelle_id')->references('id')->on('parcelles');
            $table->index('geometre_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
