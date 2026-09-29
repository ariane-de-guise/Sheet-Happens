<?php

namespace App\Policies;

use App\Models\Personnage;
use App\Models\Utilisateur;

/**
 * Un personnage n'est consultable et modifiable que par l'utilisateur qui
 * l'a créé. Appliquée à toutes les routes /personnages/{personnage}/...
 * par le middleware « can:gerer,personnage » (voir routes/web.php).
 */
class PersonnagePolicy
{
    public function gerer(Utilisateur $utilisateur, Personnage $personnage): bool
    {
        return $personnage->utilisateur_id === $utilisateur->id;
    }
}
