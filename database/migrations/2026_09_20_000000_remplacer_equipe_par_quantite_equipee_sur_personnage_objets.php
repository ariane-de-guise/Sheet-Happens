<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace le flag booléen `equipe` par un compteur `quantite_equipee`, pour
 * permettre d'équiper plusieurs exemplaires d'une même arme (dual-wield de
 * deux dagues identiques par ex.) : le budget de "mains" se calcule alors
 * comme `cout_mains * quantite_equipee` plutôt que de supposer un seul
 * exemplaire équipé par ligne.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnage_objets', function (Blueprint $table) {
            $table->unsignedTinyInteger('quantite_equipee')->default(0)->after('quantite');
        });

        DB::table('personnage_objets')->where('equipe', true)->update(['quantite_equipee' => 1]);

        Schema::table('personnage_objets', function (Blueprint $table) {
            $table->dropColumn('equipe');
        });
    }

    public function down(): void
    {
        Schema::table('personnage_objets', function (Blueprint $table) {
            $table->boolean('equipe')->default(false)->after('quantite');
        });

        DB::table('personnage_objets')->where('quantite_equipee', '>', 0)->update(['equipe' => true]);

        Schema::table('personnage_objets', function (Blueprint $table) {
            $table->dropColumn('quantite_equipee');
        });
    }
};
