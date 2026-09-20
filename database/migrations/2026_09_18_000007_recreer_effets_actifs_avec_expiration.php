<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des effets temporaires (potions à bonus) : suivi basé sur une
 * véritable expiration horaire (`expire_a`) plutôt que sur un compteur de
 * tours décrémenté manuellement — le bonus disparaît de lui-même dès que
 * `expire_a` est dans le passé, vérifié dynamiquement par
 * InventaireService::bonusActifs() à chaque calcul, sans action du joueur
 * ni tâche planifiée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('effets_actifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->cascadeOnDelete();
            $table->foreignId('objet_id')->nullable()->constrained('objets')->nullOnDelete();
            $table->string('nom');
            $table->json('bonus');
            $table->timestamp('expire_a');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('effets_actifs');
    }
};
