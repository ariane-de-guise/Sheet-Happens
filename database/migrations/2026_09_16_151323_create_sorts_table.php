<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_Degat')
                ->nullable()
                ->constrained('degats')
                ->nullOnDelete();

            $table->string('Nom');
            $table->string('NomAnglais')->nullable();

            $table->text('Description');

            // 0 = Cantrip
            // 1, 2, 3 = niveau du sort
            $table->unsignedTinyInteger('Niveau');

            $table->string('TempsActivation');
            $table->string('Duree');
            $table->string('Ecole');
            $table->string('Distance');
            $table->string('AttaqueSauvegarde')->nullable();
            $table->string('Composants')->nullable();

            $table->timestamps();

            $table->index('Niveau');
            $table->index('Ecole');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorts');
    }
};
