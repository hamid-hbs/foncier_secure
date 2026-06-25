<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->enum('type', ['geometre', 'notaire']);
            $table->string('numero_enregistrement', 50)->unique();
            $table->date('date_enregistrement');
            $table->date('date_expiration')->nullable();
            $table->string('specialisation', 255)->nullable();
            $table->unsignedBigInteger('commune_id')->nullable();
            $table->text('adresse_bureau')->nullable();
            $table->decimal('taux_horaire', 10, 2)->nullable();
            $table->decimal('note_moyenne', 3, 2)->default(3.0);
            $table->integer('nombre_avis')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->foreign('user_id', 'fk_professionnels_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('commune_id', 'fk_professionnels_commune_id')->references('id')->on('communes')->nullOnDelete();
        });

        Schema::create('avis_professionnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professionnel_id');
            $table->unsignedBigInteger('citoyen_id');
            $table->tinyInteger('note');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_avis')->useCurrent();
            $table->unique(['professionnel_id', 'citoyen_id']);

            $table->foreign('professionnel_id', 'fk_avis_professionnel_id')->references('id')->on('professionnels')->cascadeOnDelete();
            $table->foreign('citoyen_id', 'fk_avis_citoyen_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis_professionnels');
        Schema::dropIfExists('professionnels');
    }
};
