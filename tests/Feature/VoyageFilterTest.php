<?php

use App\Models\Trajet;
use App\Models\User;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * L'horloge est figée à 06h00 : depuis que les listings excluent les départs
 * déjà passés, un test créant un voyage « aujourd'hui » dépendrait sinon de
 * l'heure à laquelle la suite tourne.
 */
beforeEach(function () {
    Carbon::setTestNow(Carbon::today()->setTime(6, 0));
    Sanctum::actingAs(User::factory()->client()->create());
});

afterEach(function () {
    Carbon::setTestNow();
});

/** Voyage du jour dont le départ est encore à venir (07h30 alors qu'il est 06h00). */
function voyageDuJourAVenir(array $attributs = []): Voyage
{
    return Voyage::factory()->create(array_merge([
        'date_voyage' => now()->toDateString(),
        'trajet_id' => Trajet::factory()->create(['heure_depart' => '07:30'])->id,
    ], $attributs));
}

test('le filtre periode=today ne renvoie que les voyages du jour', function () {
    voyageDuJourAVenir();
    Voyage::factory()->create(['date_voyage' => now()->addDays(2)->toDateString()]);

    $response = $this->getJson('/api/v1/voyages?periode=today')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

test('le filtre periode=semaine renvoie les 7 prochains jours', function () {
    voyageDuJourAVenir();
    Voyage::factory()->create(['date_voyage' => now()->addDays(5)->toDateString()]);
    Voyage::factory()->create(['date_voyage' => now()->addDays(10)->toDateString()]); // hors semaine

    $response = $this->getJson('/api/v1/voyages?periode=semaine')->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

test('par défaut, les voyages passés sont exclus', function () {
    Voyage::factory()->create(['date_voyage' => now()->subDays(2)->toDateString()]);
    voyageDuJourAVenir();

    $response = $this->getJson('/api/v1/voyages')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

test('un départ du jour déjà passé n\'est plus proposé à l\'achat', function () {
    // 05h00 alors qu'il est 06h00 : la chaloupe est partie.
    $parti = Voyage::factory()->create([
        'date_voyage' => now()->toDateString(),
        'trajet_id' => Trajet::factory()->create(['heure_depart' => '05:00'])->id,
    ]);
    $aVenir = voyageDuJourAVenir();

    // Listings destinés à l'achat : seul le départ à venir remonte.
    foreach (['/api/v1/voyages', '/api/v1/voyages?periode=semaine'] as $url) {
        $response = $this->getJson($url)->assertOk();
        expect($response->json('meta.total'))->toBe(1);
        expect($response->json('data.0.id'))->toBe($aVenir->id);
    }

    // periode=today reste exhaustif : les contrôleurs et l'admin ont besoin de
    // voir la journée entière, départs effectués compris.
    $response = $this->getJson('/api/v1/voyages?periode=today')->assertOk();
    expect($response->json('meta.total'))->toBe(2);
    expect(collect($response->json('data'))->pluck('id'))->toContain($parti->id);
});

test('le filtre disponibles=true exclut les voyages complets', function () {
    voyageDuJourAVenir(['places_restantes' => 5]);
    voyageDuJourAVenir(['places_restantes' => 0]);

    $response = $this->getJson('/api/v1/voyages?disponibles=true')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

test('le filtre date cible une date précise', function () {
    $date = now()->addDays(3)->toDateString();
    Voyage::factory()->create(['date_voyage' => $date]);
    Voyage::factory()->create(['date_voyage' => now()->addDays(4)->toDateString()]);

    $response = $this->getJson('/api/v1/voyages?date='.$date)->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});
