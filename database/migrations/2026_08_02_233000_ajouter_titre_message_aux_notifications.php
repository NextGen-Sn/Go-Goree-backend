<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persiste le contenu des notifications.
 *
 * La table ne gardait que le type, le canal et la date de lecture. Le titre et
 * le message n'existaient qu'en variable locale : diffusés en temps réel via
 * Reverb, envoyés par email, puis perdus. Un passager hors ligne au moment de
 * l'envoi ouvrait donc l'app et trouvait une notification sans contenu.
 *
 * Nullables : les 144 lignes déjà en base n'ont aucun contenu à récupérer,
 * il n'existe nulle part. L'app affiche un libellé dérivé du type pour
 * celles-là.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('titre')->nullable()->after('type');
            $table->text('message')->nullable()->after('titre');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['titre', 'message']);
        });
    }
};
