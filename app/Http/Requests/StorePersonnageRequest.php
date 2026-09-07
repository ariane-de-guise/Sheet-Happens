<?php

namespace App\Http\Requests;

use App\Models\SousClasse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonnageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $niveau = $this->input('niveau', 1);

        return [
            'utilisateur_id' => ['required', 'integer', 'exists:users,id'],
            'nom' => ['required', 'string', 'max:100'],
            'race_id' => ['required', 'integer', 'exists:races,id'],
            'sous_race_id' => ['nullable', 'integer', 'exists:sous_races,id'],
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
            'sous_classe_id' => [
                'nullable',
                'integer',
                'exists:sous_classes,id',
                Rule::exists('sous_classes', 'id')->where(function ($query) {
                    return $query->where('niveau_sous_classe', '<=', $this->input('niveau', 1));
                }),
            ],
            'niveau' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'alignement' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'point_de_vie' => ['required', 'integer', 'min:1', 'max:1000'],
            'abilites' => ['required', 'array'],
            'abilites.force' => ['required', 'integer', 'min:1', 'max:30'],
            'abilites.dexterite' => ['required', 'integer', 'min:1', 'max:30'],
            'abilites.constitution' => ['required', 'integer', 'min:1', 'max:30'],
            'abilites.intelligence' => ['required', 'integer', 'min:1', 'max:30'],
            'abilites.sagesse' => ['required', 'integer', 'min:1', 'max:30'],
            'abilites.charisme' => ['required', 'integer', 'min:1', 'max:30'],
            'competences' => ['sometimes', 'array'],
            'competences.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'sous_classe_id.exists' => 'La sous-classe sélectionnée n\'est pas disponible au niveau actuel de votre personnage.',
        ];
    }
}