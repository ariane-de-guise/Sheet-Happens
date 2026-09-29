<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Capacités (traits de classe) : accordées par une classe ou,
         * lorsque sous_classe_id est renseigné, par une sous-classe, à
         * partir du niveau indiqué.
         */
        Schema::create('capacites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('sous_classe_id')->nullable()->constrained('sous_classes')->cascadeOnDelete();
            $table->string('nom');
            $table->unsignedTinyInteger('niveau');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['classe_id', 'niveau']);
            $table->index(['sous_classe_id', 'niveau']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacites');
    }
};
