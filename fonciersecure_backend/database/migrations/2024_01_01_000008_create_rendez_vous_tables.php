<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['signature', 'visite_terrain', 'expertise']);
            $table->morphs('lie');
            $table->text('lieu');
            $table->dateTime('date_time')->index();
            $table->unsignedBigInteger('organisateur_id');
            $table->enum('statut', ['planifie', 'confirme', 'effectue', 'annule'])->default('planifie')->index();
            $table->boolean('tous_confirmes')->default(false);
            $table->timestamps();

            $table->foreign('organisateur_id', 'fk_rdv_organisateur_id')->references('id')->on('users');
        });

        Schema::create('rendez_vous_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rendez_vous_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('est_confirme')->default(false);
            $table->timestamp('date_confirmation')->nullable();
            $table->timestamps();
            $table->unique(['rendez_vous_id', 'user_id']);

            $table->foreign('rendez_vous_id', 'fk_rdv_participants_rdv_id')->references('id')->on('rendez_vous');
            $table->foreign('user_id', 'fk_rdv_participants_user_id')->references('id')->on('users');
            $table->index('rendez_vous_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendez_vous_participants');
        Schema::dropIfExists('rendez_vous');
    }
};
