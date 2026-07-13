<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Classe de mail envoyée pour transmettre le rapport d'activité quotidien aux administrateurs.
 */
class RapportJournalierMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    /**
     * Créer une nouvelle instance de mail de rapport journalier.
     */
    public function __construct(public array $donnees)
    {
        $this->onQueue('rapports');
    }

    /**
     * Définir l'enveloppe du mail (expéditeur, sujet, etc.).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rapport Journalier d\'activité - Go Gorée',
        );
    }

    /**
     * Définir le contenu du mail (vue Blade associée).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.rapports.journalier',
        );
    }
}
