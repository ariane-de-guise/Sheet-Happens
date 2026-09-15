<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * En 5e, l'outil de départ n'est jamais un choix totalement libre : il
     * dépend de l'historique choisi. Certains historiques imposent un (ou
     * plusieurs) outil(s) précis sans choix (ex. l'Ermite reçoit toujours
     * un kit d'herboriste), d'autres proposent une catégorie dans laquelle
     * le joueur choisit (ex. « un type d'outils d'artisan »), et d'autres
     * encore n'accordent aucun outil du tout.
     *
     * - outils_fixes : outils accordés automatiquement, sans choix (fusionnés
     *   avec l'équipement de départ).
     * - outils_disponibles : outils parmi lesquels le joueur doit choisir
     *   (liste vide = aucun choix à faire).
     */
    public function up(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->json('outils_fixes')->nullable()->after('equipement_depart');
            $table->json('outils_disponibles')->nullable()->after('outils_fixes');
        });
    }

    public function down(): void
    {
        Schema::table('historiques', function (Blueprint $table) {
            $table->dropColumn(['outils_fixes', 'outils_disponibles']);
        });
    }
};
