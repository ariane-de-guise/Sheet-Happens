<?php

namespace App\Services;

use App\Models\Attachement;
use App\Models\Classe;
use App\Models\Defaut;
use App\Models\Ideal;
use App\Models\Personnalite;
use App\Models\Race;
use App\Models\SousRace;

/**
 * Calculs de l'assistant de création de personnage : bonus de
 * caractéristiques (fixes et « au choix »), points de vie de départ et
 * alignement déduit des traits d'historique.
 */
class CalculCreationService
{
    /**
     * Repère, parmi la race, la sous-race et la classe du personnage, celles
     * qui accordent un bonus "au choix" (ex. Humain Variant : 2 bonus de +1
     * à répartir librement) plutôt qu'un bonus fixe. Retourne, pour chaque
     * source concernée, sa configuration ['nombre' => int, 'bonus' => int].
     */
    public function bonusAuChoixDisponibles(array $donnees): array
    {
        $race = Race::find($donnees['race_id']);
        $sousRace = ! empty($donnees['sous_race_id']) ? SousRace::find($donnees['sous_race_id']) : null;
        $classe = Classe::find($donnees['classe_id']);

        $sources = [];
        foreach (['race' => $race, 'sous_race' => $sousRace, 'classe' => $classe] as $cle => $modele) {
            $auChoix = data_get($modele, 'bonus_aptitudes.au_choix');
            if (is_array($auChoix) && ! empty($auChoix['nombre']) && ! empty($auChoix['bonus'])) {
                $sources[$cle] = $auChoix;
            }
        }

        return $sources;
    }

    /**
     * Combine les 4 traits de personnalité (idéal, défaut, attachement,
     * trait de personnalité) en un seul alignement final à deux axes,
     * plutôt que de se baser uniquement sur l'idéal choisi.
     *
     * Chaque trait qui porte une étiquette d'alignement "vote" sur les
     * deux axes : "Loyal" vaut -1 et "Chaotique" vaut +1 sur l'axe loi ;
     * "Bon" vaut -1 et "Mauvais" vaut +1 sur l'axe bien. Un trait sans
     * étiquette, ou dont l'étiquette est simplement "Neutre", ne fait
     * pencher aucun axe. Le signe de la somme des votes sur chaque axe
     * donne le mot final (Loyal/Neutre/Chaotique, puis Bon/Neutre/
     * Mauvais) ; une somme nulle (aucun vote, ou des votes qui
     * s'annulent) donne "Neutre" sur cet axe.
     *
     * NB : ce système de "vote" est un choix de conception maison, pas
     * une règle officielle de D&D 5e — dans les livres, seuls les idéaux
     * suggèrent un alignement ; les défauts, attachements et traits de
     * personnalité n'en portent pas officiellement. Les étiquettes
     * ajoutées ici dans HistoriquesSeeder reflètent simplement ce que le
     * texte de chaque trait semble indiquer.
     */
    public function alignement(?Ideal $ideal, ?Defaut $defaut, ?Attachement $attachement, ?Personnalite $personnalite): string
    {
        $loiScore = 0;
        $bienScore = 0;

        foreach ([$ideal, $defaut, $attachement, $personnalite] as $trait) {
            $alignement = $trait?->alignement;

            if (! $alignement) {
                continue;
            }

            if (str_contains($alignement, 'Loyal')) {
                $loiScore--;
            } elseif (str_contains($alignement, 'Chaotique')) {
                $loiScore++;
            }

            if (str_contains($alignement, 'Bon')) {
                $bienScore--;
            } elseif (str_contains($alignement, 'Mauvais')) {
                $bienScore++;
            }
        }

        $loi = match (true) {
            $loiScore < 0 => 'Loyal',
            $loiScore > 0 => 'Chaotique',
            default => 'Neutre',
        };

        // Ces deux mots (avec cette casse) doivent correspondre exactement
        // aux options du select "Alignement" de resources/views/personnages/edit.blade.php,
        // sinon la valeur enregistrée ne s'y retrouve jamais présélectionnée.
        $bien = match (true) {
            $bienScore < 0 => 'bon',
            $bienScore > 0 => 'méchant',
            default => 'neutre',
        };

        if ($loi === 'Neutre' && $bien === 'neutre') {
            return 'Neutre';
        }

        if ($loi === 'Neutre') {
            return "Neutre $bien";
        }

        if ($bien === 'neutre') {
            return "$loi neutre";
        }

        return "$loi $bien";
    }

    public function pointsDeVieInitiaux(array $abilites, ?Classe $classe): int
    {
        $conMod = floor(($abilites['constitution'] ?? 8) - 10) / 2;
        $conMod = floor($conMod);

        if ($classe && $classe->de_de_vie) {
            $de = (int) str_replace('d', '', $classe->de_de_vie);

            return max(1, $de + $conMod);
        }

        return 10 + $conMod;
    }

    public function abilitesAvecBonus(array $abilites, array $donnees): array
    {
        $race = Race::find($donnees['race_id']);
        $sousRace = ! empty($donnees['sous_race_id']) ? SousRace::find($donnees['sous_race_id']) : null;
        $classe = Classe::find($donnees['classe_id']);

        foreach ($abilites as $caracteristique => &$score) {
            $score += (int) data_get($race, "bonus_aptitudes.$caracteristique", 0);
            $score += (int) data_get($sousRace, "bonus_aptitudes.$caracteristique", 0);
            $score += (int) data_get($classe, "bonus_aptitudes.$caracteristique", 0);
        }
        unset($score);

        // Bonus "au choix" : la caractéristique concrète est celle choisie
        // par le joueur à l'étape des caractéristiques (voir
        // CreationPersonnageController::enregistrerCaracteristiques()), pas une caractéristique fixe déclarée
        // sur la race/sous-race/classe.
        $auChoixDisponibles = $this->bonusAuChoixDisponibles($donnees);
        foreach ($auChoixDisponibles as $source => $config) {
            foreach ($donnees['bonus_choix'][$source] ?? [] as $caracteristique) {
                if (array_key_exists($caracteristique, $abilites)) {
                    $abilites[$caracteristique] += (int) $config['bonus'];
                }
            }
        }

        return $abilites;
    }
}
