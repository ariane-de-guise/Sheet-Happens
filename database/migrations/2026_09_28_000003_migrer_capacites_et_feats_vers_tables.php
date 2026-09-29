<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * Les dons et capacités étaient stockés comme simples listes de noms
     * (colonnes JSON personnages.feats / personnages.capacites). Ils vivent
     * désormais dans les tables dons / capacites, reliées au personnage
     * par personnage_don / personnage_capacite.
     *
     * Les dons sont des choix du joueur : on les reporte dans le pivot (en
     * créant le don par son nom s'il n'existe pas encore ; DonSeeder
     * complétera sa description). Les capacités, elles, découlent de la
     * classe, de la sous-classe et du niveau : CapaciteSeeder les
     * resynchronise pour les personnages existants.
     */
    public function up(): void
    {
        $maintenant = now();

        DB::table('personnages')->whereNotNull('feats')->orderBy('id')->each(function ($personnage) use ($maintenant) {
            foreach (json_decode($personnage->feats, true) ?: [] as $nom) {
                $donId = DB::table('dons')->where('nom', $nom)->value('id')
                    ?? DB::table('dons')->insertGetId(['nom' => $nom, 'created_at' => $maintenant, 'updated_at' => $maintenant]);

                DB::table('personnage_don')->insertOrIgnore([
                    'personnage_id' => $personnage->id,
                    'don_id' => $donId,
                    'created_at' => $maintenant,
                    'updated_at' => $maintenant,
                ]);
            }
        });

        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn(['capacites', 'feats']);
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->json('capacites')->nullable();
            $table->json('feats')->nullable();
        });

        DB::table('personnages')->orderBy('id')->each(function ($personnage) {
            $dons = DB::table('personnage_don')
                ->join('dons', 'dons.id', '=', 'personnage_don.don_id')
                ->where('personnage_don.personnage_id', $personnage->id)
                ->pluck('dons.nom')
                ->all();
            $capacites = DB::table('personnage_capacite')
                ->join('capacites', 'capacites.id', '=', 'personnage_capacite.capacite_id')
                ->where('personnage_capacite.personnage_id', $personnage->id)
                ->orderBy('capacites.niveau')
                ->pluck('capacites.nom')
                ->all();

            DB::table('personnages')->where('id', $personnage->id)->update([
                'feats' => json_encode($dons),
                'capacites' => json_encode($capacites),
            ]);
        });
    }
};
