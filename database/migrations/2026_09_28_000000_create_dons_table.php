<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dons', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            // Résumé d'une ligne affiché dans la liste de choix de la montée de niveau.
            $table->string('resume')->nullable();
            $table->text('description')->nullable();
            // Caractéristique augmentée de 1 sans choix (ex. Acteur → charisme).
            $table->string('stat')->nullable();
            // Caractéristiques parmi lesquelles le joueur choisit celle augmentée de 1.
            $table->json('choix_stat')->nullable();
            // Effets chiffrés appliqués par la fiche (ex. {"pv_par_niveau": 2}).
            $table->json('effets')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dons');
    }
};
