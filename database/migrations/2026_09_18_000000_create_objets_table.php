<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogue des objets : les objets de base 5e (utilisateur_id = null,
 * peuplés par ObjetsSeeder) ainsi que les objets custom créés par un
 * utilisateur (utilisateur_id renseigné). Un même objet du catalogue peut
 * être possédé par plusieurs personnages via la table `personnage_objets`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();

            // Type : détermine les règles d'équipement (une seule armure,
            // budget de "mains" pour armes/bouclier, cf. InventaireService).
            $table->enum('type', ['arme', 'armure', 'bouclier', 'potion', 'outil', 'autre']);

            // Sous-catégorie : uniquement pertinente pour armes
            // (une_main, deux_mains, distance) et armures (legere,
            // intermediaire, lourde) ; null pour les autres types.
            $table->string('categorie')->nullable();

            // Nombre de "mains" occupées quand l'objet est équipé (0, 1 ou
            // 2) : sert au budget de 2 mains pour armes + bouclier.
            $table->unsignedTinyInteger('cout_mains')->default(0);

            // Classe d'Armure de base d'une armure (avant modificateur de
            // Dextérité), ex. 11 pour une armure de cuir. Non pertinent
            // pour les autres types : la CA d'un bouclier ou d'un objet
            // magique est un bonus additif, porté par `bonus.ca` ci-dessous.
            $table->unsignedTinyInteger('ca_base')->nullable();

            // Bonus additifs appliqués tant que l'objet est équipé (ou en
            // tout temps si bonus_toujours_actif est vrai), ex.
            // {"ca": 2, "force": 1}. Clés valides : force, dexterite,
            // constitution, intelligence, sagesse, charisme, ca,
            // bonus_attaque, bonus_degats (armes magiques +1/+2/+3, cf.
            // FicheDePersonnageService::attaques()), bonus_jets_sauvegarde
            // (tous les jets de sauvegarde, cf. ::jetsDeSauvegarde()),
            // bonus_competences (toutes les compétences, cf.
            // ::competences()).
            $table->json('bonus')->nullable();

            // Uniquement pertinent pour les objets custom : par défaut
            // (5e standard) un bonus ne s'applique que si l'objet est
            // équipé ; cette case permet de le rendre actif en tout temps,
            // même dans le sac (ex. un objet passif type anneau).
            $table->boolean('bonus_toujours_actif')->default(false);

            // Objet consommable (ex. potion) : peut être "utilisé" sur soi
            // plutôt qu'équipé, ce qui consomme une unité et applique un
            // effet instantané (ex. {"pv": 7} pour un soin). Une éventuelle
            // durée (tours/minutes) reste purement informative, écrite
            // dans `description` : elle n'est pas suivie mécaniquement par
            // l'application, qui n'a pas d'horloge de jeu.
            $table->boolean('est_consommable')->default(false);
            $table->json('effet_utilisation')->nullable();

            // Dégâts d'une arme, utilisés par FicheDePersonnageService pour
            // la section "Actions" quand l'arme est équipée.
            $table->string('degats')->nullable();
            $table->string('caracteristique_degats')->nullable();
            $table->string('type_degats')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objets');
    }
};
