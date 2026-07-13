<?php

use App\Models\Billet;
use App\Models\Tarif;
use App\Models\Trajet;
use App\Models\User;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('un client ne peut pas gérer les utilisateurs', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->getJson('/api/v1/users')->assertForbidden();
    $this->postJson('/api/v1/users', [])->assertForbidden();
    $this->getJson('/api/v1/controleurs')->assertForbidden();
});

test('un client ne peut pas créer/modifier les données de référence (voyages, tarifs, chaloupes)', function () {
    $trajet = Trajet::factory()->create();
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/voyages', [
        'date_voyage' => now()->addDay()->toDateString(),
        'places' => 100,
        'trajet_id' => $trajet->id,
        'chaloupe_id' => $trajet->id,
    ])->assertForbidden();

    $this->postJson('/api/v1/tarifs', ['categorie' => 'RESIDENT', 'prix' => 500, 'trajet_id' => $trajet->id])
        ->assertForbidden();

    $this->postJson('/api/v1/chaloupes', ['imatriculation' => 'IM-X', 'nom' => 'X', 'capacite' => 10])
        ->assertForbidden();
});

test('un agent (contrôleur) ne peut pas créer de voyage', function () {
    $trajet = Trajet::factory()->create();
    Sanctum::actingAs(User::factory()->agent()->create());

    $this->postJson('/api/v1/voyages', [
        'date_voyage' => now()->addDay()->toDateString(),
        'places' => 100,
        'trajet_id' => $trajet->id,
        'chaloupe_id' => $trajet->id,
    ])->assertForbidden();
});

test('la lecture des données de référence reste ouverte à tout utilisateur authentifié', function () {
    Voyage::factory()->create();
    Tarif::factory()->adulte()->create();
    Sanctum::actingAs(User::factory()->client()->create());

    $this->getJson('/api/v1/voyages')->assertOk();
    $this->getJson('/api/v1/tarifs')->assertOk();
});

test('un client ne peut pas consulter les alertes de fraude', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->getJson('/api/v1/alertes-fraude')->assertForbidden();
});

test('un client ne peut pas gérer résidents et abonnements', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->getJson('/api/v1/residents')->assertForbidden();
    $this->getJson('/api/v1/abonnements')->assertForbidden();
});

test('un client ne peut pas scanner un billet', function () {
    $billet = Billet::factory()->paye()->create();
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/scans', ['qr_token' => $billet->qr_token])->assertForbidden();
});

test('un utilisateur sans rôle est traité comme non autorisé sur les routes protégées par rôle', function () {
    Sanctum::actingAs(User::factory()->create()); // aucun rôle

    $this->getJson('/api/v1/users')->assertForbidden();
});
