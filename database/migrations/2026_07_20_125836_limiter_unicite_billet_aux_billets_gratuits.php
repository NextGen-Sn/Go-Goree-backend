<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * L'unicité « un seul billet actif par (client, voyage) » ne doit plus concerner
 * que les billets GRATUITS (résident abonné, montant = 0) — pour empêcher la
 * génération en masse de billets offerts. Un client payant peut, lui, acheter
 * plusieurs billets pour un même voyage (ex. pour ses enfants), donc l'index ne
 * s'applique plus aux billets payants (montant > 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS billets_user_voyage_actif_unique');

        DB::statement(
            'CREATE UNIQUE INDEX billets_user_voyage_gratuit_unique ON billets (user_id, voyage_id) '.
            "WHERE statut IN ('EN_ATTENTE_PAIEMENT','PAYE','UTILISE') AND montant = 0 AND deleted_at IS NULL"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS billets_user_voyage_gratuit_unique');

        DB::statement(
            'CREATE UNIQUE INDEX billets_user_voyage_actif_unique ON billets (user_id, voyage_id) '.
            "WHERE statut IN ('EN_ATTENTE_PAIEMENT','PAYE','UTILISE') AND deleted_at IS NULL"
        );
    }
};
