<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource Eloquent pour représenter un utilisateur dans les réponses de l'API.
 */
class UserResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prenom' => $this->prenom,
            'nom' => $this->nom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'active' => (bool) $this->active,
            'password_reset_at' => $this->password_reset_at,
            'invite_pending' => $this->password_reset_at === null && $this->relationLoaded('role') && $this->role && $this->role->nom === RoleEnum::AGENT,
            'traversees_count' => $this->relationLoaded('role') && $this->role && $this->role->nom === \App\Enums\RoleEnum::CLIENT ? $this->billets()->where('statut', \App\Enums\StatutBilletEnum::UTILISE->value)->count() : 0,
            'role' => $this->relationLoaded('role') && $this->role ? [
                'id' => $this->role->id,
                'nom' => $this->role->nom,
            ] : null,
            'portefeuille' => $this->relationLoaded('portefeuille') && $this->portefeuille ? [
                'solde' => $this->portefeuille->solde,
            ] : null,
            'est_resident' => (bool) $this->est_resident,
            // Abonnement actif (résident) : présent uniquement si la relation
            // resident.abonnements est chargée (endpoint /me). L'abonnement est
            // « actif » tant que sa date_fin est dans le futur.
            'abonnement' => $this->when(
                $this->relationLoaded('resident'),
                function () {
                    $actif = $this->resident
                        ? $this->resident->abonnements
                            ->first(fn ($a) => $a->date_fin && $a->date_fin->isFuture())
                        : null;

                    return [
                        'actif' => (bool) $actif,
                        'date_fin' => $actif?->date_fin?->toIso8601String(),
                        'plan' => $actif && $actif->plan ? [
                            'nom' => $actif->plan->nom,
                            'duree_mois' => $actif->plan->duree_mois,
                        ] : null,
                    ];
                }
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
