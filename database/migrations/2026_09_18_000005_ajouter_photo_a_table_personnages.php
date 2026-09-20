<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photo de personnage, optionnelle : chemin (sur le disque public) de
 * l'image envoyée par le joueur. Sans photo, la fiche affiche le logo par
 * défaut de l'application plutôt qu'un cercle vide.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('nom');
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
