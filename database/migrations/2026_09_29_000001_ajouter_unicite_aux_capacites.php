<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * Une même capacité ne peut exister qu'une fois par classe (ou par
     * sous-classe). Un index unique direct sur sous_classe_id ne suffirait
     * pas : les capacités de classe ont sous_classe_id NULL, et les bases
     * de données considèrent chaque NULL comme distinct. La colonne générée
     * sous_classe_cle remplace NULL par 0 pour que l'unicité s'applique.
     */
    public function up(): void
    {
        Schema::table('capacites', function (Blueprint $table) {
            $table->unsignedBigInteger('sous_classe_cle')->virtualAs('coalesce(sous_classe_id, 0)');
            $table->unique(['classe_id', 'sous_classe_cle', 'nom'], 'capacites_unique');
        });
    }

    public function down(): void
    {
        Schema::table('capacites', function (Blueprint $table) {
            $table->dropUnique('capacites_unique');
            $table->dropColumn('sous_classe_cle');
        });
    }
};
