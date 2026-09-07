<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courriel' => 'required|string|email|max:255|unique:utilisateurs,courriel',
            'mot_de_passe' => 'required|string|min:8|same:confirmation_mot_de_passe',
            'confirmation_mot_de_passe' => 'required|string',
        ];
    }

    /**
     * S'exécute automatiquement dès que la validation réussit.
     */
    protected function passedValidation(): void
    {
        $this->replace([
            'courriel' => $this->courriel,
            'mot_de_passe' => Hash::make($this->mot_de_passe),
        ]);
    }
}
