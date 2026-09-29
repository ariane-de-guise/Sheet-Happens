<?php

namespace App\Services;

use App\Models\Personnage;

/**
 * Calcule les valeurs dérivées affichées sur la fiche de personnage
 * (consultation en lecture seule) : modificateurs, bonus de maîtrise,
 * jets de sauvegarde, compétences, perception passive, Classe d'Armure, et
 * le contenu de la section "Actions" (sorts ou attaques selon la classe).
 *
 * Le schéma actuel (table `classes`) ne stocke pas encore les jets de
 * sauvegarde maîtrisés ni la caractéristique d'incantation d'une classe :
 * en attendant que ces colonnes existent, ce service s'appuie sur les
 * tables de référence 5e (SRD) ci-dessous, indexées par le nom de la
 * classe tel qu'enregistré par ClassesSeeder. Une classe absente de ces
 * tables (donnée non encore modélisée) est traitée comme non-maîtrisée /
 * non-lanceuse de sorts plutôt que de faire échouer l'affichage.
 *
 * Les bonus d'objets équipés (InventaireService::bonusActifs()) sont
 * ajoutés aux scores de caractéristiques ici, à l'affichage seulement :
 * jamais écrits sur `abilites`, pour qu'ils disparaissent dès qu'un objet
 * est déséquipé.
 */
class FicheDePersonnageService
{
    public function __construct(private readonly InventaireService $inventaire) {}

    private const ABILITES = [
        'force' => 'Force',
        'dexterite' => 'Dextérité',
        'constitution' => 'Constitution',
        'intelligence' => 'Intelligence',
        'sagesse' => 'Sagesse',
        'charisme' => 'Charisme',
    ];

    /** Jets de sauvegarde maîtrisés par classe (référence 5e SRD). */
    private const JETS_SAUVEGARDE_PAR_CLASSE = [
        'Barbare' => ['force', 'constitution'],
        'Clerc' => ['sagesse', 'charisme'],
        'Roublard' => ['dexterite', 'intelligence'],
        'Magicien' => ['intelligence', 'sagesse'],
        'Guerrier' => ['force', 'constitution'],
        'Druide' => ['intelligence', 'sagesse'],
        'Barde' => ['dexterite', 'charisme'],
        'Moine' => ['force', 'dexterite'],
        'Paladin' => ['sagesse', 'charisme'],
        'Rôdeur' => ['force', 'dexterite'],
        'Ensorceleur' => ['constitution', 'charisme'],
        'Occultiste' => ['sagesse', 'charisme'],
    ];

    /** Caractéristique associée à chacune des 18 compétences (5e SRD). */
    private const COMPETENCES = [
        'Acrobaties' => 'dexterite',
        'Arcanes' => 'intelligence',
        'Athlétisme' => 'force',
        'Art de la duplicité' => 'charisme',
        'Discrétion' => 'dexterite',
        'Dressage' => 'sagesse',
        'Escamotage' => 'dexterite',
        'Histoire' => 'intelligence',
        'Intimidation' => 'charisme',
        'Intuition' => 'sagesse',
        'Investigation' => 'intelligence',
        'Médecine' => 'sagesse',
        'Nature' => 'intelligence',
        'Perception' => 'sagesse',
        'Performance' => 'charisme',
        'Persuasion' => 'charisme',
        'Religion' => 'intelligence',
        'Survie' => 'sagesse',
    ];

    public function modificateur(int $score): int
    {
        return (int) floor(($score - 10) / 2);
    }

    public function modificateurFormate(int $score): string
    {
        return $this->formaterBonus($this->modificateur($score));
    }

    public function bonusMaitrise(int $niveau): int
    {
        $niveau = max(1, min(20, $niveau));

        return 2 + intdiv($niveau - 1, 4);
    }

    public function formaterBonus(int $valeur): string
    {
        return $valeur >= 0 ? '+'.$valeur : (string) $valeur;
    }

