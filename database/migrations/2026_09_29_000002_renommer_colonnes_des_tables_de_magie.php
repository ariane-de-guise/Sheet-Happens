<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * Aligne les tables de magie sur la convention du reste de la base
     * (snake_case, clés étrangères en <table>_id). SortConnue devient
     * sorts_prepares : c'est la limite de sorts préparés, pas de sorts connus.
     */
    private const COLONNES = [
        'sorts' => [
            'id_Degat' => 'degat_id', 'Nom' => 'nom', 'NomAnglais' => 'nom_anglais', 'Description' => 'description',
            'Niveau' => 'niveau', 'TempsActivation' => 'temps_activation', 'Duree' => 'duree', 'Ecole' => 'ecole',
            'Distance' => 'distance', 'AttaqueSauvegarde' => 'attaque_sauvegarde', 'Composants' => 'composants',
        ],
        'degats' => ['Nom' => 'nom'],
        'classe_sorts' => ['id_Classe' => 'classe_id', 'id_Sort' => 'sort_id'],
        'sous_classe_sorts' => ['id_SousClasse' => 'sous_classe_id', 'id_Sort' => 'sort_id'],
        'personnage_sorts' => ['id_Personnage' => 'personnage_id', 'id_Sort' => 'sort_id', 'Prepare' => 'preparation'],
        'limite_magies' => [
            'TourMagie' => 'tours_de_magie', 'SortConnue' => 'sorts_prepares', 'Niveau1' => 'emplacements_niveau_1',
            'Niveau2' => 'emplacements_niveau_2', 'Niveau3' => 'emplacements_niveau_3',
        ],
        'limite_magie_classes' => [
            'id_Classe' => 'classe_id', 'id_LimiteMagie' => 'limite_magie_id', 'Niveau' => 'niveau', 'AbiliteMagie' => 'caracteristique_magie',
        ],
        'limite_magie_sous_classes' => [
            'id_SousClasse' => 'sous_classe_id', 'id_LimiteMagie' => 'limite_magie_id', 'Niveau' => 'niveau', 'AbiliteMagie' => 'caracteristique_magie',
        ],
    ];

    public function up(): void
    {
        Schema::rename('sousclasse_sorts', 'sous_classe_sorts');

        foreach (self::COLONNES as $table => $colonnes) {
            Schema::table($table, function (Blueprint $blueprint) use ($colonnes) {
                foreach ($colonnes as $ancienne => $nouvelle) {
                    $blueprint->renameColumn($ancienne, $nouvelle);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLONNES as $table => $colonnes) {
            Schema::table($table, function (Blueprint $blueprint) use ($colonnes) {
                foreach ($colonnes as $ancienne => $nouvelle) {
                    $blueprint->renameColumn($nouvelle, $ancienne);
                }
            });
        }

        Schema::rename('sous_classe_sorts', 'sousclasse_sorts');
    }
};
