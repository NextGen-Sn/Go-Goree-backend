<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation JSON pour une Demande de Résidence.
 */
class DemandeResidenceResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'carte_identite' => $this->carte_identite,
            'residence' => $this->residence,
            'statut' => $this->statut,
            'photo' => $this->photo,
            'motif_refus' => $this->motif_refus,
            'valide_par' => $this->valide_par,
            'date_validation' => $this->date_validation,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ];
    }
}
