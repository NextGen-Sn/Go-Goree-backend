<?php

namespace App\Http\Controllers\Api\V1\Voyages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Voyages\StoreVoyageRequest;
use App\Http\Requests\Api\V1\Voyages\UpdateVoyageRequest;
use App\Http\Resources\Api\V1\VoyageResource;
use App\Models\Voyage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Contrôleur pour gérer les instances de voyage d'une chaloupe.
 */
class VoyageController extends Controller
{
    /**
     * Liste des voyages, avec filtres :
     *   ?periode=today      → uniquement aujourd'hui
     *   ?periode=semaine    → 7 prochains jours (défaut : voyages à venir)
     *   ?date=YYYY-MM-DD    → une date précise
     *   ?disponibles=true   → uniquement ceux avec des places restantes
     */
    public function index(Request $request)
    {
        $query = Voyage::with(['trajet', 'chaloupe'])
            ->withVentes()
            ->orderBy('date_voyage');

        $today = now()->toDateString();

        if ($request->filled('date')) {
            $query->whereDate('date_voyage', $request->date);
        } elseif ($request->periode === 'today') {
            $query->whereDate('date_voyage', $today);
        } elseif ($request->periode === 'semaine') {
            $query->whereBetween('date_voyage', [$today, now()->addDays(6)->toDateString()]);
            $this->exclureDepartsPasses($query, $today);
        } else {
            // Par défaut : les voyages à venir (aujourd'hui et après).
            $query->whereDate('date_voyage', '>=', $today);
            $this->exclureDepartsPasses($query, $today);
        }

        if ($request->boolean('disponibles')) {
            $query->where('places_restantes', '>', 0);
        }

        return VoyageResource::collection($query->paginate());
    }

    /**
     * Retire du listing les départs du jour dont l'heure est passée.
     *
     * Ces listings alimentent l'achat côté client : proposer la chaloupe de
     * 07h30 à 21h revient à vendre une place sur un bateau déjà parti. Le
     * filtre ne s'applique pas à `?periode=today` ni à `?date=`, qui servent
     * aux contrôleurs et à l'admin — eux ont besoin de la journée entière.
     */
    private function exclureDepartsPasses($query, string $today): void
    {
        $maintenant = now()->toTimeString();

        $query->where(function ($q) use ($today, $maintenant) {
            $q->whereDate('date_voyage', '>', $today)
                ->orWhere(function ($jour) use ($today, $maintenant) {
                    $jour->whereDate('date_voyage', $today)
                        ->whereHas('trajet', fn ($t) => $t->where('heure_depart', '>', $maintenant));
                });
        });
    }

    /**
     * Enregistrer un nouveau voyage.
     */
    public function store(StoreVoyageRequest $request)
    {
        $record = Voyage::create($request->validated());

        app(\App\Services\Logs\ActivityLogService::class)->log(
            "Création voyage",
            "Voyage planifié le : {$record->date_voyage} avec la chaloupe ID : {$record->chaloupe_id}"
        );

        return (new VoyageResource($record->load(['trajet', 'chaloupe'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Afficher les détails d'un voyage spécifique.
     */
    public function show($id)
    {
        $record = Voyage::with(['trajet', 'chaloupe'])->withVentes()->findOrFail($id);

        return new VoyageResource($record);
    }

    /**
     * Mettre à jour un voyage.
     *
     * Si la capacité (`places`) change, on recalcule `places_restantes` =
     * nouvelles places − nombre de billets déjà vendus pour ce voyage, afin
     * de ne jamais désynchroniser le stock réel via le frontend.
     */
    public function update(UpdateVoyageRequest $request, $id)
    {
        $record = Voyage::withCount([
            'billets as billets_vendus' => fn ($q) => $q->whereIn('statut', ['PAYE', 'UTILISE']),
        ])->findOrFail($id);

        $data = $request->validated();

        // Recalcul automatique des places restantes si la capacité est modifiée.
        if (isset($data['places'])) {
            $vendus = $record->billets_vendus ?? 0;
            $data['places_restantes'] = max(0, $data['places'] - $vendus);
        }

        // Ne jamais accepter places_restantes brut envoyé par le client.
        unset($data['places_restantes_client']);

        $record->update($data);

        app(\App\Services\Logs\ActivityLogService::class)->log(
            "Modification voyage",
            "Voyage ID : {$record->id} (Date : {$record->date_voyage}, Chaloupe ID : {$record->chaloupe_id})"
        );

        return new VoyageResource($record->load(['trajet', 'chaloupe']));
    }

    /**
     * Supprimer un voyage.
     */
    public function destroy($id)
    {
        $record = Voyage::findOrFail($id);
        
        app(\App\Services\Logs\ActivityLogService::class)->log(
            "Suppression voyage",
            "Voyage du : {$record->date_voyage} (ID : {$record->id})"
        );

        $record->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Déclencher manuellement la génération des voyages des 7 prochains jours.
     *
     * Le job est envoyé sur la queue Redis (QUEUE_CONNECTION=redis) afin de ne
     * pas bloquer la réponse HTTP pendant la génération.
     */
    public function generer()
    {
        \App\Jobs\GenererVoyagesSemaineJob::dispatch();

        app(\App\Services\Logs\ActivityLogService::class)->log(
            "Génération voyages",
            "Job de génération automatique mis en file d'attente (7 prochains jours)"
        );

        return response()->json([
            'message' => 'Génération des voyages mise en file d\'attente. Les voyages seront disponibles dans quelques instants.'
        ]);
    }
}
