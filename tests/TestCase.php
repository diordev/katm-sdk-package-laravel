<?php

namespace Katm\KatmSdk\Tests;

use Illuminate\Support\Facades\Config;
use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Enums\KatmGenderTypeEnum;
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
            'verify_ssl' => false,
            'token_ttl' => 300,
        ]);

        // Cache driver va Redis sozlamalari (env() ishlatmaymiz!)
        Config::set('cache.default', 'redis');
        Config::set('database.redis.default', [
            'host' => '127.0.0.1',
            'password' => null,
            'port' => 6379,
            'database' => 0,
        ]);

        // Laravel Data max depth config
        Config::set('data.throw_when_max_depth_reached', false);
    }

    protected function makeInitClientDto(): InitClientRequestDto
    {
        return new InitClientRequestDto(
            pPinfl: '00112233445566',
            pDocSeries: 'AD',
            pDocNumber: '1234567',
            pFirstName: 'Diyorbek',
            pLastName: 'Abdumutalibov',
            pMiddleName: "Abdumutallib o'g'li",
            pBirthDate: '1995-09-01',
            pIssueDocDate: '2022-08-05',
            pExpiredDocDate: '2032-08-04',
            pGender: KatmGenderTypeEnum::Male,
            pDistrictId: '1715',
            pResAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
            pRegAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
            pPhone: '+9989981774774',
            pEmail: 'demo@gmail.com'
        );
    }
}
