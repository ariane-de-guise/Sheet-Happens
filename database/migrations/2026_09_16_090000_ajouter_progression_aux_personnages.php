<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->json('capacites')->nullable()->after('equipement');
            $table->json('feats')->nullable()->after('capacites');
        });

        Schema::create('montees_niveau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->cascadeOnDelete();
            $table->unsignedTinyInteger('niveau_depart');
            $table->unsignedTinyInteger('niveau_arrivee');
            $table->json('choix')->nullable();
            $table->json('changements')->nullable();
            $table->timestamps();
            $table->unique(['personnage_id', 'niveau_arrivee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('montees_niveau');
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn(['capacites', 'feats']);
        });
    }
};
