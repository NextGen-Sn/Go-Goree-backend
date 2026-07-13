<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Inscription publique d'un client.
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');

// Rate limiting contre le bruteforce (6 tentatives / minute / IP).
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

// Mot de passe : demande de lien + définition via jeton (routes publiques, limitées).
Route::post('/password/forgot', [PasswordResetController::class, 'forgot'])->middleware('throttle:6,1');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
