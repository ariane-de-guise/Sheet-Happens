<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PersonnageSort', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_Personnage');
            $table->unsignedBigInteger('id_Sort');

            // 0 = non préparé
            // 1 = préparé
            // 2 = état spécial
            $table->unsignedTinyInteger('Prepare')
                ->default(0);

            $table->timestamps();

            /*
             * Un personnage ne peut avoir qu'une seule
             * association avec un même sort.
             */
            $table->unique(
                ['id_Personnage', 'id_Sort'],
                'personnage_sort_unique'
            );

            /*
             * Vérifie que Prepare est compris entre 0 et 2.
             */
            $table->check('Prepare >= 0 AND Prepare <= 2');

            /*
             * Foreign keys
             */
            $table->foreign('id_Personnage')
                ->references('id')
                ->on('Personnage')
                ->cascadeOnDelete();

            $table->foreign('id_Sort')
                ->references('id')
                ->on('Sort')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PersonnageSort');
    }
};
