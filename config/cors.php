<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))),

    /*
     * En local, les fronts changent de port au gré des outils (Vite prend 5174
     * si 5173 est pris, Expo web 8081/8082/8083...). Plutôt que de rouvrir le
     * .env à chaque fois, on accepte n'importe quel port sur la machine locale.
     *
     * Ce n'est pas l'équivalent d'un joker : une page servie depuis Internet a
     * une tout autre origine et reste refusée. En production, seule la liste
     * CORS_ALLOWED_ORIGINS s'applique — ce motif n'y correspond à rien.
     */
    'allowed_origins_patterns' => [
        '#^http://(localhost|127\.0\.0\.1)(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
