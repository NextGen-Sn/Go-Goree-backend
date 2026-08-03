<?php

use App\Enums\ResultatScanEnum;
use App\Enums\StatutBilletEnum;
use App\Models\Billet;
use App\Models\Embarquement;
use App\Models\Scan;
use App\Models\User;
use App\Models\Voyage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Le service met ses résultats en cache 5 minutes : sans purge, un test
    // lirait les chiffres calculés par le précédent.
    Cache::forget('gg_analytics_dashboard');
});

test('le récapitulatif historique est calculé sur les données réelles', function () {
    $voyage = Voyage::factory()->create([
        'date_voyage' => now()->toDateString(),
        'places' => 100,
        'places_restantes' => 60,
    ]);
    Billet::factory()->paye()->create(['voyage_id' => $voyage->id, 'montant' => 2500]);
    Billet::factory()->statut(StatutBilletEnum::UTILISE)->create(['voyage_id' => $voyage->id, 'montant' => 1500]);
    // Un billet non encaissé ne compte ni en passagers ni en recettes.
    Billet::factory()->enAttente()->create(['voyage_id' => $voyage->id, 'montant' => 5000]);

    Sanctum::actingAs(User::factory()->admin()->create());
    $reponse = $this->getJson('/api/v1/analytics/dashboard')->assertOk();

    expect($reponse->json('historique_voyages.total_voyages'))->toBe(1);
    expect($reponse->json('historique_voyages.passagers_transportes'))->toBe(2);
    expect($reponse->json('historique_voyages.recettes_totales'))->toEqual(4000);
    // 40 places occupées sur 100.
    expect($reponse->json('historique_voyages.taux_occupation_moyen'))->toEqual(40);
});

test('le récapitulatif historique reste à zéro sans données, sans diviser par zéro', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $reponse = $this->getJson('/api/v1/analytics/dashboard')->assertOk();

    expect($reponse->json('historique_voyages.total_voyages'))->toBe(0);
    expect($reponse->json('historique_voyages.taux_occupation_moyen'))->toEqual(0);
});

test('le taux de validation QR est ventilé par contrôleur', function () {
    $agent = User::factory()->agent()->create(['prenom' => 'Aliou', 'nom' => 'Ndong']);
    $embarquement = Embarquement::factory()->create();

    Scan::factory()->count(3)->create([
        'scanne_par' => $agent->id,
        'embarquement_id' => $embarquement->id,
        'resultat' => ResultatScanEnum::VALIDE->value,
    ]);
    Scan::factory()->create([
        'scanne_par' => $agent->id,
        'embarquement_id' => $embarquement->id,
        'resultat' => ResultatScanEnum::DEJA_SCANNE->value,
    ]);

    Sanctum::actingAs(User::factory()->admin()->create());
    $reponse = $this->getJson('/api/v1/analytics/dashboard')->assertOk();

    expect($reponse->json('validation_qr.scannes_mois'))->toBe(4);
    expect($reponse->json('validation_qr.valides'))->toBe(3);
    expect($reponse->json('validation_qr.invalides'))->toBe(1);
    expect($reponse->json('validation_qr.taux_global'))->toEqual(75);
    expect($reponse->json('validation_qr.par_controleur.0.nom'))->toBe('Aliou Ndong');
    expect($reponse->json('validation_qr.par_controleur.0.scannes'))->toBe(4);
});
