<?php

namespace App\Http\Requests;

use App\Models\Classe;
use App\Models\Historique;
use App\Models\Personnage;
use App\Models\Race;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Modification d'un personnage depuis le formulaire d'édition. Le niveau
 * et les caractéristiques n'y sont pas modifiables : ils n'évoluent qu'à
 * la montée de niveau (dé de vie, amélioration de caractéristiques ou don).
 */
class ModifierPersonnageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gerer', $this->personnage());
    }

    public function rules(): array
    {
        $champsEnvoyes = array_keys($this->except(['_token', '_method']));
        if (count($champsEnvoyes) === 1 && $champsEnvoyes[0] === 'point_de_vie') {
            return ['point_de_vie' => 'required|integer|min:1'];
        }

        $memeHistorique = fn (string $table) => Rule::exists($table, 'id')
            ->where(fn ($requeteSql) => $requeteSql->where('historique_id', $this->input('historique_id')));

        return [
            'nom' => 'required|string|max:100',
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(fn ($requeteSql) => $requeteSql->where('race_id', $this->input('race_id'))),
            ],
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(fn ($requeteSql) => $requeteSql->where('classe_id', $this->input('classe_id'))),
            ],
            'alignement' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'point_de_vie' => 'required|integer|min:1',
            'competences' => ['nullable', 'array'],
            'competences.*' => 'string|max:50',
            'langues' => ['nullable', 'array'],
            'langues.*' => 'string|max:50',
            'historique_id' => ['required', 'integer', 'exists:historiques,id'],
            'ideal_id' => ['required', 'integer', $memeHistorique('ideaux')],
            'defaut_id' => ['required', 'integer', $memeHistorique('defauts')],
            'attachement_id' => ['required', 'integer', $memeHistorique('attachements')],
            'personnalite_id' => ['required', 'integer', $memeHistorique('personnalites')],
        ];
    }

    public function messages(): array
    {
        return [
            'historique_id.required' => 'Vous devez choisir un historique.',
            'ideal_id.required' => 'Vous devez choisir un idéal.',
            'ideal_id.exists' => 'L’idéal choisi ne correspond pas à l’historique sélectionné.',
            'defaut_id.required' => 'Vous devez choisir un défaut.',
            'defaut_id.exists' => 'Le défaut choisi ne correspond pas à l’historique sélectionné.',
            'attachement_id.required' => 'Vous devez choisir un attachement.',
            'attachement_id.exists' => 'L’attachement choisi ne correspond pas à l’historique sélectionné.',
            'personnalite_id.required' => 'Vous devez choisir un trait de personnalité.',
            'personnalite_id.exists' => 'Le trait de personnalité choisi ne correspond pas à l’historique sélectionné.',
        ];
    }

    /** Règles qui dépendent de plusieurs champs et de l'état du personnage. */
    public function after(): array
    {
        return [function (Validator $validateur): void {
            if ($validateur->errors()->isNotEmpty()) {
                return;
            }

            $personnage = $this->personnage();
            $champsEnvoyes = array_keys($this->except(['_token', '_method']));
            if (count($champsEnvoyes) === 1 && $champsEnvoyes[0] === 'point_de_vie') {
                [$minimum, $maximum] = $this->bornesPointsDeVie(
                    (int) $personnage->niveau,
                    (int) $personnage->abilites->constitution,
                    $personnage->classe,
                );
                $pv = (int) $this->input('point_de_vie');
                if ($pv < $minimum || $pv > $maximum) {
                    $validateur->errors()->add('point_de_vie', "Les PV doivent être compris entre $minimum et $maximum pour ce niveau, cette classe et cette Constitution.");
                }

                return;
            }

            $niveau = (int) $personnage->niveau;
            $race = Race::with('sousRaces')->find($this->input('race_id'));
            $classe = $this->nouvelleClasse();

            if ($race->sousRaces->isNotEmpty() && ! $this->filled('sous_race_id')) {
                $validateur->errors()->add('sous_race_id', 'Vous devez choisir une sous-race pour cette race.');
            }

            // Un changement de classe est un reclassement complet : les
            // limites (compétences, dé de vie, sous-classe) suivent la
            // nouvelle classe.
            if ($this->filled('sous_classe_id') && $classe->niveau_sous_classe > $niveau) {
                $validateur->errors()->add('sous_classe_id', "Cette sous-classe n'est disponible qu'à partir du niveau {$classe->niveau_sous_classe}.");
            } elseif (! $this->filled('sous_classe_id') && $classe->niveau_sous_classe <= $niveau && $classe->sousClasses()->exists()) {
                $validateur->errors()->add('sous_classe_id', 'Vous devez choisir une sous-classe pour cette classe.');
            }

            $maxCompetences = $classe->nombre_competences ?? 2;
            $competencesClasse = array_values(array_diff($this->competencesFinales(), $this->competencesHistoriques()));
            $anciennesCompetencesClasse = array_values(array_diff(
                array_unique($personnage->listeCompetence->competences ?? []),
                $personnage->historique?->competences ?? [],
            ));
            if (! $this->memesElements($competencesClasse, $anciennesCompetencesClasse) && count($competencesClasse) > $maxCompetences) {
                $validateur->errors()->add('competences', "Vous ne pouvez avoir que $maxCompetences compétence(s) à ce niveau.");
            }

            $maxLangues = 2 + intdiv($niveau - 1, 3);
            if (! $this->memesElements($this->languesFinales(), $personnage->langues ?? []) && count($this->languesFinales()) > $maxLangues) {
                $validateur->errors()->add('langues', "Vous ne pouvez avoir que $maxLangues langue(s) à ce niveau.");
            }

            if ((int) $this->input('point_de_vie') !== (int) $personnage->point_de_vie) {
                [$minimum, $maximum] = $this->bornesPointsDeVie($niveau, (int) $personnage->abilites->constitution, $classe);
                $pv = (int) $this->input('point_de_vie');
                if ($pv < $minimum || $pv > $maximum) {
                    $validateur->errors()->add('point_de_vie', "Les PV doivent être compris entre $minimum et $maximum pour ce niveau, cette classe et cette Constitution.");
                }
            }
        }];
    }

    public function personnage(): Personnage
    {
        return $this->route('personnage')->loadMissing(['abilites', 'listeCompetence', 'historique']);
    }

    public function nouvelleClasse(): Classe
    {
        return Classe::findOrFail($this->input('classe_id'));
    }

    /**
     * Compétences finales : celles cochées, plus celles (fixes) de l'ancien
     * et du nouvel historique.
     */
    public function competencesFinales(): array
    {
        return array_values(array_unique(array_merge($this->input('competences', []), $this->competencesHistoriques())));
    }

    public function languesFinales(): array
    {
        return array_values(array_unique($this->input('langues', [])));
    }

    private function competencesHistoriques(): array
    {
        $ids = array_unique(array_filter([$this->personnage()->historique_id, $this->input('historique_id')]));

        return Historique::whereIn('id', $ids)->get()->flatMap(fn (Historique $h) => $h->competences ?? [])->all();
    }

    private function memesElements(array $a, array $b): bool
    {
        sort($a);
        sort($b);

        return $a === $b;
    }

    /**
     * Bornes des PV maximum : dé de vie + mod. CON au niveau 1, puis entre
     * 1 + mod. CON (minimum 1) et dé + mod. CON par niveau supplémentaire.
     */
    private function bornesPointsDeVie(int $niveau, int $constitution, Classe $classe): array
    {
        $de = (int) preg_replace('/\D/', '', (string) ($classe->de_de_vie ?? '10'));
        $modCon = (int) floor(($constitution - 10) / 2);
        $premierNiveau = max(1, $de + $modCon);

        return [
            $premierNiveau + max(0, $niveau - 1) * max(1, 1 + $modCon),
            $premierNiveau + max(0, $niveau - 1) * max(1, $de + $modCon),
        ];
    }
}
