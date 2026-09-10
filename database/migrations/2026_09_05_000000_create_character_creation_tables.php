<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('taille')->nullable();
            $table->unsignedSmallInteger('vitesse')->nullable();
            $table->string('type_creature')->default('Humanoide');
            $table->text('description')->nullable();
            $table->json('bonus_aptitudes')->nullable();
            $table->timestamps();
        });

        Schema::create('sous_races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->json('bonus_aptitudes')->nullable();
            $table->timestamps();
            $table->unique(['race_id', 'nom']);
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('de_de_vie')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sous_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('niveau_sous_classe')->default(1);
            $table->timestamps();
            $table->unique(['classe_id', 'nom']);
        });

        Schema::create('abilites', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('force')->default(8);
            $table->unsignedTinyInteger('dexterite')->default(8);
            $table->unsignedTinyInteger('constitution')->default(8);
            $table->unsignedTinyInteger('intelligence')->default(8);
            $table->unsignedTinyInteger('sagesse')->default(8);
            $table->unsignedTinyInteger('charisme')->default(8);
            $table->timestamps();
        });

        Schema::create('listes_competences', function (Blueprint $table) {
            $table->id();
            $table->json('competences')->nullable();
            $table->timestamps();
        });

        Schema::create('personnages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('race_id')->constrained('races')->restrictOnDelete();
            $table->foreignId('sous_race_id')->nullable()->constrained('sous_races')->nullOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('sous_classe_id')->nullable()->constrained('sous_classes')->nullOnDelete();
            $table->foreignId('abilites_id')->constrained('abilites')->cascadeOnDelete();
            $table->foreignId('liste_competence_id')->constrained('listes_competences')->cascadeOnDelete();
            $table->string('nom');
            $table->unsignedTinyInteger('niveau')->default(1);
            $table->string('alignement')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('point_de_vie')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnages');
        Schema::dropIfExists('listes_competences');
        Schema::dropIfExists('abilites');
        Schema::dropIfExists('sous_classes');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('sous_races');
        Schema::dropIfExists('races');
    }
};
