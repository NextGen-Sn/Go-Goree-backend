<?php

use App\Enums\JourEnum;
use App\Jobs\GenererVoyagesSemaineJob;
use App\Models\Chaloupe;
use App\Models\Trajet;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('le job génère un voyage pour chaque jour de la semaine ayant un trajet', function () {
    Chaloupe::factory()->create(); // active par défaut

    // Un trajet par jour de la semaine → un voyage par jour sur 7 jours.
    foreach (JourEnum::cases() as $jour) {
        Trajet::factory()->create(['jour' => $jour->value]);
    }

    (new GenererVoyagesSemaineJob(7))->handle();

    expect(Voyage::count())->toBe(7);
});

test('le job est idempotent : relancer ne duplique pas les voyages', function () {
    Chaloupe::factory()->create();
    foreach (JourEnum::cases() as $jour) {
        Trajet::factory()->create(['jour' => $jour->value]);
    }

    (new GenererVoyagesSemaineJob(7))->handle();
    (new GenererVoyagesSemaineJob(7))->handle();

    expect(Voyage::count())->toBe(7);
});

test('le job assigne des places égales à la capacité de la chaloupe', function () {
    Chaloupe::factory()->create(['capacite' => 120]);

    // Un trajet correspondant au jour d'aujourd'hui → un voyage généré aujourd'hui.
    $jourAujourdhui = JourEnum::cases()[now()->dayOfWeekIso - 1];
    Trajet::factory()->create(['jour' => $jourAujourdhui->value]);

    (new GenererVoyagesSemaineJob(1))->handle();

    $voyage = Voyage::firstOrFail();
    expect($voyage->places)->toBe(120);
    expect($voyage->places_restantes)->toBe(120);
});

test('sans chaloupe active, aucun voyage n\'est généré', function () {
    Chaloupe::factory()->enMaintenance()->create();
    Trajet::factory()->create();

    (new GenererVoyagesSemaineJob(7))->handle();

    expect(Voyage::count())->toBe(0);
});
