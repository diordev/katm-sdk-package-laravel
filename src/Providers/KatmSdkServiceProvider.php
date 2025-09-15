<?php

namespace Katm\KatmSdk\Providers;

use Illuminate\Support\ServiceProvider;
use Katm\KatmSdk\Services\Auth\KatmAuthService;
use Katm\KatmSdk\Services\Credit\KatmCreditService;
use Katm\KatmSdk\Services\KatmManagerService;

class KatmSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/katm.php', 'katm');

        // Manager singleton
        // Provider (singleton + facade accessor)
        $this->app->singleton(KatmManagerService::class, function ($app) {
            return new KatmManagerService(
                $app->make(KatmAuthService::class),
                $app->make(KatmCreditService::class),
            );
        });

    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../../config/katm.php' => config_path('katm.php'),
        ], 'katm-config');
    }
}