    /**
     * Score d'une caractéristique augmenté des bonus d'objets actuellement
     * actifs (équipés, ou custom "toujours actifs", ou effets temporaires)
     * — jamais écrit en base, recalculé à chaque appel.
     */
    private function scoreEffectif(Personnage $personnage, string $cle): int
    {
        $base = $personnage->abilites?->$cle ?? 10;
        $bonus = $this->inventaire->bonusActifs($personnage)[$cle] ?? 0;

        return $base + $bonus;
    }

    /**
     * @return array<int, array{aptitude: string, libelle: string, modificateur: int, maitrise: bool, bonus: int}>
     */
    public function jetsDeSauvegarde(Personnage $personnage): array
    {
        $nomClasse = $personnage->classe?->nom;
        $maitrisees = self::JETS_SAUVEGARDE_PAR_CLASSE[$nomClasse] ?? [];
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);
        // Bonus d'objet à tous les jets de sauvegarde sans distinction
        // (ex. Cape/Anneau de protection), en plus du bonus par
        // caractéristique déjà inclus via scoreEffectif().
        $bonusObjetGlobal = $this->inventaire->bonusActifs($personnage)['bonus_jets_sauvegarde'] ?? 0;

        $resultat = [];
        foreach (self::ABILITES as $cle => $libelle) {
            $mod = $this->modificateur($this->scoreEffectif($personnage, $cle));
            $maitrise = in_array($cle, $maitrisees, true);

            $resultat[] = [
                'aptitude' => $cle,
                'libelle' => $libelle,
                'modificateur' => $mod,
                'maitrise' => $maitrise,
                'bonus' => $mod + ($maitrise ? $bonusMaitrise : 0) + $bonusObjetGlobal,
            ];
        }

