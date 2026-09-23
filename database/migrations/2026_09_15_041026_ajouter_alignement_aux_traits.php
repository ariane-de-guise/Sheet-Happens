<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute une colonne "alignement" (nullable) aux tables defauts,
     * attachements et personnalites, sur le même modèle que celle qui
     * existe déjà sur la table ideaux. Permet de calculer l'alignement
     * final du personnage en combinant les 4 traits (idéal, défaut,
     * attachement, personnalité) plutôt que de se baser uniquement sur
     * l'idéal choisi.
     */
    public function up(): void
    {
        Schema::table('defauts', function (Blueprint $table) {
            $table->string('alignement')->nullable()->after('valeur');
        });

        Schema::table('attachements', function (Blueprint $table) {
            $table->string('alignement')->nullable()->after('valeur');
        });

        Schema::table('personnalites', function (Blueprint $table) {
            $table->string('alignement')->nullable()->after('valeur');
        });
    }

    public function down(): void
    {
        Schema::table('defauts', function (Blueprint $table) {
            $table->dropColumn('alignement');
        });

        Schema::table('attachements', function (Blueprint $table) {
            $table->dropColumn('alignement');
        });

        Schema::table('personnalites', function (Blueprint $table) {
            $table->dropColumn('alignement');
        });
    }
};
