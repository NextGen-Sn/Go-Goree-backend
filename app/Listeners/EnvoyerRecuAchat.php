<?php

namespace App\Listeners;

use App\Enums\CanalEnum;
use App\Enums\NotificationEnum;
use App\Events\BilletAchete;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Support\Facades\Log;

/**
 * Écouteur pour envoyer un reçu d'achat au client ayant acheté un billet.
 */
class EnvoyerRecuAchat
{
    /**
     * Créer une nouvelle instance de l'écouteur.
     */
    public function __construct(protected NotificationDispatchService $notifier) {}

    /**
     * Traiter l'événement.
     */
    public function handle(BilletAchete $event): void
    {
        $billet = $event->billet;
        $user = $billet->user;

        if ($user) {
            $message = "Reçu d'achat : Votre billet pour le voyage ID {$billet->voyage_id} a été acheté avec succès. Montant : {$billet->montant} FCFA. ID Billet : {$billet->id}";

            // Envoi de la notification par E-mail
            $this->notifier->dispatch(
                $user,
                NotificationEnum::PAYEMENT,
                CanalEnum::MAIL,
                $message,
                'Billet confirmé'
            );

            // Envoi de la notification in-app
            $this->notifier->dispatch(
                $user,
                NotificationEnum::PAYEMENT,
                CanalEnum::IN_APP,
                $message,
                'Billet confirmé'
            );

            Log::info("EnvoyerRecuAchat : Reçu d'achat envoyé à l'utilisateur ID {$user->id} pour le billet ID {$billet->id}");
        }
    }
}