        return $resultat;
    }

    /**
     * @return array<int, array{nom: string, aptitude: string, modificateur: int, maitrise: bool, bonus: int}>
     */
    public function competences(Personnage $personnage): array
    {
        $maitrisees = $personnage->listeCompetence?->competences ?? [];
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);
        // Bonus d'objet à toutes les compétences sans distinction, en plus
        // du bonus par caractéristique déjà inclus via scoreEffectif().
        $bonusObjetGlobal = $this->inventaire->bonusActifs($personnage)['bonus_competences'] ?? 0;

        $resultat = [];
        foreach (self::COMPETENCES as $nom => $aptitude) {
            $mod = $this->modificateur($this->scoreEffectif($personnage, $aptitude));
            $maitrise = in_array($nom, $maitrisees, true);

            $resultat[] = [
                'nom' => $nom,
                'aptitude' => self::ABILITES[$aptitude],
                'modificateur' => $mod,
                'maitrise' => $maitrise,
                'bonus' => $mod + ($maitrise ? $bonusMaitrise : 0) + $bonusObjetGlobal,
            ];
        }

        return $resultat;
    }

    public function perceptionPassive(Personnage $personnage): int
    {
        $perception = collect($this->competences($personnage))
            ->firstWhere('nom', 'Perception');

        return 10 + ($perception['bonus'] ?? 0);
    }

    /**
     * Classe d'Armure du personnage, dérivée de l'armure/bouclier
     * réellement équipés (voir InventaireService::classeArmure()).
     */
    public function classeArmure(Personnage $personnage): int
    {
        $modDex = $this->modificateur($this->scoreEffectif($personnage, 'dexterite'));

        return $this->inventaire->classeArmure($personnage, $modDex);
    }

    public function estLanceurDeSorts(Personnage $personnage): bool
    {
        $nomClasse = $personnage->classe?->nom;

        return array_key_exists($nomClasse, SortService::CARACTERISTIQUE_INCANTATION_PAR_CLASSE);
    }

    /**
     * @return array{caracteristique: string, modificateur: int, dd: int, bonusAttaque: int}|null
     */
    public function statistiquesIncantation(Personnage $personnage): ?array
    {
        $nomClasse = $personnage->classe?->nom;
        $cle = SortService::CARACTERISTIQUE_INCANTATION_PAR_CLASSE[$nomClasse] ?? null;

        if (! $cle) {
            return null;
        }

        $mod = $this->modificateur($this->scoreEffectif($personnage, $cle));
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);

        return [
            'caracteristique' => self::ABILITES[$cle],
            'modificateur' => $mod,
            'dd' => 8 + $bonusMaitrise + $mod,
            'bonusAttaque' => $bonusMaitrise + $mod,
        ];
    }

    /**
     * Attaques dérivées des armes réellement équipées (InventaireService),
     * et non plus d'une liste de noms en dur. Les armes magiques (+1/+2/+3)
     * ajoutent leur bonus au toucher et aux dégâts via `bonus.bonus_attaque`
     * / `bonus.bonus_degats` : comme le reste des bonus d'objets, ce sont
     * des totaux agrégés sur le personnage (InventaireService::bonusActifs),
     * pas un bonus distinct par arme équipée.
     *
     * @return array<int, array{nom: string, degats: string, type: string, bonusAttaque: int}>
     */
    public function attaques(Personnage $personnage): array
    {
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);
        $bonusMagique = $this->inventaire->bonusActifs($personnage);
        $bonusAttaqueMagique = $bonusMagique['bonus_attaque'] ?? 0;
        $bonusDegatsMagique = $bonusMagique['bonus_degats'] ?? 0;

        $resultat = [];
        foreach ($this->inventaire->armesEquipees($personnage) as $arme) {
            if (! $arme->degats) {
                continue;
            }

            $caracteristique = $arme->caracteristique_degats ?? 'force';
            $mod = $this->modificateur($this->scoreEffectif($personnage, $caracteristique));

            $resultat[] = [
                'nom' => $arme->nom,
                'degats' => $arme->degats.' '.$this->formaterBonus($mod + $bonusDegatsMagique),
                'type' => $arme->type_degats ?? '—',
                'bonusAttaque' => $mod + $bonusMaitrise + $bonusAttaqueMagique,
            ];
        }

        return $resultat;
    }

    /**
     * Contenu unifié de la section "Actions" de la fiche : sorts pour une
     * classe lanceuse de sorts, sinon attaques dérivées de l'équipement
     * porté.
     *
     * @return array{type: 'sorts'|'attaques', incantation: array|null, attaques: array<int, array>}
     */
    public function actions(Personnage $personnage): array
    {
        if ($this->estLanceurDeSorts($personnage)) {
            return [
                'type' => 'sorts',
                'incantation' => $this->statistiquesIncantation($personnage),
                'attaques' => [],
            ];
        }

        return [
            'type' => 'attaques',
            'incantation' => null,
            'attaques' => $this->attaques($personnage),
        ];
    }
    /**
     * Répartit l'inventaire entre l'équipement de combat et les objets
     * divers affichés sur la fiche.
     *
     * @return array{armure: array<int, string>, objets: array<int, string>}
     */
    public function equipementClasse(Personnage $personnage): array
    {
        $armure = [];
        $objets = [];

        foreach ($personnage->objets as $objet) {
            $nom = $objet->nom;
            $quantite = max(1, (int) ($objet->pivot->quantite ?? 1));
            $destination = in_array($objet->type, ['arme', 'armure', 'bouclier'], true)
                ? 'armure'
                : 'objets';

            for ($i = 0; $i < $quantite; $i++) {
                ${$destination}[] = $nom;
            }
        }

        return ['armure' => $armure, 'objets' => $objets];
    }

    /**
     * Section "Traits" : bonus et capacités du personnage, dérivés des
     * descriptions déjà enregistrées sur sa race, sous-race, classe et
     * sous-classe.
     *
     * @return array<int, array{label: string, texte: string}>
     */
    public function traits(Personnage $personnage): array
    {
        $sources = [
            'Race' => $personnage->race?->description,
            $personnage->sousRace?->nom => $personnage->sousRace?->description,
            'Classe' => $personnage->classe?->description,
            $personnage->sousClasse?->nom => $personnage->sousClasse?->description,
        ];

        $resultat = [];
        foreach ($sources as $label => $texte) {
            if (! empty($label) && ! empty($texte)) {
                $resultat[] = ['label' => $label, 'texte' => $texte];
            }
        }

        return $resultat;
    }
}
