<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['utilisateur_id', 'race_id', 'sous_race_id', 'classe_id', 'sous_classe_id', 'abilites_id', 'liste_competence_id', 'nom', 'photo', 'niveau', 'experience', 'alignement', 'historique_id', 'ideal_id', 'defaut_id', 'attachement_id', 'personnalite_id', 'description', 'point_de_vie', 'langues', 'capacites', 'feats'])]
class Personnage extends Model
{
    /**
     * Nombre maximum de personnages qu'un utilisateur peut posséder
     * simultanément. Au-delà, la création est bloquée tant qu'il n'en a
     * pas supprimé un.
     */
    public const MAX_PAR_UTILISATEUR = 5;

    protected function casts(): array
    {
        return [
            'langues' => 'array',
            'capacites' => 'array',
            'feats' => 'array',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function sousRace(): BelongsTo
    {
        return $this->belongsTo(SousRace::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function sousClasse(): BelongsTo
    {
        return $this->belongsTo(SousClasse::class);
    }

    public function historique(): BelongsTo
    {
        return $this->belongsTo(Historique::class);
    }

    public function ideal(): BelongsTo
    {
        return $this->belongsTo(Ideal::class);
    }

    public function defaut(): BelongsTo
    {
        return $this->belongsTo(Defaut::class);
    }

    public function attachement(): BelongsTo
    {
        return $this->belongsTo(Attachement::class);
    }

    public function personnalite(): BelongsTo
    {
        return $this->belongsTo(Personnalite::class);
    }

    public function abilites(): BelongsTo
    {
        return $this->belongsTo(Abilite::class, 'abilites_id');
    }

    public function monteesNiveau(): HasMany
    {
        return $this->hasMany(MonteeNiveau::class)->orderBy('niveau_arrivee');
    }

    public function listeCompetence(): BelongsTo
    {
        return $this->belongsTo(ListeCompetence::class, 'liste_competence_id');
    }

    public function objets(): BelongsToMany
    {
        return $this->belongsToMany(Objet::class, 'personnage_objets')
            ->using(PersonnageObjet::class)
            ->withPivot(['id', 'quantite', 'equipe'])
            ->withTimestamps();
    }

    public function effetsActifs(): HasMany
    {
        return $this->hasMany(EffetActif::class);
    }
}
