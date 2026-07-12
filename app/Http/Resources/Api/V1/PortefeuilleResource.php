<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation JSON pour un Portefeuille.
 */
class PortefeuilleResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'solde' => $this->solde,
            'user_id' => $this->user_id,
            'mouvements' => $this->whenLoaded('mouvements'),
            'created_at' => $this->created_at,
        ];
    }
}
