<?php

namespace App\Http\Controllers\Api\V1\Billetterie;

use App\Enums\CategorieEnum;
use App\Enums\ModePayementEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Billetterie\StoreBilletRequest;
use App\Http\Resources\Api\V1\BilletResource;
use App\Models\Billet;
use App\Services\Billetterie\BilletPurchaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Contrôleur pour gérer la réservation et l'achat de billets.
 */
class BilletController extends Controller
{
    public function __construct(protected BilletPurchaseService $purchaseService) {}

    /**
     * Liste des billets de l'utilisateur connecté (ou tous les billets si Admin/Agent).
     */
    public function index(Request $request)
    {
        // voyage.trajet : l'app affiche l'heure de départ sur chaque billet.
        // Sans ce chargement, la relation part en lazy loading (N+1) et le
        // client reçoit un `trajet` absent selon le contexte.
        $query = Billet::with(['voyage.trajet', 'tarif', 'user']);

        if ($request->user()->role && $request->user()->role->nom === RoleEnum::CLIENT) {
            $query->where('user_id', $request->user()->id);
        }

        // Du plus récent au plus ancien. Sans tri explicite, la base renvoyait
        // les billets par ordre d'insertion : un billet fraîchement acheté
        // atterrissait en dernière page de pagination, donc invisible dans
        // l'app qui n'affiche que la première.
        $query->latest();

        return BilletResource::collection($query->paginate());
    }

    /**
     * Acheter/réserver un billet pour un trajet.
     */
    public function store(StoreBilletRequest $request)
    {
        try {
            $result = $this->purchaseService->purchase(
                $request->user(),
                $request->voyage_id,
                ModePayementEnum::from($request->payment_mode),
                $request->categorie ? CategorieEnum::from($request->categorie) : null
            );

            return response()->json([
                'message' => 'Billet réservé avec succès.',
                'billet' => new BilletResource($result['billet']->load(['voyage', 'tarif'])),
                'payement' => $result['payement'],
                'redirect_url' => $result['redirect_url'],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Afficher les détails d'un billet spécifique.
     */
    public function show(Request $request, $id)
    {
        $billet = Billet::with(['voyage', 'tarif'])->findOrFail($id);

        if ($request->user()->role && $request->user()->role->nom === RoleEnum::CLIENT && $billet->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], Response::HTTP_FORBIDDEN);
        }

        return new BilletResource($billet);
    }
}
