<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('limite_magie_classes', 'AbiliteMagie')) {
            Schema::table('limite_magie_classes', function (Blueprint $table) {
                $table->string('AbiliteMagie')->default('Intelligence');
            });
        }

        if (! Schema::hasColumn('limite_magie_sous_classes', 'AbiliteMagie')) {
            Schema::table('limite_magie_sous_classes', function (Blueprint $table) {
                $table->string('AbiliteMagie')->default('Intelligence');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('limite_magie_classes', 'AbiliteMagie')) {
            Schema::table('limite_magie_classes', function (Blueprint $table) {
                $table->dropColumn('AbiliteMagie');
            });
        }

        if (Schema::hasColumn('limite_magie_sous_classes', 'AbiliteMagie')) {
            Schema::table('limite_magie_sous_classes', function (Blueprint $table) {
                $table->dropColumn('AbiliteMagie');
            });
        }
    }
};
