<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distingue les objets magiques du reste du catalogue (filtrable sur la
 * page d'ajout) et leur rareté (5e SRD), pertinente uniquement pour les
 * objets magiques.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objets', function (Blueprint $table) {
            $table->boolean('magique')->default(false)->after('type');
            $table->enum('rarete', ['commun', 'peu_commun', 'rare', 'tres_rare', 'legendaire', 'artefact'])->nullable()->after('magique');
        });
    }

    public function down(): void
    {
        Schema::table('objets', function (Blueprint $table) {
            $table->dropColumn(['magique', 'rarete']);
        });
    }
};
