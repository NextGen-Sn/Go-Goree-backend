<?php

use App\Enums\DemandeResidenceEnum;
use App\Events\DemandeResidenceSoumise;
use App\Models\Abonnement;
use App\Models\DemandeResidence;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('un client peut soumettre une demande de résidence', function () {
    Event::fake([DemandeResidenceSoumise::class]);
    $client = User::factory()->client()->create();
    Sanctum::actingAs($client);

    $response = $this->postJson('/api/v1/demandes-residence', [
        'carte_identite' => 'CNI123456789',
        'residence' => 'Gorée Centre',
        'photo' => 'photo.png',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('demande_residences', [
        'user_id' => $client->id,
        'statut' => DemandeResidenceEnum::EN_COURS->value,
    ]);
    Event::assertDispatched(DemandeResidenceSoumise::class);
});

test('la soumission valide les champs requis', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/demandes-residence', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['carte_identite', 'residence', 'photo']);
});

test('un client ne voit que ses propres demandes', function () {
    $client = User::factory()->client()->create();
    DemandeResidence::factory()->create(['user_id' => $client->id]);
    DemandeResidence::factory()->create(); // celle d'un autre

    Sanctum::actingAs($client);

    $response = $this->getJson('/api/v1/demandes-residence')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

test('un admin voit toutes les demandes', function () {
    DemandeResidence::factory()->count(3)->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $response = $this->getJson('/api/v1/demandes-residence')->assertOk();
    expect($response->json('meta.total'))->toBe(3);
});

test('un client ne peut pas voir la demande d\'un autre', function () {
    $autre = DemandeResidence::factory()->create();
    Sanctum::actingAs(User::factory()->client()->create());

    $this->getJson("/api/v1/demandes-residence/{$autre->id}")->assertForbidden();
});

test('un admin valide une demande, ce qui active le résident (sans abonnement auto)', function () {
    $demande = DemandeResidence::factory()->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson("/api/v1/demandes-residence/{$demande->id}/valider")
        ->assertOk();

    $this->assertDatabaseHas('demande_residences', [
        'id' => $demande->id,
        'statut' => DemandeResidenceEnum::ACCEPTEE->value,
    ]);

    $resident = Resident::where('user_id', $demande->user_id)->first();
    expect($resident)->not->toBeNull();
    expect((bool) $resident->active)->toBeTrue();
    expect($demande->user->fresh()->est_resident)->toBeTrue();
    // L'abonnement n'est plus créé automatiquement : il se souscrit et se paie.
    expect(Abonnement::where('resident_id', $resident->id)->exists())->toBeFalse();
});

test('un client ne peut pas valider une demande', function () {
    $demande = DemandeResidence::factory()->create();
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson("/api/v1/demandes-residence/{$demande->id}/valider")
        ->assertForbidden();

    $this->assertDatabaseHas('demande_residences', [
        'id' => $demande->id,
        'statut' => DemandeResidenceEnum::EN_COURS->value,
    ]);
});

test('un agent ne peut pas valider une demande (réservé aux admins)', function () {
    $demande = DemandeResidence::factory()->create();
    Sanctum::actingAs(User::factory()->agent()->create());

    $this->postJson("/api/v1/demandes-residence/{$demande->id}/valider")
        ->assertForbidden();
});

test('un admin refuse une demande avec un motif', function () {
    $demande = DemandeResidence::factory()->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson("/api/v1/demandes-residence/{$demande->id}/refuser", [
        'motif_refus' => 'Documents illisibles.',
    ])->assertOk();

    $this->assertDatabaseHas('demande_residences', [
        'id' => $demande->id,
        'statut' => DemandeResidenceEnum::REFUSEE->value,
        'motif_refus' => 'Documents illisibles.',
    ]);
});

test('le refus exige un motif', function () {
    $demande = DemandeResidence::factory()->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson("/api/v1/demandes-residence/{$demande->id}/refuser", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['motif_refus']);
});

test('une deuxième demande est refusée tant que la première est en cours', function () {
    $client = User::factory()->client()->create();
    DemandeResidence::factory()->create([
        'user_id' => $client->id,
        'statut' => DemandeResidenceEnum::EN_COURS->value,
    ]);

    Sanctum::actingAs($client);

    $this->postJson('/api/v1/demandes-residence', [
        'carte_identite' => 'CNI123456789',
        'residence' => 'Gorée Centre',
        'photo' => 'demandes_residence/photo.png',
    ])
        ->assertStatus(409)
        ->assertJsonPath('message', 'Une demande est déjà en cours d\'examen. Vous serez notifié dès qu\'elle aura été traitée.');

    expect(DemandeResidence::where('user_id', $client->id)->count())->toBe(1);
});

test('un résident déjà accepté ne peut pas redéposer de demande', function () {
    $client = User::factory()->client()->resident()->create();
    Sanctum::actingAs($client);

    $this->postJson('/api/v1/demandes-residence', [
        'carte_identite' => 'CNI123456789',
        'residence' => 'Gorée Centre',
        'photo' => 'demandes_residence/photo.png',
    ])
        ->assertStatus(409)
        ->assertJsonPath('message', 'Vous êtes déjà résident. Aucune nouvelle demande n\'est nécessaire.');

    expect(DemandeResidence::where('user_id', $client->id)->count())->toBe(0);
});

test('après un refus, une nouvelle demande est possible', function () {
    $client = User::factory()->client()->create();
    DemandeResidence::factory()->create([
        'user_id' => $client->id,
        'statut' => DemandeResidenceEnum::REFUSEE->value,
        'motif_refus' => 'Pièce illisible',
    ]);

    Sanctum::actingAs($client);

    // C'est précisément le cas où l'utilisateur doit pouvoir recommencer.
    $this->postJson('/api/v1/demandes-residence', [
        'carte_identite' => 'CNI987654321',
        'residence' => 'Gorée Nord',
        'photo' => 'demandes_residence/photo2.png',
    ])->assertCreated();

    expect(DemandeResidence::where('user_id', $client->id)->count())->toBe(2);
});
