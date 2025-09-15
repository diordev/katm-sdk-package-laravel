<?php

namespace Katm\KatmSdk\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Katm\KatmSdk\Enums\KatmApiEndpointEnum;
use Katm\KatmSdk\Facades\Katm;
use Katm\KatmSdk\Tests\TestCase;

class KatmFacadeTest extends TestCase
{
    protected string $base;

    protected function setUp(): void
    {
        parent::setUp();
        $this->base = config('katm.base_url');
    }

    public function test_authenticate_success(): void
    {
        $base = config('katm.base_url');

        Http::fake([
            $base.KatmApiEndpointEnum::Auth->value => Http::response([
                'data' => [
                    'accessToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9
                    .eyJtZXJjaGFudElkIjoyMywicm9sZSI6InVzZXIiLCJuYW1lIjoibWtiYW5rIiwidXJsIjpudWxsLCJpYXQiOjE3NTc5MjgzOTMsImV4cCI6MTc1ODAxNDc5M30
                    .QCm_bdh86FOCZlAih6G6p5wFieCuzGq9Opvi_0goRvg',
                ],
                'error' => null,
                'success' => true,
                'total' => null,
            ], 200),
        ]);

        $resp = Katm::authenticate();

        $this->assertTrue($resp->success);

        $this->assertSame(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9
                    .eyJtZXJjaGFudElkIjoyMywicm9sZSI6InVzZXIiLCJuYW1lIjoibWtiYW5rIiwidXJsIjpudWxsLCJpYXQiOjE3NTc5MjgzOTMsImV4cCI6MTc1ODAxNDc5M30
                    .QCm_bdh86FOCZlAih6G6p5wFieCuzGq9Opvi_0goRvg', $resp->data->accessToken
        );

        Http::assertSentCount(1);

        Http::assertSent(fn ($req) => $req->url() === $base.KatmApiEndpointEnum::Auth->value
            && $req->method() === 'POST'
            && $req['login'] === 'demo@login'
            && $req['password'] === 'secret'
        );
    }

    public function test_init_client_uses_bearer_after_authenticate(): void
    {
        $base = config('katm.base_url');

        // 1) Auth beriladi, 2) initClient bearer bilan ketadi
        Http::fake([
            $base.KatmApiEndpointEnum::Auth->value => Http::response([
                'data' => ['accessToken' => 'bearer_abc'],
                'error' => null,
                'success' => true,
                'total' => null,
            ], 200),

            $base.KatmApiEndpointEnum::AuthClient->value => Http::response([
                'data' => ['pClientId' => 'C-001'],
                'error' => null,
                'success' => true,
                'total' => null,
            ], 200),
        ]);

        // Avval authenticate
        Katm::authenticate();

        // Keyin initClient
        $resp = Katm::initClient($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertSame('C-001', $resp->data['pClientId'] ?? null);

        // Bearer header yuborilganini tekshiramiz
        Http::assertSent(fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value
            && $req->hasHeader('Authorization', 'Bearer bearer_abc')
        );
    }

    public function test_init_client_return_once_on_401_and_succeeds(): void
    {
        $base = config('katm.base_url');

        Cache::forget('katm:token');
        Cache::put('katm:token', 'expired_token', now()->addMinutes(5));

        Http::fake([
            // 1) initClient -> 401 Unauthorized
            // 2) initClient -> 200 Success (retry after auth)
            $base.KatmApiEndpointEnum::AuthClient->value => Http::sequence()
                ->push([
                    'data' => null,
                    'error' => [
                        'errId' => 106,
                        'isFriendly' => true,
                        'errMsg' => 'Вы не авторизованы',
                    ],
                    'success' => false,
                ], 401)
                ->push([
                    'success' => true,
                    'data' => ['pClientId' => 'C-777'],
                ], 200),

            // 3) Auth -> 200 (token qaytaradi)
            $base.KatmApiEndpointEnum::Auth->value => Http::response([
                'success' => true,
                'data' => ['accessToken' => 'new_token'],
            ], 200),
        ]);

        $resp = Katm::initClient($this->makeInitClientDto());

        // ✅ Success bo‘lishi kerak
        $this->assertTrue($resp->success);
        $this->assertSame('C-777', $resp->data['pClientId'] ?? null);

        // ✅ 3 ta request bo‘lishi kerak: initClient (401), auth (200), initClient (200)
        Http::assertSentCount(3);

        // ✅ So‘rovlar ketma-ketligi
        Http::assertSentInOrder([
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value && $req->method() === 'POST',
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::Auth->value && $req->method() === 'POST',
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value
                && $req->method() === 'POST'
                && $req->hasHeader('Authorization', 'Bearer new_token'),
        ]);
    }
}
