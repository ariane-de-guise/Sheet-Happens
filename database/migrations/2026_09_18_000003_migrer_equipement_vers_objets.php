<?php

use App\Models\Objet;
use App\Models\Personnage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'ancienne colonne `personnages.equipement` (tableau JSON de simples noms)
 * est remplacée par les tables `objets`/`personnage_objets`, qui permettent
 * quantité, équipement et bonus (voir InventaireService). Cette migration
 * convertit les données existantes avant de supprimer la colonne : chaque
 * nom d'objet déjà enregistré sur un personnage devient une ligne
 * `personnage_objets` (non équipée par défaut), rattachée à une entrée du
 * catalogue `objets` créée à la volée si besoin.
 *
 * Les quelques objets mécaniques déjà interprétés en dur par
 * FicheDePersonnageService (armes/armure/bouclier) sont recréés avec leurs
 * vraies propriétés ; tout le reste (objets narratifs accordés par un
 * historique, ex. "Symbole sacré") devient un objet générique de type
 * "autre", sans bonus. ObjetsSeeder (exécuté séparément via db:seed)
 * enrichira ensuite ces entrées mécaniques via updateOrCreate, sans créer
 * de doublon.
 */
return new class extends Migration
{
    private const OBJETS_CONNUS = [
        'Épée' => ['type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant'],
        'Arc court' => ['type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 2, 'degats' => '1d6', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant'],
        'Bouclier' => ['type' => 'bouclier', 'categorie' => null, 'cout_mains' => 1, 'bonus' => ['ca' => 2]],
        'Armure de cuir' => ['type' => 'armure', 'categorie' => 'legere', 'cout_mains' => 0, 'ca_base' => 11],
    ];

    public function up(): void
    {
        Personnage::query()
            ->whereNotNull('equipement')
            ->get(['id', 'equipement'])
            ->each(function (Personnage $personnage): void {
                $noms = array_count_values($personnage->equipement ?? []);

                foreach ($noms as $nom => $quantite) {
                    if ($nom === '') {
                        continue;
                    }

                    $definition = self::OBJETS_CONNUS[$nom] ?? ['type' => 'autre', 'categorie' => null, 'cout_mains' => 0];

                    $objet = Objet::firstOrCreate(
                        ['nom' => $nom, 'utilisateur_id' => null],
                        $definition,
                    );

                    $personnage->objets()->syncWithoutDetaching([
                        $objet->id => ['quantite' => $quantite, 'equipe' => false],
                    ]);
                }
            });

        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn('equipement');
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->json('equipement')->nullable();
        });
    }
};
