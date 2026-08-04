<?php

namespace App\Listeners;

use App\Enums\CanalEnum;
use App\Enums\NotificationEnum;
use App\Enums\ResultatScanEnum;
use App\Events\BilletScanne;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Support\Facades\Log;

/**
 * Écouteur pour notifier le client lors d'un embarquement validé.
 */
class NotifierEmbarquement
{
    /**
     * Créer une nouvelle instance de l'écouteur.
     */
    public function __construct(protected NotificationDispatchService $notifier) {}

    /**
     * Traiter l'événement.
     */
    public function handle(BilletScanne $event): void
    {
        $scan = $event->scan;
        $billet = $scan->billet;

        if (! $scan || ! $billet) {
            return;
        }

        // Si le scan est valide (embarquement autorisé)
        if ($scan->resultat === ResultatScanEnum::VALIDE) {
            $user = $billet->user;

            if ($user) {
                $message = "Bon voyage ! Votre embarquement pour le voyage ID {$billet->voyage_id} a été validé avec succès.";

                // Envoi d'une notification in-app
                $this->notifier->dispatch(
                    $user,
                    NotificationEnum::ALERTE,
                    CanalEnum::IN_APP,
                    $message,
                    'Embarquement ouvert'
                );

                // Envoi d'un SMS
                $this->notifier->dispatch(
                    $user,
                    NotificationEnum::ALERTE,
                    CanalEnum::SMS,
                    $message,
                    'Embarquement ouvert'
                );

                Log::info("NotifierEmbarquement : Notifications d'embarquement envoyées à l'utilisateur ID {$user->id} pour le billet ID {$billet->id}");
            }
        }
    }
}
