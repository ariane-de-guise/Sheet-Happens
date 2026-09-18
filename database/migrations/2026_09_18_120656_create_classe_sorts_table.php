<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classe_sorts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_Classe');
            $table->unsignedBigInteger('id_Sort');

            $table->foreign('id_Classe')
                ->references('id')
                ->on('classes')
                ->cascadeOnDelete();

            $table->foreign('id_Sort')
                ->references('id')
                ->on('sorts')
                ->cascadeOnDelete();

            $table->unique(['id_Classe', 'id_Sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classe_sorts');
    }
};
