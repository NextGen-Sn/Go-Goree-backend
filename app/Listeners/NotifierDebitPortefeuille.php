<?php

namespace App\Listeners;

use App\Enums\CanalEnum;
use App\Enums\NotificationEnum;
use App\Events\PortefeuilleDebite;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Support\Facades\Log;

/**
 * Écouteur pour notifier l'utilisateur lors du débit de son portefeuille.
 */
class NotifierDebitPortefeuille
{
    /**
     * Créer une nouvelle instance de l'écouteur.
     */
    public function __construct(protected NotificationDispatchService $notifier) {}

    /**
     * Traiter l'événement.
     */
    public function handle(PortefeuilleDebite $event): void
    {
        $portefeuille = $event->portefeuille;
        $user = $portefeuille->user;

        if ($user) {
            $message = "Votre portefeuille a été débité de {$event->montant} FCFA. Nouveau solde : {$portefeuille->solde} FCFA.";

            // Notification in-app
            $this->notifier->dispatch(
                $user,
                NotificationEnum::PAYEMENT,
                CanalEnum::IN_APP,
                $message,
                'Portefeuille débité'
            );

            Log::info("NotifierDebitPortefeuille : Notification in-app envoyée à l'utilisateur ID {$user->id} pour le débit de {$event->montant} FCFA.");
        }
    }
}
