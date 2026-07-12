<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    'paydunya' => [
        'mode' => env('PAYDUNYA_MODE', 'fake'),
        'master_key' => env('PAYDUNYA_MASTER_KEY'),
        'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
        'public_key' => env('PAYDUNYA_PUBLIC_KEY'),
        'token' => env('PAYDUNYA_TOKEN'),
        'base_url' => env('PAYDUNYA_BASE_URL', 'https://app.paydunya.com/api/v1'),
        'environment' => env('PAYDUNYA_ENVIRONMENT', 'test'),
        'webhook_secret' => env('PAYDUNYA_WEBHOOK_SECRET'),
        'webhook_url' => env('PAYDUNYA_WEBHOOK_URL'),
        'return_url' => env('PAYDUNYA_RETURN_URL'),
        'cancel_url' => env('PAYDUNYA_CANCEL_URL'),
    ],

    'fraude' => [
        'velocite_max_tentatives' => env('FRAUDE_VELOCITE_MAX_TENTATIVES', 5),
        'velocite_fenetre_minutes' => env('FRAUDE_VELOCITE_FENETRE_MINUTES', 10),
        'alerte_critique_mail_immediat' => env('FRAUDE_ALERTE_CRITIQUE_MAIL_IMMEDIAT', true),
    ],
];
