<?php

namespace App\Http\Requests\Api\V1\Users;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Requête de validation pour la création d'un utilisateur.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Seul un administrateur peut créer un utilisateur (et donc lui assigner un rôle).
     */
    public function authorize(): bool
    {
        return $this->user()?->role?->nom === RoleEnum::ADMIN;
    }

    /**
     * Règles de validation appliquées à la requête.
     */
    public function rules(): array
    {
        return [
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mot_de_passe' => ['required', 'string', 'min:8'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'role_id' => ['required', 'exists:roles,id'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
