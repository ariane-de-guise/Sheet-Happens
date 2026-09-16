<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            // "Maîtrise" faisait doublon avec "Historique" : l'équipement de
            // départ et l'alignement sont désormais dérivés de l'historique
            // et de l'idéal choisis (voir CreationPersonnageController).
            $table->dropColumn('maitrise');
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->string('maitrise')->nullable()->after('alignement');
        });
    }
};
