<?php

namespace App\Http\Requests\Api\V1\Voyages;

use App\Enums\StatutChaloupeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Requête de validation pour la création d'une chaloupe.
 */
class StoreChaloupeRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation appliquées à la requête.
     */
    public function rules(): array
    {
        return [
            'imatriculation' => ['required', 'string', 'max:255', 'unique:chaloupes,imatriculation'],
            'nom' => ['required', 'string', 'max:255', 'unique:chaloupes,nom'],
            'capacite' => ['required', 'integer', 'min:1'],
            'statut' => ['sometimes', new Enum(StatutChaloupeEnum::class)],
        ];
    }
}
