<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
