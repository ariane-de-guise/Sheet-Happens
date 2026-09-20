<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventaire réel d'un personnage : une ligne par objet possédé (avec sa
 * quantité et son état équipé/non équipé). Remplace l'ancienne colonne
 * `personnages.equipement` (simple tableau JSON de noms), qui ne permettait
 * ni quantité, ni équipement, ni bonus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnage_objets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->cascadeOnDelete();
            $table->foreignId('objet_id')->constrained('objets')->cascadeOnDelete();
            $table->unsignedInteger('quantite')->default(1);
            $table->boolean('equipe')->default(false);
            $table->timestamps();

            $table->unique(['personnage_id', 'objet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage_objets');
    }
};
