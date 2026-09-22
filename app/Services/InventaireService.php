<?php

namespace App\Services;

use App\Models\EffetActif;
use App\Models\Objet;
use App\Models\Personnage;
use App\Models\PersonnageObjet;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Gère l'inventaire d'un personnage : ajout/retrait d'objets, quantités,
 * équipement (avec les règles DnD 5e de budget de "mains" et d'armure
 * unique) et utilisation d'objets consommables.
 *
 * Les bonus d'objets ne sont jamais écrits sur `abilites` ou `classes` :
 * ils sont recalculés dynamiquement à chaque appel de bonusActifs(), à
 * partir de ce qui est réellement équipé/actif au moment de l'appel — même
 * principe que FicheDePersonnageService pour le reste de la fiche.
 */
class InventaireService
{
    /** Nombre maximal de "mains" disponibles pour porter armes/bouclier. */
    private const BUDGET_MAINS = 2;

    public function ajouter(Personnage $personnage, Objet $objet, int $quantite = 1): PersonnageObjet
    {
        $ligne = $personnage->objets()->where('objet_id', $objet->id)->first()?->pivot;

        if ($ligne) {
            $ligne->update(['quantite' => $ligne->quantite + $quantite]);

            return $ligne;
        }

        return PersonnageObjet::create([
            'personnage_id' => $personnage->id,
            'objet_id' => $objet->id,
            'quantite' => $quantite,
            'quantite_equipee' => 0,
        ]);
    }

    /**
     * Ajuste la quantité d'une ligne d'inventaire (delta positif ou
     * négatif). Si la quantité restante passe sous le nombre d'exemplaires
     * équipés (ex. on vend une dague dual-wieldée), l'excédent équipé est
     * retiré automatiquement.
     *
     * Avec $permetSuppression à false (les boutons +/- de l'inventaire),
     * la quantité est plafonnée à 1 au lieu de faire disparaître l'objet :
     * seul retirer() doit pouvoir enlever un objet de l'inventaire. Avec
     * $permetSuppression à true (ex. consommer le dernier exemplaire d'une
     * potion via utiliser()), la ligne est supprimée quand la quantité
     * tombe à 0.
     */
    public function ajusterQuantite(PersonnageObjet $ligne, int $delta, bool $permetSuppression = true): void
    {
        $nouvelleQuantite = $ligne->quantite + $delta;

        if ($nouvelleQuantite <= 0) {
            if (! $permetSuppression) {
                return;
            }

            $ligne->delete();

            return;
        }

        $ligne->update([
            'quantite' => $nouvelleQuantite,
            'quantite_equipee' => min($ligne->quantite_equipee, $nouvelleQuantite),
        ]);
    }

    public function retirer(PersonnageObjet $ligne): void
    {
        $ligne->delete();
    }

