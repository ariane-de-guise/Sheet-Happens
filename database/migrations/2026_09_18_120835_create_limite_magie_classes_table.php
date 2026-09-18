<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LimiteMagieClasse', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_Classe');

            $table->unsignedBigInteger('id_LimiteMagie');

            $table->unsignedTinyInteger('Niveau');

            $table->timestamps();

            $table->foreign('id_Classe')
                ->references('id')
                ->on('classes')
                ->cascadeOnDelete();

            $table->foreign('id_LimiteMagie')
                ->references('id')
                ->on('LimiteMagie')
                ->cascadeOnDelete();

            $table->unique(
                ['id_Classe', 'Niveau'],
                'limite_magie_classe_niveau_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LimiteMagieClasse');
    }
};
