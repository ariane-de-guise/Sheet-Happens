<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // En 5e, chaque classe propose sa propre liste de compétences
            // parmi lesquelles choisir (et un nombre de choix qui varie
            // selon la classe, ex. 2 pour la plupart, 4 pour le Roublard).
            $table->json('competences_disponibles')->nullable()->after('description');
            $table->unsignedTinyInteger('nombre_competences')->default(2)->after('competences_disponibles');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['competences_disponibles', 'nombre_competences']);
        });
    }
};
