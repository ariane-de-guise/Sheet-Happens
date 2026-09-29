<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnage_don', function (Blueprint $table) {
            $table->foreignId('personnage_id')->constrained('personnages')->cascadeOnDelete();
            $table->foreignId('don_id')->constrained('dons')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['personnage_id', 'don_id']);
        });

        Schema::create('personnage_capacite', function (Blueprint $table) {
            $table->foreignId('personnage_id')->constrained('personnages')->cascadeOnDelete();
            $table->foreignId('capacite_id')->constrained('capacites')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['personnage_id', 'capacite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage_capacite');
        Schema::dropIfExists('personnage_don');
    }
};
