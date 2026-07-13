<?php

namespace App\Http\Requests\Api\V1\Voyages;

use App\Enums\StatutChaloupeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Requête de validation pour la mise à jour d'une chaloupe.
 */
class UpdateChaloupeRequest extends FormRequest
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
        $id = $this->route('id') ?? $this->route('chaloupe');

        return [
            'imatriculation' => ['sometimes', 'string', 'max:255', 'unique:chaloupes,imatriculation,'.$id],
            'nom' => ['sometimes', 'string', 'max:255', 'unique:chaloupes,nom,'.$id],
            'capacite' => ['sometimes', 'integer', 'min:1'],
            'statut' => ['sometimes', new Enum(StatutChaloupeEnum::class)],
        ];
    }
}