    /**
     * Équipe un exemplaire de plus de cet objet (jusqu'à la quantité
     * possédée), après validation des règles DnD 5e d'équipement. Appelé
     * une fois par exemplaire à équiper : équiper deux dagues identiques
     * dans chaque main, c'est appeler equiper() deux fois sur la même
     * ligne, tant qu'on en possède au moins deux.
     *
     * @throws ValidationException si aucun exemplaire supplémentaire n'est
     *                             disponible, ou si la règle d'armure
     *                             unique ou de budget de mains est violée.
     */
    public function equiper(Personnage $personnage, PersonnageObjet $ligne): void
    {
        $objet = $ligne->objet;

        if (! in_array($objet->type, ['arme', 'armure', 'bouclier'], true)) {
            throw ValidationException::withMessages([
                'objet' => ["{$objet->nom} ne peut pas être équipé (ce n'est ni une arme, ni une armure, ni un bouclier)."],
            ]);
        }

        if ($ligne->quantite_equipee >= $ligne->quantite) {
            throw ValidationException::withMessages([
                'objet' => ["Impossible d'équiper un exemplaire de plus de {$objet->nom} : vous n'en possédez pas d'autre exemplaire disponible."],
            ]);
        }

        // Note : on ne rejette PAS la ligne courante ici, contrairement à
        // l'ancien modèle booléen — elle peut déjà contribuer au budget de
        // mains (ex. un premier exemplaire de cette même dague déjà en
        // main) et ce budget doit être compté avant d'ajouter l'exemplaire
        // suivant.
        $autresArmuresEquipees = $this->objetsEquipes($personnage)
            ->reject(fn (PersonnageObjet $l) => $l->id === $ligne->id)
            ->first(fn (PersonnageObjet $l) => $l->objet->type === 'armure');

        if ($objet->type === 'armure') {
            if ($autresArmuresEquipees) {
                throw ValidationException::withMessages([
                    'objet' => ["Impossible d'équiper {$objet->nom} : {$autresArmuresEquipees->objet->nom} est déjà portée. Déséquipez-la d'abord."],
                ]);
            }

            if ($ligne->quantite_equipee >= 1) {
                throw ValidationException::withMessages([
                    'objet' => ["{$objet->nom} est déjà portée : une armure ne peut être équipée qu'en un seul exemplaire."],
                ]);
            }
        }

        if (in_array($objet->type, ['arme', 'bouclier'], true)) {
            $occupantMains = $this->objetsEquipes($personnage)->filter(fn (PersonnageObjet $l) => in_array($l->objet->type, ['arme', 'bouclier'], true));
            $mainsUtilisees = $occupantMains->sum(fn (PersonnageObjet $l) => $l->objet->cout_mains * $l->quantite_equipee);

            if ($mainsUtilisees + $objet->cout_mains > self::BUDGET_MAINS) {
                $dejaEquipes = $occupantMains->map(fn (PersonnageObjet $l) => $l->objet->nom)->implode(' et ');

                throw ValidationException::withMessages([
                    'objet' => ["Impossible d'équiper {$objet->nom} (nécessite {$objet->cout_mains} main(s)) : {$dejaEquipes} occupe déjà {$mainsUtilisees} main(s) sur les ".self::BUDGET_MAINS.' disponibles.'],
                ]);
            }
        }

        $ligne->update(['quantite_equipee' => $ligne->quantite_equipee + 1]);
    }

    /**
     * Déséquipe un exemplaire de cet objet (appelé une fois par exemplaire
     * à retirer).
     */
    public function desequiper(PersonnageObjet $ligne): void
    {
        $ligne->update(['quantite_equipee' => max(0, $ligne->quantite_equipee - 1)]);
    }

    /**
     * Consomme une unité d'un objet consommable et applique son effet :
     * soin instantané (ex. {"pv": 7}) et/ou bonus temporaire, enregistré
     * dans `effets_actifs`. Un effet en minutes reçoit une vraie
     * expiration horaire (`expire_a`), qui disparaît d'elle-même — un
     * round de combat (6 secondes en jeu) n'ayant pas de correspondance
     * fiable avec le temps réel écoulé à table, un effet en tours reçoit
     * plutôt un compteur (`tours_restants`) que le joueur décrémente
     * lui-même (voir ajusterTours()).
     *
     * @throws ValidationException si l'objet n'est pas consommable.
     */
    public function utiliser(Personnage $personnage, PersonnageObjet $ligne): void
    {
        $objet = $ligne->objet;

        if (! $objet->est_consommable) {
            throw ValidationException::withMessages([
                'objet' => ["{$objet->nom} ne peut pas être utilisé de cette façon."],
            ]);
        }

        $effet = $objet->effet_utilisation ?? [];

        if (! empty($effet['pv'])) {
            $personnage->update(['point_de_vie' => max(0, $personnage->point_de_vie + (int) $effet['pv'])]);
        }

        if (! empty($effet['bonus']) && ! empty($effet['duree_valeur'])) {
            $enTours = ($effet['duree_unite'] ?? 'tours') === 'tours';

            EffetActif::create([
                'personnage_id' => $personnage->id,
                'objet_id' => $objet->id,
                'nom' => $objet->nom,
                'bonus' => $effet['bonus'],
                'unite' => $enTours ? 'tours' : 'minutes',
                'tours_restants' => $enTours ? (int) $effet['duree_valeur'] : null,
                'expire_a' => $enTours ? null : now()->addSeconds((int) $effet['duree_secondes']),
            ]);
        }

        $this->ajusterQuantite($ligne, -1);
    }

