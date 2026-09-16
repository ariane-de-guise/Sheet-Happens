<?php

namespace App\Services;

use App\Models\Personnage;

/**
 * Calcule les valeurs dérivées affichées sur la fiche de personnage
 * (consultation en lecture seule) : modificateurs, bonus de maîtrise,
 * jets de sauvegarde, compétences, perception passive, et le contenu de
 * la section "Actions" (sorts ou attaques selon la classe).
 *
 * Le schéma actuel (table `classes`) ne stocke pas encore les jets de
 * sauvegarde maîtrisés ni la caractéristique d'incantation d'une classe :
 * en attendant que ces colonnes existent, ce service s'appuie sur les
 * tables de référence 5e (SRD) ci-dessous, indexées par le nom de la
 * classe tel qu'enregistré par ClassesSeeder. Une classe absente de ces
 * tables (donnée non encore modélisée) est traitée comme non-maîtrisée /
 * non-lanceuse de sorts plutôt que de faire échouer l'affichage.
 */
class FicheDePersonnageService
{
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

    /** Caractéristique d'incantation des classes lanceuses de sorts (5e SRD). */
    private const CARACTERISTIQUE_INCANTATION_PAR_CLASSE = [
        'Clerc' => 'sagesse',
        'Magicien' => 'intelligence',
        'Druide' => 'sagesse',
        'Barde' => 'charisme',
        'Paladin' => 'charisme',
        'Rôdeur' => 'sagesse',
        'Ensorceleur' => 'charisme',
        'Occultiste' => 'charisme',
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

    /**
     * Armes reconnues parmi les choix d'équipement de départ proposés à la
     * création (voir PersonnageSheetController::edit()) : seules celles-ci
     * peuvent alimenter la section "Actions" des classes non lanceuses de
     * sorts, à partir de l'équipement réellement porté par le personnage.
     */
    private const ARMES = [
        'Épée' => ['degats' => '1d8', 'type' => 'tranchant', 'caracteristique' => 'force'],
        'Arc court' => ['degats' => '1d6', 'type' => 'perforant', 'caracteristique' => 'dexterite'],
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
     * @return array<int, array{aptitude: string, label: string, modificateur: int, maitrise: bool, bonus: int}>
     */
    public function jetsDeSauvegarde(Personnage $personnage): array
    {
        $abilites = $personnage->abilites;
        $nomClasse = $personnage->classe?->nom;
        $maitrisees = self::JETS_SAUVEGARDE_PAR_CLASSE[$nomClasse] ?? [];
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);

        $resultat = [];
        foreach (self::ABILITES as $cle => $label) {
            $mod = $this->modificateur($abilites?->$cle ?? 10);
            $maitrise = in_array($cle, $maitrisees, true);

            $resultat[] = [
                'aptitude' => $cle,
                'label' => $label,
                'modificateur' => $mod,
                'maitrise' => $maitrise,
                'bonus' => $mod + ($maitrise ? $bonusMaitrise : 0),
            ];
        }

        return $resultat;
    }

    /**
     * @return array<int, array{nom: string, aptitude: string, modificateur: int, maitrise: bool, bonus: int}>
     */
    public function competences(Personnage $personnage): array
    {
        $abilites = $personnage->abilites;
        $maitrisees = $personnage->listeCompetence->competences ?? [];
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);

        $resultat = [];
        foreach (self::COMPETENCES as $nom => $aptitude) {
            $mod = $this->modificateur($abilites?->$aptitude ?? 10);
            $maitrise = in_array($nom, $maitrisees, true);

            $resultat[] = [
                'nom' => $nom,
                'aptitude' => self::ABILITES[$aptitude],
                'modificateur' => $mod,
                'maitrise' => $maitrise,
                'bonus' => $mod + ($maitrise ? $bonusMaitrise : 0),
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

    public function estLanceurDeSorts(Personnage $personnage): bool
    {
        $nomClasse = $personnage->classe?->nom;

        return array_key_exists($nomClasse, self::CARACTERISTIQUE_INCANTATION_PAR_CLASSE);
    }

    /**
     * @return array{caracteristique: string, modificateur: int, dd: int, bonusAttaque: int}|null
     */
    public function statistiquesIncantation(Personnage $personnage): ?array
    {
        $nomClasse = $personnage->classe?->nom;
        $cle = self::CARACTERISTIQUE_INCANTATION_PAR_CLASSE[$nomClasse] ?? null;

        if (! $cle) {
            return null;
        }

        $mod = $this->modificateur($personnage->abilites?->$cle ?? 10);
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);

        return [
            'caracteristique' => self::ABILITES[$cle],
            'modificateur' => $mod,
            'dd' => 8 + $bonusMaitrise + $mod,
            'bonusAttaque' => $bonusMaitrise + $mod,
        ];
    }

    /**
     * @return array<int, array{nom: string, degats: string, type: string, bonusAttaque: int}>
     */
    public function attaques(Personnage $personnage): array
    {
        $equipement = $personnage->equipement ?? [];
        $bonusMaitrise = $this->bonusMaitrise($personnage->niveau);

        $resultat = [];
        foreach ($equipement as $item) {
            if (! isset(self::ARMES[$item])) {
                continue;
            }

            $arme = self::ARMES[$item];
            $mod = $this->modificateur($personnage->abilites?->{$arme['caracteristique']} ?? 10);

            $resultat[] = [
                'nom' => $item,
                'degats' => $arme['degats'].' '.$this->formaterBonus($mod),
                'type' => $arme['type'],
                'bonusAttaque' => $mod + $bonusMaitrise,
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
