<?php

namespace App\Http\Requests;

use App\Models\Objet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Création d'un objet personnalisé ajouté directement à l'inventaire. */
class AjouterObjetPersonnaliseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gerer', $this->route('personnage'));
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in(Objet::TYPES)],
            'categorie' => 'nullable|string|max:50',
            'cout_mains' => 'nullable|integer|in:1,2',
            'quantite' => 'nullable|integer|min:1|max:999',
            'magique' => 'nullable|boolean',
            'rarete' => ['nullable', Rule::in(Objet::RARETES)],
            'bonus_toujours_actif' => 'nullable|boolean',
            'bonus' => 'nullable|array',
            'bonus.*' => 'nullable|integer|min:-20|max:20',
            'degats' => 'nullable|string|max:20',
            'type_degats' => ['nullable', Rule::in(['tranchant', 'perforant', 'contondant'])],
            'caracteristique_degats' => ['nullable', Rule::in(['force', 'dexterite'])],
            'ca_base' => 'nullable|integer|min:1|max:30',
            'effet_pv' => 'nullable|integer|min:-100|max:100',
            'duree_valeur' => 'nullable|integer|min:1|max:999',
            'duree_unite' => ['nullable', Rule::in(['tours', 'minutes'])],
        ];
    }
}
