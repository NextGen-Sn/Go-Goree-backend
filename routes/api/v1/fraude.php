<?php

use App\Http\Controllers\Api\V1\Fraude\AlerteFraudeController;
use Illuminate\Support\Facades\Route;

// Alertes de fraude : consultation et traitement réservés aux administrateurs.
Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('alertes-fraude', [AlerteFraudeController::class, 'index']);
    Route::get('alertes-fraude/{id}', [AlerteFraudeController::class, 'show']);
    Route::put('alertes-fraude/{id}', [AlerteFraudeController::class, 'update']);
});
