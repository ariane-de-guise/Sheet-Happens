<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->string('maitrise')->nullable()->after('alignement');
            $table->string('outil')->nullable()->after('maitrise');
            $table->json('langues')->nullable()->after('outil');
            $table->json('equipement')->nullable()->after('langues');
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn(['maitrise', 'outil', 'langues', 'equipement']);
        });
    }
};
