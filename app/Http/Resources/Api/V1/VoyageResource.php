<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Voyage;
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
            'billets_vendus' => (int) $this->agregatVentes('billets_vendus'),
            'recette' => (float) $this->agregatVentes('recette'),
            'trajet' => $this->trajet,
            'chaloupe' => $this->chaloupe,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Récupère un agrégat de vente (`billets_vendus` ou `recette`).
     *
     * La valeur vient du scope `withVentes()` quand la requête l'a appliqué ;
     * sinon elle est calculée à la volée. On ne renvoie jamais 0 par défaut :
     * un chiffre d'affaires faux serait pire qu'une requête supplémentaire.
     */
    private function agregatVentes(string $attribut): int|float
    {
        if (isset($this->resource->{$attribut})) {
            return $this->resource->{$attribut};
        }

        $billetsVendus = $this->billets()->whereIn('statut', Voyage::STATUTS_VENDUS);

        return $attribut === 'recette' ? $billetsVendus->sum('montant') : $billetsVendus->count();
    }
}
