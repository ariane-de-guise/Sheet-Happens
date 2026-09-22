<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnage_sorts', function (Blueprint $table) {
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
             * Foreign keys
             */
            $table->foreign('id_Personnage')
                ->references('id')
                ->on('personnages')
                ->cascadeOnDelete();

            $table->foreign('id_Sort')
                ->references('id')
                ->on('sorts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage_sorts');
    }
};
