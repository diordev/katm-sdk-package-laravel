<?php

namespace Katm\KatmSdk\Providers;

use Illuminate\Support\ServiceProvider;
use Katm\KatmSdk\Services\Auth\KatmAuthService;
use Katm\KatmSdk\Services\Auth\KatmInitClientService;
use Katm\KatmSdk\Services\Credit\KatmCreditService;
use Katm\KatmSdk\Services\KatmManagerService;
use Katm\KatmSdk\Services\Report\KatmReportService;

class KatmSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/katm.php', 'katm');

        // Manager singleton
        $this->app->singleton('katm.manager', function ($app) {
            return new KatmManagerService(
                $app->make(KatmAuthService::class),
                $app->make(KatmInitClientService::class),
                $app->make(KatmCreditService::class),
                $app->make(KatmReportService::class),
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
