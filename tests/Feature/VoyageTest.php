<?php

use App\Enums\CategorieEnum;
use App\Enums\StatutBilletEnum;
use App\Models\Billet;
use App\Models\Chaloupe;
use App\Models\Trajet;
use App\Models\User;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('la liste des voyages est paginée avec trajet et chaloupe', function () {
    Voyage::factory()->count(2)->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/v1/voyages')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'date_voyage', 'places_restantes']]]);
});

test('un voyage peut être créé avec des données valides', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $trajet = Trajet::factory()->create();
    $chaloupe = Chaloupe::factory()->create();

    $this->postJson('/api/v1/voyages', [
        'date_voyage' => now()->addDay()->toDateString(),
        'places' => 120,
        'places_restantes' => 120,
        'trajet_id' => $trajet->id,
        'chaloupe_id' => $chaloupe->id,
    ])->assertCreated();

    $this->assertDatabaseHas('voyages', ['trajet_id' => $trajet->id, 'places' => 120]);
});

test('la création de voyage refuse une date passée', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $trajet = Trajet::factory()->create();
    $chaloupe = Chaloupe::factory()->create();

    $this->postJson('/api/v1/voyages', [
        'date_voyage' => now()->subDay()->toDateString(),
        'places' => 120,
        'trajet_id' => $trajet->id,
        'chaloupe_id' => $chaloupe->id,
    ])->assertStatus(422)->assertJsonValidationErrors(['date_voyage']);
});

test('la création de voyage valide les champs requis', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/v1/voyages', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['date_voyage', 'places', 'trajet_id', 'chaloupe_id']);
});

test('un voyage peut être mis à jour', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $voyage = Voyage::factory()->placesRestantes(50)->create();

    $this->putJson("/api/v1/voyages/{$voyage->id}", [
        'date_voyage' => now()->addDays(2)->toDateString(),
        'places' => 80,
        'places_restantes' => 80,
        'trajet_id' => $voyage->trajet_id,
        'chaloupe_id' => $voyage->chaloupe_id,
    ])->assertOk();

    expect($voyage->fresh()->places)->toBe(80);
});

test('un voyage peut être supprimé (soft delete)', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $voyage = Voyage::factory()->create();

    $this->deleteJson("/api/v1/voyages/{$voyage->id}")->assertNoContent();
    $this->assertSoftDeleted('voyages', ['id' => $voyage->id]);
});

test('un trajet peut être créé', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/v1/trajets', [
        'jour' => 'LUNDI',
        'heure_depart' => '07:30',
        'duree' => 20,
    ])->assertCreated();
});

test('une chaloupe peut être créée avec son immatriculation', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/v1/chaloupes', [
        'imatriculation' => 'IM-TEST-01',
        'nom' => 'Beer',
        'capacite' => 150,
    ])->assertCreated();

    $this->assertDatabaseHas('chaloupes', ['imatriculation' => 'IM-TEST-01', 'nom' => 'Beer']);
});

test('la création de chaloupe exige une immatriculation', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson('/api/v1/chaloupes', [
        'nom' => 'Beer',
        'capacite' => 150,
    ])->assertStatus(422)->assertJsonValidationErrors(['imatriculation']);
});

test('un tarif peut être créé', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $trajet = Trajet::factory()->create();

    $this->postJson('/api/v1/tarifs', [
        'categorie' => CategorieEnum::RESIDENT->value,
        'prix' => 500,
        'trajet_id' => $trajet->id,
    ])->assertCreated();

    $this->assertDatabaseHas('tarifs', ['categorie' => CategorieEnum::RESIDENT->value]);
});

test('les routes voyages sont protégées par authentification', function () {
    $this->getJson('/api/v1/voyages')->assertUnauthorized();
    $this->getJson('/api/v1/tarifs')->assertUnauthorized();
});

test('la liste des voyages expose la recette réelle et le nombre de billets vendus', function () {
    $voyage = Voyage::factory()->create();

    // Deux billets encaissés, un billet gratuit d'abonné, et trois billets qui
    // ne comptent pas : en attente de paiement, expiré, annulé.
    Billet::factory()->paye()->create(['voyage_id' => $voyage->id, 'montant' => 2500]);
    Billet::factory()->statut(StatutBilletEnum::UTILISE)->create(['voyage_id' => $voyage->id, 'montant' => 1500]);
    Billet::factory()->paye()->create(['voyage_id' => $voyage->id, 'montant' => 0]);
    Billet::factory()->enAttente()->create(['voyage_id' => $voyage->id, 'montant' => 2500]);
    Billet::factory()->expire()->create(['voyage_id' => $voyage->id, 'montant' => 2500]);
    Billet::factory()->statut(StatutBilletEnum::ANNULE)->create(['voyage_id' => $voyage->id, 'montant' => 2500]);

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/v1/voyages')
        ->assertOk()
        ->assertJsonPath('data.0.billets_vendus', 3)   // dont le billet gratuit
        ->assertJsonPath('data.0.recette', 4000);      // 2500 + 1500, le gratuit n'ajoute rien
});

test('un voyage sans billet vendu affiche une recette nulle', function () {
    Voyage::factory()->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/v1/voyages')
        ->assertOk()
        ->assertJsonPath('data.0.billets_vendus', 0)
        ->assertJsonPath('data.0.recette', 0);
});

test('le détail d\'un voyage expose aussi sa recette', function () {
    $voyage = Voyage::factory()->create();
    Billet::factory()->paye()->create(['voyage_id' => $voyage->id, 'montant' => 2500]);

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson("/api/v1/voyages/{$voyage->id}")
        ->assertOk()
        ->assertJsonPath('data.recette', 2500)
        ->assertJsonPath('data.billets_vendus', 1);
});
