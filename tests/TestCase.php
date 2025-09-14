<?php

namespace Katm\KatmSdk\Tests;

use Katm\KatmSdk\Facades\Katm as KatmFacade;
use Katm\KatmSdk\Providers\KatmSdkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [KatmSdkServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Katm' => KatmFacade::class,
        ];
    }
}
