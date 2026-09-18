<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sousclasse_sorts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_SousClasse');
            $table->unsignedBigInteger('id_Sort');

            $table->foreign('id_SousClasse')
                ->references('id')
                ->on('sous_classes')
                ->cascadeOnDelete();

            $table->foreign('id_Sort')
                ->references('id')
                ->on('sorts')
                ->cascadeOnDelete();

            $table->unique(['id_SousClasse', 'id_Sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sousclasse_sorts');
    }
};
