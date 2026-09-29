<?php

namespace App\Services;

use App\Models\Capacite;
use App\Models\Don;
use App\Models\MonteeNiveau;
use App\Models\Personnage;
use App\Models\SousClasse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonteeNiveauService
{
    private const STATS = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];

    /** Seuil d'expérience (PHB 2014) pour passer du niveau (clé) au suivant. */
    private const XP_PAR_NIVEAU = [
        1 => 300,
        2 => 900,
        3 => 2700,
        4 => 6500,
    ];

    /**
     * Classes dont la progression (niveaux 1 à 5) est prise en charge. Les
     * capacités gagnées à chaque niveau viennent de la table capacites et
     * les dons proposés, de la table dons.
     */
    private const CLASSES_SUPPORTEES = ['Barbare', 'Clerc', 'Guerrier', 'Magicien', 'Roublard'];

    public function __construct(private readonly CapaciteService $capacites) {}

    /**
     * Seuil d'expérience cumulée à atteindre pour pouvoir monter au niveau
     * suivant, ou null si le personnage a atteint le niveau maximum pris
     * en charge (5).
     */
    public function xpProchainNiveau(Personnage $personnage): ?int
    {
        return self::XP_PAR_NIVEAU[$personnage->niveau] ?? null;
    }

    /** Le personnage a-t-il assez d'expérience pour monter de niveau ? */
    public function peutMonterDeNiveau(Personnage $personnage): bool
    {
        return $this->peutAtteindreNiveau($personnage, $personnage->niveau + 1);
    }

    /**
     * Expérience cumulée requise pour atteindre un niveau, ou null si ce
     * niveau n'est pas pris en charge (au-delà de 5).
     */
    public function xpRequisePourNiveau(int $niveau): ?int
    {
        return $niveau <= 1 ? 0 : (self::XP_PAR_NIVEAU[$niveau - 1] ?? null);
    }

    /**
     * Un personnage peut toujours rester à son niveau ou redescendre ; pour
     * monter, son expérience doit atteindre le seuil du niveau visé.
     */
    public function peutAtteindreNiveau(Personnage $personnage, int $niveau): bool
    {
        if ($niveau <= $personnage->niveau) {
            return true;
        }

        $seuil = $this->xpRequisePourNiveau($niveau);

        return $seuil !== null && $personnage->experience >= $seuil;
    }

    /** Message d'erreur expliquant pourquoi le niveau visé est refusé. */
    public function messageExperienceInsuffisante(Personnage $personnage, int $niveau): string
    {
        $seuil = $this->xpRequisePourNiveau($niveau);

        return $seuil === null
            ? 'Le niveau maximum pris en charge est 5.'
            : "Il faut {$seuil} points d'expérience pour atteindre le niveau {$niveau} ({$personnage->experience} actuellement).";
    }

    public function apercu(Personnage $personnage): array
    {
        $personnage->loadMissing(['classe', 'sousClasse', 'sousClasse.classe', 'abilites']);
        $this->verifierPeutMonter($personnage);
        $niveau = $personnage->niveau + 1;

        return [
            'niveau_depart' => $personnage->niveau,
            'niveau_arrivee' => $niveau,
            'capacites' => $this->capacites->capacitesDuNiveau($personnage->classe_id, $personnage->sous_classe_id, $niveau),
            'bonus_maitrise' => $niveau >= 5 ? 3 : 2,
            'pv_base' => $this->gainPvFixe($personnage->classe->de_de_vie),
            'de_vie_faces' => $this->facesDeVie($personnage->classe->de_de_vie),
            'modificateur_constitution' => $this->modificateur($personnage->abilites->constitution),
            'choix_sous_classe' => $personnage->sous_classe_id === null && (int) $personnage->classe->niveau_sous_classe === $niveau,
            'choix_amelioration' => $niveau === 4,
            'sous_classes' => $personnage->classe->sousClasses()->get(),
            'dons' => $niveau === 4 ? Don::query()->orderBy('nom')->get()->keyBy('nom')->all() : [],
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
            $changements = ['capacites' => $apercu['capacites']->pluck('nom')->all()];

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
                // Les capacités de sous-classe des niveaux déjà atteints sont acquises d'un coup.
                $changements['capacites'] = array_merge(
                    $changements['capacites'],
                    Capacite::query()
                        ->where('sous_classe_id', $sousClasse->id)
                        ->where('niveau', '<=', $niveauArrivee)
                        ->orderBy('niveau')
                        ->pluck('nom')
                        ->all(),
                );
            }

            if ($apercu['choix_amelioration']) {
                $this->appliquerAmeliorationOuDon($personnage, $choix, $changements);
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
            $personnage->save();
            $personnage->abilites->save();
            $this->capacites->synchroniser($personnage);

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

    private function appliquerAmeliorationOuDon(Personnage $personnage, array $choix, array &$changements): void
    {
        $type = $choix['type_amelioration'] ?? null;
        if ($type === 'caracteristiques') {
            $stats = $choix['caracteristiques'] ?? [];
            $total = 0;
            foreach (self::STATS as $stat) {
                $gain = (int) ($stats[$stat] ?? 0);
                if ($gain < 0 || $gain > 2) {
                    throw ValidationException::withMessages(['caracteristiques' => 'Chaque augmentation doit être comprise entre 0 et 2.']);
                }
                $total += $gain;
                if ($personnage->abilites->$stat + $gain > 20) {
                    throw ValidationException::withMessages(['caracteristiques' => 'Une amélioration de caractéristiques ne peut pas dépasser 20.']);
                }
            }
            if ($total !== 2 || count(array_filter($stats, fn ($v) => (int) $v > 0)) > 2) {
                throw ValidationException::withMessages(['caracteristiques' => 'Répartissez exactement 2 points : +2 à une caractéristique ou +1 à deux caractéristiques.']);
            }
            foreach (self::STATS as $stat) {
                $personnage->abilites->$stat += (int) ($stats[$stat] ?? 0);
            }
            $changements['caracteristiques'] = $stats;

            return;
        }

        if ($type === 'don') {
            $don = Don::query()->where('nom', $choix['don'] ?? '')->first();
            if (! $don) {
                throw ValidationException::withMessages(['don' => 'Choisissez un don valide.']);
            }
            $nom = $don->nom;
            if ($personnage->dons()->whereKey($don->id)->exists()) {
                throw ValidationException::withMessages(['don' => 'Ce personnage possède déjà ce don.']);
            }

            $stat = $don->stat;
            if (! empty($don->choix_stat)) {
                $stat = $choix['don_caracteristique'] ?? null;
                if (! in_array($stat, $don->choix_stat, true)) {
                    throw ValidationException::withMessages(['don_caracteristique' => 'Choisissez une caractéristique valide pour ce don.']);
                }
            }
            if ($stat) {
                if ($personnage->abilites->$stat >= 20) {
                    throw ValidationException::withMessages(['don' => 'Ce don ne peut pas augmenter cette caractéristique au-dessus de 20.']);
                }
                $personnage->abilites->$stat++;
                $changements['don_caracteristique'] = [$stat => 1];
            }

            if (($don->effets['pv_par_niveau'] ?? 0) > 0) {
                // Le don Robuste donne +2 PV pour chaque niveau, incluant le niveau atteint maintenant.
                $bonus = (int) $don->effets['pv_par_niveau'] * 4;
                $personnage->point_de_vie += $bonus;
                $changements['bonus_pv_don'] = $bonus;
            }
            $personnage->dons()->attach($don->id);
            $changements['don'] = $nom;
            $changements['effets_don'] = $don->effets ?? [];

            return;
        }

        throw ValidationException::withMessages(['type_amelioration' => 'Choisissez une amélioration de caractéristiques ou un don.']);
    }

    private function verifierPeutMonter(Personnage $personnage): void
    {
        if ($personnage->niveau >= 5) {
            throw ValidationException::withMessages(['niveau' => 'Le niveau maximum pris en charge est 5.']);
        }
        if (! in_array($personnage->classe->nom, self::CLASSES_SUPPORTEES, true)) {
            throw ValidationException::withMessages(['classe' => 'Cette classe n’est pas prise en charge pour la montée de niveau.']);
        }
        if (! $this->peutMonterDeNiveau($personnage)) {
            throw ValidationException::withMessages([
                'experience' => $this->messageExperienceInsuffisante($personnage, $personnage->niveau + 1),
            ]);
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
