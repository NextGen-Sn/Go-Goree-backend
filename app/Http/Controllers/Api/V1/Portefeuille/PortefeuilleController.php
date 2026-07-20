<?php

namespace App\Http\Controllers\Api\V1\Portefeuille;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MouvementPortefeuilleResource;
use App\Http\Resources\Api\V1\PortefeuilleResource;
use App\Models\MouvementPortefeuille;
use App\Models\Portefeuille;
use Illuminate\Http\Request;

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
        $portefeuille = Portefeuille::where('user_id', $request->user()->id)->firstOrFail();

        return new PortefeuilleResource($portefeuille);
    }

    /**
     * Historique des mouvements du portefeuille de l'utilisateur connecté.
     */
    public function mouvements(Request $request)
    {
        $portefeuille = Portefeuille::where('user_id', $request->user()->id)->firstOrFail();

        $mouvements = MouvementPortefeuille::where('portefeuille_id', $portefeuille->id)
            ->with('payement')
            ->orderByDesc('created_at')
            ->paginate();

        return MouvementPortefeuilleResource::collection($mouvements);
    }
}
