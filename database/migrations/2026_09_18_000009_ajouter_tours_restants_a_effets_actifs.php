<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un effet temporaire en "tours" (rounds de combat) n'a pas de
 * correspondance fiable avec le temps réel écoulé à table (un round de 6
 * secondes en jeu peut prendre plusieurs minutes réelles à résoudre) :
 * contrairement aux effets en minutes (qui gardent une vraie expiration
 * horaire, `expire_a`), les effets en tours sont décomptés manuellement
 * par le joueur via `tours_restants`. `expire_a` devient donc nullable
 * (uniquement rempli pour les effets en minutes), et `unite` indique
 * lequel des deux modes s'applique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('effets_actifs', function (Blueprint $table) {
            $table->enum('unite', ['tours', 'minutes'])->default('minutes')->after('bonus');
            $table->unsignedInteger('tours_restants')->nullable()->after('unite');
        });

        // `->change()` nécessiterait doctrine/dbal, non installé ici : on
        // recrée la colonne plutôt que de la modifier en place. Sans
        // conséquence en pratique, cette table ne contient que des effets
        // temporaires de courte durée.
        Schema::table('effets_actifs', function (Blueprint $table) {
            $table->dropColumn('expire_a');
        });
        Schema::table('effets_actifs', function (Blueprint $table) {
            $table->timestamp('expire_a')->nullable()->after('tours_restants');
        });
    }

    public function down(): void
    {
        Schema::table('effets_actifs', function (Blueprint $table) {
            $table->dropColumn(['unite', 'tours_restants', 'expire_a']);
        });
        Schema::table('effets_actifs', function (Blueprint $table) {
            $table->timestamp('expire_a');
        });
    }
};
