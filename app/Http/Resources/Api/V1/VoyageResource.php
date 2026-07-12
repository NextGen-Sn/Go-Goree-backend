<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ressource de présentation JSON pour un Voyage.
 */
class VoyageResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_voyage' => $this->date_voyage,
            'places' => $this->places,
            'places_restantes' => $this->places_restantes,
            'trajet' => $this->trajet,
            'chaloupe' => $this->chaloupe,
            'created_at' => $this->created_at,
        ];
    }
}
