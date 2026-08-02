<?php

use App\Models\Billet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('la liste des billets présente les plus récents en premier', function () {
    $client = User::factory()->client()->create();

    // Plus de billets qu'une page : sans tri, l'achat du jour se retrouve en
    // dernière page et disparaît de l'écran « Mes billets », qui n'affiche que
    // la première.
    Billet::factory()->count(16)->create([
        'user_id' => $client->id,
        'created_at' => now()->subDays(3),
    ]);
    $dernier = Billet::factory()->create([
        'user_id' => $client->id,
        'created_at' => now(),
    ]);

    Sanctum::actingAs($client);

    $this->getJson('/api/v1/billets')
        ->assertOk()
        ->assertJsonPath('data.0.id', $dernier->id);
});

test('un client ne voit que ses propres billets, un agent les voit tous', function () {
    $client = User::factory()->client()->create();
    Billet::factory()->count(2)->create(['user_id' => $client->id]);
    Billet::factory()->create(); // billet d'un autre passager

    Sanctum::actingAs($client);
    expect($this->getJson('/api/v1/billets')->assertOk()->json('meta.total'))->toBe(2);

    Sanctum::actingAs(User::factory()->agent()->create());
    expect($this->getJson('/api/v1/billets')->assertOk()->json('meta.total'))->toBe(3);
});
