<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('classes', 'niveau_sous_classe')) {
            return;
        }

        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedTinyInteger('niveau_sous_classe')->default(1)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('niveau_sous_classe');
        });
    }
};
