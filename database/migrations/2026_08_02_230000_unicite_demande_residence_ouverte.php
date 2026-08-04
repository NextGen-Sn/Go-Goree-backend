<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Un utilisateur ne peut avoir qu'une seule demande de résidence « ouverte »
 * — en cours d'examen ou déjà acceptée.
 *
 * Le contrôleur refuse déjà ce cas, mais entre sa vérification et l'insertion
 * il reste une fenêtre : deux requêtes simultanées (double tap, réémission
 * réseau) passent toutes les deux. Cet index partiel ferme la fenêtre en base,
 * comme le fait déjà `billets_user_voyage_gratuit_unique` pour les billets
 * gratuits.
 *
 * Les demandes REFUSEE et ANNULEE sont hors index : redéposer après un refus
 * doit rester possible, autant de fois que nécessaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'CREATE UNIQUE INDEX demandes_residence_user_ouverte_unique ON demande_residences (user_id) '.
            "WHERE statut IN ('EN_COURS','ACCEPTEE') AND deleted_at IS NULL"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS demandes_residence_user_ouverte_unique');
    }
};
