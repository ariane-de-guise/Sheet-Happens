<?php

namespace App\Http\Requests;

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
            'nom' => ['required', 'string', 'max:100'],
            'race_id' => ['required', 'integer', 'exists:races,id'],
            'sous_race_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(function ($query) {
                    return $query->where('race_id', $this->input('race_id'));
                }),
            ],
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
            'sous_classe_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(function ($query) {
                    return $query->where('classe_id', $this->input('classe_id'));
                }),
            ],
            'niveau' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'alignement' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'point_de_vie' => ['required', 'integer', 'min:1', 'max:1000'],
            'abilites' => ['required', 'array'],
            'abilites.force' => ['required', 'integer', 'min:1', 'max:20'],
            'abilites.dexterite' => ['required', 'integer', 'min:1', 'max:20'],
            'abilites.constitution' => ['required', 'integer', 'min:1', 'max:20'],
            'abilites.intelligence' => ['required', 'integer', 'min:1', 'max:20'],
            'abilites.sagesse' => ['required', 'integer', 'min:1', 'max:20'],
            'abilites.charisme' => ['required', 'integer', 'min:1', 'max:20'],
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
