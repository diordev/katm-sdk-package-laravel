<?php

namespace Katm\KatmSdk\Tests;

use Illuminate\Support\Facades\Config;
use Katm\KatmSdk\Facades\Katm;
use Katm\KatmSdk\Providers\KatmSdkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            KatmSdkServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Katm' => Katm::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Paket configlarini sozlaymiz
        Config::set('katm', [
            'base_url' => 'https://api.example.com',
            'username' => 'demo@login',
            'password' => 'secret',
            'timeout' => 5,
            'headers' => ['Accept' => 'application/json'],
            'retry' => [
                'tries' => 0,
                'sleep_ms' => 0,
                'when' => [429, 500, 502, 503, 504],
            ],
            'add_request_id' => false,
        ]);
        config()->set('data.throw_when_max_depth_reached', false);
    }
}
