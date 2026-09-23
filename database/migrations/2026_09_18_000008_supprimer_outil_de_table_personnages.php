<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'outil choisi à la création n'est plus stocké comme un simple texte sur
 * le personnage (et affiché sous "Compétences") : il est désormais ajouté
 * à l'inventaire réel comme un objet de type "outil" (voir
 * PersonnageCreationService::create()), cohérent avec le reste de
 * l'équipement de départ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn('outil');
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->string('outil')->nullable();
        });
    }
};
