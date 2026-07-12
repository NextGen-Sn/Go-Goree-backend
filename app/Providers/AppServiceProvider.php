<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(\App\Services\Paiements\PayDunya\PayDunyaClientInterface::class, function ($app) {
            return config('services.paydunya.mode') === 'real'
                ? $app->make(\App\Services\Paiements\PayDunya\PayDunyaClient::class)
                : $app->make(\App\Services\Paiements\PayDunya\FakePayDunyaClient::class);
        });
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
