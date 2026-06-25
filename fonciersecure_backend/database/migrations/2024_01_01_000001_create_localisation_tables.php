<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('code_commune', 10)->unique();
            $table->string('nom', 100);
            $table->string('departement', 100);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        Schema::create('arrondissements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->string('code_arrondissement', 10)->unique();
            $table->string('nom', 100);
            $table->timestamps();
        });

        Schema::create('quartiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrondissement_id')->constrained()->cascadeOnDelete();
            $table->string('nom', 100);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quartiers');
        Schema::dropIfExists('arrondissements');
        Schema::dropIfExists('communes');
    }
};
