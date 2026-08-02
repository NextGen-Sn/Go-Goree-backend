<?php

use App\Enums\ModePayementEnum;
use App\Models\Portefeuille;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('un utilisateur consulte le solde de son portefeuille', function () {
    $user = User::factory()->client()->create();
    Portefeuille::factory()->solde(7500)->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/portefeuille')
        ->assertOk()
        ->assertJsonPath('data.solde', '7500.00');
});

// Ce endpoint renvoyait 404 quand aucun portefeuille n'existait. C'était
// intenable côté app : l'inscription ne créait pas de portefeuille, donc
// l'écran « Portefeuille » de tout nouveau client échouait jusqu'à sa première
// recharge. Un compte sans portefeuille a un solde de 0, ce n'est pas une
// ressource absente — voir le test ci-dessous.

test('la recharge refuse un montant trop faible', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/portefeuille/recharge', ['montant' => 50])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['montant']);
});

test('la recharge refuse un montant non numérique', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/portefeuille/recharge', ['montant' => 'beaucoup'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['montant']);
});

test('la recharge refuse le mode PORTEFEUILLE (on ne recharge pas depuis soi-même)', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/portefeuille/recharge', [
        'montant' => 5000,
        'payment_mode' => ModePayementEnum::PORTEFEUILLE->value,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['payment_mode']);
});

test('la recharge refuse un mode de paiement inconnu', function () {
    Sanctum::actingAs(User::factory()->client()->create());

    $this->postJson('/api/v1/portefeuille/recharge', [
        'montant' => 5000,
        'payment_mode' => 'BITCOIN',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['payment_mode']);
});

test('le portefeuille est protégé par authentification', function () {
    $this->getJson('/api/v1/portefeuille')->assertUnauthorized();
    $this->postJson('/api/v1/portefeuille/recharge', ['montant' => 5000])->assertUnauthorized();
});

test('un compte sans portefeuille en obtient un vide au lieu d\'une 404', function () {
    // Cas réel : comptes créés avant la création automatique à l'inscription,
    // comptes de seeder, ou contrôleurs invités par un admin.
    $user = User::factory()->client()->create();
    expect(Portefeuille::where('user_id', $user->id)->exists())->toBeFalse();

    Sanctum::actingAs($user);

    // `solde` est un decimal : sérialisé en chaîne, comme partout ailleurs.
    $this->getJson('/api/v1/portefeuille')
        ->assertOk()
        ->assertJsonPath('data.solde', '0.00');

    $this->getJson('/api/v1/portefeuille/mouvements')->assertOk();

    expect(Portefeuille::where('user_id', $user->id)->exists())->toBeTrue();
});

test('l\'inscription crée le portefeuille du nouveau client', function () {
    $this->postJson('/api/v1/register', [
        'prenom' => 'Awa',
        'nom' => 'Ndiaye',
        'email' => 'awa.ndiaye@goree.sn',
        'telephone' => '770000001',
        'mot_de_passe' => 'motdepasse123',
        'mot_de_passe_confirmation' => 'motdepasse123',
    ])->assertCreated();

    $user = User::where('email', 'awa.ndiaye@goree.sn')->firstOrFail();
    expect(Portefeuille::where('user_id', $user->id)->value('solde'))->toEqual(0);
});
