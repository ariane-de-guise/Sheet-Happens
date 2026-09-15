<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('or_depart')->default(0);
            $table->json('competences')->nullable();
            $table->json('langues')->nullable();
            $table->json('equipement_depart')->nullable();
            $table->timestamps();
        });

        Schema::create('ideaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historique_id')->constrained('historiques')->cascadeOnDelete();
            $table->string('valeur');
            $table->string('alignement')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('defauts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historique_id')->constrained('historiques')->cascadeOnDelete();
            $table->string('valeur');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('attachements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historique_id')->constrained('historiques')->cascadeOnDelete();
            $table->string('valeur');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('personnalites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historique_id')->constrained('historiques')->cascadeOnDelete();
            $table->string('valeur');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnalites');
        Schema::dropIfExists('attachements');
        Schema::dropIfExists('defauts');
        Schema::dropIfExists('ideaux');
        Schema::dropIfExists('historiques');
    }
};
