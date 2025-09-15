<?php

namespace Katm\KatmSdk\Providers;

use Illuminate\Support\ServiceProvider;
use Katm\KatmSdk\Services\Auth\KatmAuthService;
use Katm\KatmSdk\Services\Credit\KatmCreditService;
use Katm\KatmSdk\Services\KatmManagerService;

/**
 * Class KatmSdkServiceProvider
 *
 * Laravel ilovasi uchun KATM SDK paketini ro‘yxatdan o‘tkazuvchi service provider.
 *
 * Ushbu provider quyidagi ishlarni bajaradi:
 * - Konfiguratsiya faylini birlashtiradi (`config/katm.php`)
 * - `KatmManagerService` singleton sifatida konteynerga bog‘lanadi
 * - Konfiguratsiyani `vendor:publish` orqali chop etish imkonini beradi
 */
class KatmSdkServiceProvider extends ServiceProvider
{
    /**
     * SDK xizmatlarini Laravel konteyneriga ro‘yxatdan o‘tkazadi.
     * `KatmManagerService` singleton sifatida bog‘lanadi.
     */
    public function register(): void
    {
        // Konfiguratsiyani birlashtirish
        $this->mergeConfigFrom(
            __DIR__.'/../../config/katm.php',
            'katm'
        );

        // KATM SDK boshqaruvchi servisni singleton sifatida ro‘yxatga olish
        $this->app->singleton(KatmManagerService::class, function ($app) {
            return new KatmManagerService(
                $app->make(KatmAuthService::class),
                $app->make(KatmCreditService::class),
            );
        });
    }

    /**
     * SDK konfiguratsiyasini publish qilish imkonini beradi.
     *
     * `php artisan vendor:publish --tag=katm-config`
     */
    public function boot(): void
    {
        // Konfiguratsiyani chop etish
        $this->publishes([
            __DIR__.'/../../config/katm.php' => config_path('katm.php'),
        ], 'katm-config');
    }
}
