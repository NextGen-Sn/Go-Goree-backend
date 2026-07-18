<?php

use App\Http\Controllers\Api\Webhooks\PayDunyaWebhookController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::post('webhooks/paydunya', [PayDunyaWebhookController::class, 'handle'])
                ->name('webhooks.paydunya');
        }
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        // Le frontend (SPA) s'authentifie par Bearer token (Sanctum), pas par
        // session/cookie : sans ça, "channels:" ci-dessus enregistrerait
        // /broadcasting/auth avec le middleware "web" par défaut, qui ignore
        // le token et renvoie systématiquement 403.
        ['middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Backend API + SPA pur : aucune route "login" HTML n'existe (routes/web.php
        // n'a qu'une page d'accueil). Sans ce "true" inconditionnel, une requête non
        // authentifiée hors "api/*" (ex : /broadcasting/auth) provoque un 500
        // "Route [login] not defined" au lieu d'un 401 JSON propre.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => true,
        );
    })->create();
