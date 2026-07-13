<?php

namespace App\Mail;

use App\Models\AlerteFraude;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Classe de mail envoyée pour notifier les administrateurs d'une alerte de fraude critique détectée.
 */
class AlerteFraudeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 10;

    /**
     * Créer une nouvelle instance de mail d'alerte de fraude.
     */
    public function __construct(public AlerteFraude $alerte)
    {
        $this->onQueue('fraude');
    }

    /**
     * Définir l'enveloppe du mail (expéditeur, sujet, etc.).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerte Fraude Critique détectée - Go Gorée',
        );
    }

    /**
     * Définir le contenu du mail (vue Blade associée).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.alertes.fraude',
        );
    }
}
