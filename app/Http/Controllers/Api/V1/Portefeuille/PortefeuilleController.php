<?php

namespace App\Http\Controllers\Api\V1\Portefeuille;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MouvementPortefeuilleResource;
use App\Http\Resources\Api\V1\PortefeuilleResource;
use App\Models\MouvementPortefeuille;
use App\Models\Portefeuille;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Contrôleur pour visualiser les informations du portefeuille.
 */
class PortefeuilleController extends Controller
{
    /**
     * Afficher le portefeuille de l'utilisateur connecté.
     */
    public function show(Request $request)
    {
        // Statut forcé à 200 : Laravel répond 201 dès que la ressource sérialisée
        // vient d'être créée (`wasRecentlyCreated`). Ici la création est un détail
        // d'implémentation, l'appelant fait une simple lecture.
        return (new PortefeuilleResource($this->portefeuilleDe($request)))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Portefeuille de l'utilisateur connecté, créé à vide s'il n'existe pas.
     *
     * Un compte sans portefeuille est un état légitime (comptes antérieurs à la
     * création automatique à l'inscription, comptes créés par seeder ou par
     * invitation). Renvoyer 404 casse l'écran « Portefeuille » de l'app alors
     * que la réponse juste est « solde 0 ».
     */
    private function portefeuilleDe(Request $request): Portefeuille
    {
        return Portefeuille::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['solde' => 0]
        );
    }

    /**
     * Historique des mouvements du portefeuille de l'utilisateur connecté.
     */
    public function mouvements(Request $request)
    {
        $portefeuille = $this->portefeuilleDe($request);

        $mouvements = MouvementPortefeuille::where('portefeuille_id', $portefeuille->id)
            ->with('payement')
            ->orderByDesc('created_at')
            ->paginate();

        return MouvementPortefeuilleResource::collection($mouvements);
    }
}
