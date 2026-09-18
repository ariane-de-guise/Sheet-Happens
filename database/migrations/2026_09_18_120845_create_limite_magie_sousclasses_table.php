<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LimiteMagieSousClasse', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_SousClasse');

            $table->unsignedBigInteger('id_LimiteMagie');

            $table->unsignedTinyInteger('Niveau');

            $table->timestamps();

            $table->foreign('id_SousClasse')
                ->references('id')
                ->on('sous_classes')
                ->cascadeOnDelete();

            $table->foreign('id_LimiteMagie')
                ->references('id')
                ->on('LimiteMagie')
                ->cascadeOnDelete();

            $table->unique(
                ['id_SousClasse', 'Niveau'],
                'limite_magie_sous_classe_niveau_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LimiteMagieSousClasse');
    }
};
