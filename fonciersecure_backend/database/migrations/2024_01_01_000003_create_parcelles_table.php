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
            $table->string('code_parcelle', 50)->unique();
            $table->string('titre_parcelle', 255);
            $table->decimal('superficie', 10, 2);
            $table->unsignedBigInteger('commune_id');
            $table->unsignedBigInteger('arrondissement_id');
            $table->unsignedBigInteger('quartier_id')->nullable();
            $table->text('localisation_textuelle')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('precision_gps')->nullable();
            $table->enum('statut', ['libre', 'en_demande', 'en_transaction', 'vendue', 'suspendue', 'en_litige'])->default('libre')->index();
            $table->enum('type_acquisition', ['achat', 'succession', 'donation', 'autre'])->default('achat');
            $table->decimal('valeur_estimee', 15, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('commune_id', 'fk_parcelles_commune_id')->references('id')->on('communes');
            $table->foreign('arrondissement_id', 'fk_parcelles_arrondissement_id')->references('id')->on('arrondissements');
            $table->foreign('quartier_id', 'fk_parcelles_quartier_id')->references('id')->on('quartiers');
            $table->index('commune_id');
        });

        Schema::create('proprietes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parcelle_id');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('type_propriete', ['plein_droit', 'usufruit', 'nue_propriete', 'autre'])->default('plein_droit');
            $table->decimal('part_indivision', 5, 2)->nullable();
            $table->enum('statut', ['actif', 'historique'])->default('actif')->index();
            $table->timestamps();

            $table->foreign('user_id', 'fk_proprietes_user_id')->references('id')->on('users');
            $table->foreign('parcelle_id', 'fk_proprietes_parcelle_id')->references('id')->on('parcelles');
            $table->unique(['user_id', 'parcelle_id', 'date_debut'], 'uq_proprietes_user_parcelle_debut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proprietes');
        Schema::dropIfExists('parcelles');
    }
};
