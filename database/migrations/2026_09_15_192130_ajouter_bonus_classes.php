<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Comme les races (voir bonus_aptitudes sur races/sous_races), la
            // classe choisie peut elle aussi accorder un bonus de
            // caractéristique (généralement sa caractéristique principale,
            // ex. Force pour le Barbare) : ce bonus s'ajoute à celui de la
            // race/sous-race dans le calcul final (voir
            // CreationPersonnageController::abilitesAvecBonus()).
            $table->json('bonus_aptitudes')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('bonus_aptitudes');
        });
    }
};
