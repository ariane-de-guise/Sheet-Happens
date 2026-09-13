<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->foreignId('historique_id')->nullable()->after('sous_classe_id')->constrained('historiques')->nullOnDelete();
            $table->foreignId('ideal_id')->nullable()->after('historique_id')->constrained('ideaux')->nullOnDelete();
            $table->foreignId('defaut_id')->nullable()->after('ideal_id')->constrained('defauts')->nullOnDelete();
            $table->foreignId('attachement_id')->nullable()->after('defaut_id')->constrained('attachements')->nullOnDelete();
            $table->foreignId('personnalite_id')->nullable()->after('attachement_id')->constrained('personnalites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personnalite_id');
            $table->dropConstrainedForeignId('attachement_id');
            $table->dropConstrainedForeignId('defaut_id');
            $table->dropConstrainedForeignId('ideal_id');
            $table->dropConstrainedForeignId('historique_id');
        });
    }
};