    /**
     * Décompte (ou recrédite) manuellement les tours restants d'un effet
     * temporaire en tours ; l'effet est retiré dès que le compteur atteint
     * 0, ce qui fait disparaître son bonus au prochain calcul.
     */
    public function ajusterTours(EffetActif $effet, int $delta): void
    {
        $restants = $effet->tours_restants + $delta;

        if ($restants <= 0) {
            $effet->delete();

            return;
        }

        $effet->update(['tours_restants' => $restants]);
    }

    /**
     * Retire les effets temporaires en minutes déjà expirés d'un
     * personnage. Purement une opération d'hygiène (évite d'accumuler des
     * lignes mortes) : la justesse des bonus ne dépend jamais de cet
     * appel, bonusActifs() ignore lui-même tout effet expiré qu'il
     * rencontre. Les effets en tours ne sont jamais purgés ainsi : ils ne
     * disparaissent que via ajusterTours(), à l'initiative du joueur.
     */
    public function purgerEffetsExpires(Personnage $personnage): void
    {
        $personnage->effetsActifs()
            ->where('unite', 'minutes')
            ->where('expire_a', '<=', now())
            ->delete();
    }

    /**
     * @return Collection<int, PersonnageObjet>
     */
    public function objetsEquipes(Personnage $personnage): Collection
    {
        return PersonnageObjet::query()
            ->where('personnage_id', $personnage->id)
            ->where('quantite_equipee', '>', 0)
            ->with('objet')
            ->get();
    }

    /**
     * Agrège les bonus actuellement actifs sur le personnage : objets
     * équipés, objets custom "toujours actifs", et effets temporaires en
     * cours (potions utilisées dont l'heure d'expiration n'est pas encore
     * passée). Jamais stocké : recalculé à chaque appel, donc un effet
     * expiré cesse de compter au tick près, sans action du joueur.
     *
     * @return array<string, int> ex. ['ca' => 3, 'force' => 1]
     */
    public function bonusActifs(Personnage $personnage): array
    {
        $bonus = [];

        foreach ($personnage->objets as $objet) {
            $equipe = $objet->pivot->quantite_equipee > 0;

            if (! $equipe && ! $objet->bonus_toujours_actif) {
                continue;
            }

            foreach ($objet->bonus ?? [] as $cle => $valeur) {
                $bonus[$cle] = ($bonus[$cle] ?? 0) + (int) $valeur;
            }
        }

        foreach ($personnage->effetsActifs as $effet) {
            if ($effet->estExpire()) {
                continue;
            }

            foreach ($effet->bonus ?? [] as $cle => $valeur) {
                $bonus[$cle] = ($bonus[$cle] ?? 0) + (int) $valeur;
            }
        }

        return $bonus;
    }

    /**
     * Objets équipés de type "arme", pour la section "Actions" de la fiche.
     * Une arme dual-wieldée (2 exemplaires identiques équipés) apparaît
     * deux fois, pour refléter les deux attaques disponibles.
     *
     * @return Collection<int, Objet>
     */
    public function armesEquipees(Personnage $personnage): Collection
    {
        return $personnage->objets
            ->filter(fn (Objet $objet) => $objet->type === 'arme' && $objet->pivot->quantite_equipee > 0)
            ->flatMap(fn (Objet $objet) => array_fill(0, $objet->pivot->quantite_equipee, $objet))
            ->values();
    }

    /**
     * Classe d'Armure du personnage, à partir de l'armure/bouclier
     * réellement équipés (logique 5e : armure légère = plein bonus de Dex,
     * intermédiaire = bonus de Dex plafonné à 2, lourde = aucun bonus de
     * Dex) plus tout bonus de CA additif actif (bouclier, objets divers).
     * Sans armure équipée : 10 + modificateur de Dex.
     */
    public function classeArmure(Personnage $personnage, int $modificateurDexterite): int
    {
        $armure = $personnage->objets->first(fn (Objet $objet) => $objet->type === 'armure' && $objet->pivot->quantite_equipee > 0);

        $ca = match ($armure?->categorie) {
            'legere' => (int) $armure->ca_base + $modificateurDexterite,
            'intermediaire' => (int) $armure->ca_base + min(2, $modificateurDexterite),
            'lourde' => (int) $armure->ca_base,
            default => 10 + $modificateurDexterite,
        };

        return $ca + ($this->bonusActifs($personnage)['ca'] ?? 0);
    }
}
