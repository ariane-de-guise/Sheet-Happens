<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * point_de_vie est le maximum de PV du personnage ; point_de_vie_actuel
     * suit les dégâts et les soins en jeu (potions, combats), entre 0 et
     * ce maximum. Les personnages existants démarrent en pleine forme.
     */
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->unsignedSmallInteger('point_de_vie_actuel')->nullable()->after('point_de_vie');
        });

        DB::table('personnages')->update(['point_de_vie_actuel' => DB::raw('point_de_vie')]);
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn('point_de_vie_actuel');
        });
    }
};
