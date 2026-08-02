<?php

use App\Enums\StatutBilletEnum;
use App\Jobs\ExpireTicketsJob;
use App\Models\Billet;
use App\Models\Trajet;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('un billet payé d\'un voyage passé (>1h) est expiré', function () {
    $voyage = Voyage::factory()->create(['date_voyage' => now()->subDay()->toDateString()]);
    $billet = Billet::factory()->paye()->create(['voyage_id' => $voyage->id]);

    (new ExpireTicketsJob)->handle();

    expect($billet->fresh()->statut)->toBe(StatutBilletEnum::EXPIRE);
});

test('un billet d\'un voyage futur n\'est pas expiré', function () {
    $voyage = Voyage::factory()->create(['date_voyage' => now()->addDay()->toDateString()]);
    $billet = Billet::factory()->paye()->create(['voyage_id' => $voyage->id]);

    (new ExpireTicketsJob)->handle();

    expect($billet->fresh()->statut)->toBe(StatutBilletEnum::PAYE);
});

test('un billet déjà utilisé n\'est pas ré-expiré', function () {
    $voyage = Voyage::factory()->create(['date_voyage' => now()->subDay()->toDateString()]);
    $billet = Billet::factory()->utilise()->create(['voyage_id' => $voyage->id]);

    (new ExpireTicketsJob)->handle();

    expect($billet->fresh()->statut)->toBe(StatutBilletEnum::UTILISE);
});

test('un billet de la veille est expiré même juste après minuit', function () {
    // 00h30 : l'heure pivot (−1h) retombe la veille. Les deux branches de la
    // condition portaient alors sur la même date, et une comparaison de
    // chaînes les faisait échouer toutes les deux — les billets de la veille
    // survivaient jusqu'à 1h du matin.
    Carbon::setTestNow(Carbon::today()->addDay()->setTime(0, 30));

    $voyage = Voyage::factory()->create([
        'date_voyage' => now()->subDay()->toDateString(),
        'trajet_id' => Trajet::factory()->create(['heure_depart' => '18:30'])->id,
    ]);
    $billet = Billet::factory()->paye()->create(['voyage_id' => $voyage->id]);

    (new ExpireTicketsJob)->handle();

    expect($billet->fresh()->statut)->toBe(StatutBilletEnum::EXPIRE);

    Carbon::setTestNow();
});
