<?php

namespace App\Services;

use App\Models\MonteeNiveau;
use App\Models\Personnage;
use App\Models\SousClasse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonteeNiveauService
{
    private const STATS = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];

    /** Progression PHB 2014, limitée aux cinq classes prises en charge par le projet. */
    private const PROGRESSION = [
        'Barbare' => [
            2 => ['Attaque téméraire', 'Sens du danger'],
            3 => ['Voie primitive'],
            4 => ['Amélioration de caractéristiques ou don'],
            5 => ['Attaque supplémentaire', 'Déplacement rapide'],
        ],
        'Clerc' => [
            2 => ['Canalisation d’énergie divine', 'Capacité de domaine divin'],
            3 => ['Emplacements de sorts de niveau 2'],
            4 => ['Amélioration de caractéristiques ou don', 'Sort mineur supplémentaire'],
            5 => ['Détruire les morts-vivants (FP 1/2)', 'Emplacements de sorts de niveau 3'],
        ],
        'Guerrier' => [
            2 => ['Sursaut d’activité'],
            3 => ['Archétype martial'],
            4 => ['Amélioration de caractéristiques ou don'],
            5 => ['Attaque supplémentaire'],
        ],
        'Magicien' => [
            2 => ['Tradition arcanique'],
            3 => ['Emplacements de sorts de niveau 2'],
            4 => ['Amélioration de caractéristiques ou don', 'Sort mineur supplémentaire'],
            5 => ['Emplacements de sorts de niveau 3'],
        ],
        'Roublard' => [
            2 => ['Ruse'],
            3 => ['Archétype de roublard', 'Attaque sournoise 2d6'],
            4 => ['Amélioration de caractéristiques ou don'],
            5 => ['Esquive instinctive', 'Attaque sournoise 3d6'],
        ],
    ];

    /** Dons PHB 2014 représentables par la fiche actuelle. Les autres effets sont conservés comme effets passifs. */
    private const FEATS = [
        'Alerte' => ['resume' => 'Améliore fortement l’initiative et la vigilance.', 'effets' => ['initiative' => 5]],
        'Athlète' => ['resume' => 'Améliore la mobilité et augmente Force ou Dextérité de 1.', 'choix_stat' => ['force', 'dexterite']],
        'Acteur' => ['resume' => 'Augmente Charisme de 1 et améliore l’imitation et le déguisement.', 'stat' => 'charisme'],
        'Résilient' => ['resume' => 'Augmente une caractéristique de 1 et donne la maîtrise de ses jets de sauvegarde.', 'choix_stat' => self::STATS],
        'Robuste' => ['resume' => 'Augmente le maximum de points de vie de 2 par niveau.', 'effets' => ['pv_par_niveau' => 2]],
        'Durable' => ['resume' => 'Augmente Constitution de 1 et améliore la récupération avec les dés de vie.', 'stat' => 'constitution'],
        'Observateur' => ['resume' => 'Augmente Intelligence ou Sagesse de 1 et améliore l’observation.', 'choix_stat' => ['intelligence', 'sagesse']],
        'Maître des armures lourdes' => ['resume' => 'Augmente Force de 1 et réduit certains dégâts physiques en armure lourde.', 'stat' => 'force'],
        'Maître des armures intermédiaires' => ['resume' => 'Augmente Force ou Dextérité de 1 et améliore l’usage des armures intermédiaires.', 'choix_stat' => ['force', 'dexterite']],
        'Maître des armes' => ['resume' => 'Augmente Force ou Dextérité de 1 et accorde des maîtrises d’armes.', 'choix_stat' => ['force', 'dexterite']],
        'Maître des armes à deux mains' => ['resume' => 'Améliore l’efficacité avec les armes lourdes à deux mains.'],
        'Maître du bouclier' => ['resume' => 'Ajoute des options défensives et offensives avec un bouclier.'],
        'Duelliste défensif' => ['resume' => 'Permet d’utiliser sa réaction pour renforcer sa CA avec une arme de finesse.'],
        'Mobile' => ['resume' => 'Augmente la vitesse et facilite les déplacements au corps à corps.', 'effets' => ['vitesse' => 10]],
        'Chanceux' => ['resume' => 'Accorde trois points de chance après un repos long.'],
        'Initié à la magie' => ['resume' => 'Accorde une petite initiation à la magie d’une autre classe.'],
        'Lanceur de sorts de guerre' => ['resume' => 'Aide à maintenir la concentration et à lancer des sorts au combat.'],
        'Tireur d’élite' => ['resume' => 'Améliore les attaques à distance à longue portée et contre le couvert.'],
        'Bagarreur de tavernes' => ['resume' => 'Augmente Force ou Constitution de 1 et améliore le combat improvisé.', 'choix_stat' => ['force', 'constitution']],
    ];

    public function apercu(Personnage $personnage): array
    {
        $personnage->loadMissing(['classe', 'sousClasse', 'sousClasse.classe', 'abilites']);
        $this->verifierPeutMonter($personnage);
        $niveau = $personnage->niveau + 1;
        $nomClasse = $personnage->classe->nom;

        return [
            'niveau_depart' => $personnage->niveau,
            'niveau_arrivee' => $niveau,
            'capacites' => self::PROGRESSION[$nomClasse][$niveau] ?? [],
            'bonus_maitrise' => $niveau >= 5 ? 3 : 2,
            'pv_base' => $this->gainPvFixe($personnage->classe->de_de_vie),
            'de_vie_faces' => $this->facesDeVie($personnage->classe->de_de_vie),
            'modificateur_constitution' => $this->modificateur($personnage->abilites->constitution),
            'choix_sous_classe' => $personnage->sous_classe_id === null && (int) $personnage->classe->niveau_sous_classe === $niveau,
            'choix_asi' => $niveau === 4,
            'sous_classes' => $personnage->classe->sousClasses()->get(),
            'feats' => $niveau === 4 ? self::FEATS : [],
        ];
    }

    public function monter(Personnage $personnage, array $choix): MonteeNiveau
    {
        return DB::transaction(function () use ($personnage, $choix) {
            $personnage->refresh()->load(['classe', 'abilites']);
            $apercu = $this->apercu($personnage);
            $niveauDepart = $personnage->niveau;
            $niveauArrivee = $apercu['niveau_arrivee'];
            $ancienModCon = $this->modificateur($personnage->abilites->constitution);
            $changements = ['capacites' => $apercu['capacites']];

            if ($apercu['choix_sous_classe']) {
                $sousClasseId = (int) ($choix['sous_classe_id'] ?? 0);
                $sousClasse = SousClasse::query()
                    ->whereKey($sousClasseId)
                    ->where('classe_id', $personnage->classe_id)
                    ->first();
                if (! $sousClasse) {
                    throw ValidationException::withMessages(['sous_classe_id' => 'Choisissez une sous-classe valide.']);
                }
                $personnage->sous_classe_id = $sousClasse->id;
                $changements['sous_classe'] = $sousClasse->nom;
                $changements['capacites'][] = 'Capacité de sous-classe : '.$sousClasse->nom;
            }

            if ($apercu['choix_asi']) {
                $this->appliquerAsiOuFeat($personnage, $choix, $changements);
            }

            $modePv = $choix['mode_pv'] ?? 'fixe';
            if (! in_array($modePv, ['fixe', 'de'], true)) {
                throw ValidationException::withMessages(['mode_pv' => 'Choisissez la valeur fixe ou le lancer du dé de vie.']);
            }
            $choix['mode_pv'] = $modePv;

            $jetDeVie = null;
            $basePv = $apercu['pv_base'];
            if ($modePv === 'de') {
                $faces = $apercu['de_vie_faces'];
                if ($faces < 1) {
                    throw ValidationException::withMessages(['mode_pv' => 'Le dé de vie de cette classe est invalide.']);
                }
                $jetDeVie = filter_var($choix['jet_de_vie'] ?? null, FILTER_VALIDATE_INT);
                if ($jetDeVie === false || $jetDeVie < 1 || $jetDeVie > $faces) {
                    throw ValidationException::withMessages(['jet_de_vie' => 'Lancez le dé de vie avant de confirmer la montée de niveau.']);
                }
                $choix['jet_de_vie'] = $jetDeVie;
                $basePv = $jetDeVie;
            }

            $nouveauModCon = $this->modificateur($personnage->abilites->constitution);
            $gainPv = $basePv + $nouveauModCon;
            // Une hausse du modificateur de CON augmente aussi les PV des niveaux déjà acquis (PHB 2014).
            $gainPv += ($nouveauModCon - $ancienModCon) * $niveauDepart;
            $gainPv = max(1, $gainPv);

            $personnage->niveau = $niveauArrivee;
            $personnage->point_de_vie += $gainPv;
            $personnage->capacites = array_values(array_unique(array_merge($personnage->capacites ?? [], $changements['capacites'])));
            $personnage->save();
            $personnage->abilites->save();

            $changements['mode_pv'] = $modePv;
            $changements['pv_base_obtenu'] = $basePv;
            $changements['jet_de_vie'] = $jetDeVie;
            $changements['pv_gagnes'] = $gainPv;
            $changements['pv_total'] = $personnage->point_de_vie;
            $changements['bonus_maitrise'] = $niveauArrivee >= 5 ? 3 : 2;

            return MonteeNiveau::create([
                'personnage_id' => $personnage->id,
                'niveau_depart' => $niveauDepart,
                'niveau_arrivee' => $niveauArrivee,
                'choix' => $choix,
                'changements' => $changements,
            ]);
        });
    }

    private function appliquerAsiOuFeat(Personnage $personnage, array $choix, array &$changements): void
    {
        $type = $choix['type_amelioration'] ?? null;
        if ($type === 'asi') {
            $stats = $choix['asi'] ?? [];
            $total = 0;
            foreach (self::STATS as $stat) {
                $gain = (int) ($stats[$stat] ?? 0);
                if ($gain < 0 || $gain > 2) {
                    throw ValidationException::withMessages(['asi' => 'Chaque augmentation doit être comprise entre 0 et 2.']);
                }
                $total += $gain;
                if ($personnage->abilites->$stat + $gain > 20) {
                    throw ValidationException::withMessages(['asi' => 'Une amélioration de caractéristiques ne peut pas dépasser 20.']);
                }
            }
            if ($total !== 2 || count(array_filter($stats, fn ($v) => (int) $v > 0)) > 2) {
                throw ValidationException::withMessages(['asi' => 'Répartissez exactement 2 points : +2 à une caractéristique ou +1 à deux caractéristiques.']);
            }
            foreach (self::STATS as $stat) {
                $personnage->abilites->$stat += (int) ($stats[$stat] ?? 0);
            }
            $changements['asi'] = $stats;

            return;
        }

        if ($type === 'feat') {
            $nom = $choix['feat'] ?? '';
            $feat = self::FEATS[$nom] ?? null;
            if (! $feat) {
                throw ValidationException::withMessages(['feat' => 'Choisissez un don valide.']);
            }
            if (in_array($nom, $personnage->feats ?? [], true)) {
                throw ValidationException::withMessages(['feat' => 'Ce personnage possède déjà ce don.']);
            }

            $stat = $feat['stat'] ?? null;
            if (isset($feat['choix_stat'])) {
                $stat = $choix['feat_stat'] ?? null;
                if (! in_array($stat, $feat['choix_stat'], true)) {
                    throw ValidationException::withMessages(['feat_stat' => 'Choisissez une caractéristique valide pour ce don.']);
                }
            }
            if ($stat) {
                if ($personnage->abilites->$stat >= 20) {
                    throw ValidationException::withMessages(['feat' => 'Ce don ne peut pas augmenter cette caractéristique au-dessus de 20.']);
                }
                $personnage->abilites->$stat++;
                $changements['feat_stat'] = [$stat => 1];
            }

            if (($feat['effets']['pv_par_niveau'] ?? 0) > 0) {
                // Le don Robuste donne +2 PV pour chaque niveau, incluant le niveau atteint maintenant.
                $bonus = (int) $feat['effets']['pv_par_niveau'] * 4;
                $personnage->point_de_vie += $bonus;
                $changements['bonus_pv_feat'] = $bonus;
            }
            $personnage->feats = array_values(array_unique(array_merge($personnage->feats ?? [], [$nom])));
            $changements['feat'] = $nom;
            $changements['effets_feat'] = $feat['effets'] ?? [];

            return;
        }

        throw ValidationException::withMessages(['type_amelioration' => 'Choisissez une amélioration de caractéristiques ou un don.']);
    }

    private function verifierPeutMonter(Personnage $personnage): void
    {
        if ($personnage->niveau >= 5) {
            throw ValidationException::withMessages(['niveau' => 'Le niveau maximum pris en charge est 5.']);
        }
        if (! isset(self::PROGRESSION[$personnage->classe->nom])) {
            throw ValidationException::withMessages(['classe' => 'Cette classe n’est pas prise en charge pour la montée de niveau.']);
        }
    }

    private function gainPvFixe(?string $de): int
    {
        return match ($this->facesDeVie($de)) {
            12 => 7, 10 => 6, 8 => 5, 6 => 4, default => 1
        };
    }

    private function facesDeVie(?string $de): int
    {
        return (int) preg_replace('/\D/', '', (string) $de);
    }

    private function modificateur(int $score): int
    {
        return (int) floor(($score - 10) / 2);
    }
}
