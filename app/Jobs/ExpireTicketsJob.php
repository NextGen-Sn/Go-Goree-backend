<?php

namespace App\Jobs;

use App\Enums\StatutBilletEnum;
use App\Models\Billet;
use App\Models\Voyage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job pour expirer automatiquement les billets 1 heure après l'heure de départ du voyage.
 */
class ExpireTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Exécuter la tâche.
     */
    public function handle(): void
    {
        // Heure pivot : il y a 1 heure.
        $pivot = now()->subHour();

        // Voyages dont le départ remonte à plus d'une heure.
        //
        // `whereDate` et non une comparaison de chaînes : `date_voyage` est une
        // colonne DATE mais transite en « 2026-08-01 00:00:00 ». Comparée telle
        // quelle à « 2026-08-01 », elle n'est ni égale ni inférieure — le
        // préfixe est identique mais la chaîne est plus longue. Entre minuit et
        // 1h du matin, l'heure pivot tombe la veille : les deux branches de la
        // condition échouaient alors ensemble, et les billets de la veille
        // restaient PAYE pendant toute cette heure-là.
        $expiredVoyageIds = Voyage::join('trajets', 'voyages.trajet_id', '=', 'trajets.id')
            ->where(function ($query) use ($pivot) {
                // Voyage d'un jour précédent
                $query->whereDate('voyages.date_voyage', '<', $pivot->toDateString())
                    // Voyage du jour pivot dont l'heure de départ est passée depuis > 1h
                    ->orWhere(function ($q) use ($pivot) {
                        $q->whereDate('voyages.date_voyage', '=', $pivot->toDateString())
                            ->where('trajets.heure_depart', '<=', $pivot->toTimeString());
                    });
            })
            ->pluck('voyages.id');

        // On n'expire que les billets NON utilisés (un billet déjà scanné reste UTILISE).
        if ($expiredVoyageIds->isNotEmpty()) {
            Billet::whereIn('voyage_id', $expiredVoyageIds)
                ->where('statut', StatutBilletEnum::PAYE)
                ->update(['statut' => StatutBilletEnum::EXPIRE]);
        }
    }
}
