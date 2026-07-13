<?php

use App\Models\User;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::factory()->client()->create());
});

test('le filtre periode=today ne renvoie que les voyages du jour', function () {
    Voyage::factory()->create(['date_voyage' => now()->toDateString()]);
    Voyage::factory()->create(['date_voyage' => now()->addDays(2)->toDateString()]);

    $response = $this->getJson('/api/v1/voyages?periode=today')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

test('le filtre periode=semaine renvoie les 7 prochains jours', function () {
    Voyage::factory()->create(['date_voyage' => now()->toDateString()]);
    Voyage::factory()->create(['date_voyage' => now()->addDays(5)->toDateString()]);
    Voyage::factory()->create(['date_voyage' => now()->addDays(10)->toDateString()]); // hors semaine

    $response = $this->getJson('/api/v1/voyages?periode=semaine')->assertOk();
    expect($response->json('meta.total'))->toBe(2);
});

test('par défaut, les voyages passés sont exclus', function () {
    Voyage::factory()->create(['date_voyage' => now()->subDays(2)->toDateString()]);
    Voyage::factory()->create(['date_voyage' => now()->toDateString()]);

    $response = $this->getJson('/api/v1/voyages')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

test('le filtre disponibles=true exclut les voyages complets', function () {
    Voyage::factory()->placesRestantes(5)->create(['date_voyage' => now()->toDateString()]);
    Voyage::factory()->complet()->create(['date_voyage' => now()->toDateString()]);

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
