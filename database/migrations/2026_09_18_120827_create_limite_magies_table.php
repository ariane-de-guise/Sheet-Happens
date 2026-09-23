<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('limite_magies', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('TourMagie')
                ->default(0);

            $table->unsignedTinyInteger('SortConnue')
                ->nullable()
                ->default(null);

            $table->unsignedTinyInteger('Niveau1')
                ->default(0);

            $table->unsignedTinyInteger('Niveau2')
                ->default(0);

            $table->unsignedTinyInteger('Niveau3')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('limite_magies');
    }
};
