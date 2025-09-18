<?php

declare(strict_types=1);

namespace Katm\KatmSdk\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Katm\KatmSdk\Enums\KatmApiEndpointEnum;
use Katm\KatmSdk\Facades\Katm;
use Katm\KatmSdk\Tests\TestCase;

final class KatmCreditServiceTest extends TestCase
{
    protected string $base;

    protected function setUp(): void
    {
        parent::setUp();
        $this->base = rtrim((string) config('katm.base_url'), '/');
        Cache::forget('katm:token');
    }

    /** @test */
    public function credit_ban_status_adds_message_when_status_0(): void
    {
        Cache::put('katm:token', 'valid_token', 300);

        Http::fake([
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::response([
                'success' => true,
                'data' => ['status' => 0],
            ], 200),
        ]);

        $resp = Katm::creditBanStatus($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertSame('Запрет не активирован', $resp->data['resultMessage'] ?? null);

        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value
            && $r->method() === 'POST'
            && $r->hasHeader('Authorization') // bearer bilan yuborilgan
        );
    }

    /** @test */
    public function credit_ban_status_adds_message_when_status_1(): void
    {
        Cache::put('katm:token', 'valid_token', 300);

        Http::fake([
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::response([
                'success' => true,
                'data' => ['status' => 1],
            ], 200),
        ]);

        $resp = Katm::creditBanStatus($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertSame('Запрет активирован', $resp->data['resultMessage'] ?? null);

        Http::assertSentCount(1);
    }

    /** @test */
    public function credit_ban_status_init_client_on_bad_request_102_then_succeeds(): void
    {
        Cache::put('katm:token', 'valid_token', 300);

        Http::fake([
            // 1) status -> 400 (client not found), 2) status -> 200
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::sequence()
                ->push([
                    'success' => false,
                    'error' => [
                        'errId' => 102,
                        'isFriendly' => true,
                        'errMsg' => 'Client not found'],
                ], 400)
                ->push([
                    'success' => true,
                    'data' => ['status' => 1],
                ], 200),

            // init-client -> 200
            $this->base.KatmApiEndpointEnum::AuthClient->value => Http::response([
                'success' => true,
                'data' => ['clientId' => 123],
            ], 200),
        ]);

        $resp = Katm::creditBanStatus($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertSame('Запрет активирован', $resp->data['resultMessage'] ?? null);

        Http::assertSentCount(3);
        Http::assertSentInOrder([
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::AuthClient->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value,
        ]);
    }

    /** @test */
    public function credit_ban_status_returns_dto_on_other_bad_request(): void
    {
        Cache::put('katm:token', 'valid_token', 300);

        Http::fake([
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::response([
                'success' => false,
                'error' => ['errId' => 999, 'isFriendly' => true, 'errMsg' => 'Some business error'],
            ], 400),
        ]);

        $resp = Katm::creditBanStatus($this->makeInitClientDto());

        $this->assertFalse($resp->success ?? false);

        // DTO obyekt/array farqini xavfsiz o‘qish
        $errMsg = is_array($resp->error ?? null)
            ? ($resp->error['errMsg'] ?? null)
            : ($resp->error->errMsg ?? null);

        // Mapper "HTTP 400: ..." prefiks qo'shishi mumkin — substring bilan tekshiramiz
        $this->assertNotNull($errMsg);
        $this->assertIsString($errMsg);
        $this->assertStringContainsString('Some business error', $errMsg);

        Http::assertSentCount(1);
    }

    /** @test */
    public function credit_ban_active_short_circuits_when_already_active(): void
    {
        Cache::put('katm:token', 'valid_token', 300);

        Http::fake([
            // status -> already active
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::response([
                'success' => true,
                'data' => ['status' => 1],
            ], 200),
            // activation endpoint umuman chaqirilmasligi kerak
            $this->base.KatmApiEndpointEnum::CreditBanActive->value => Http::response([
                'success' => true,
                'data' => ['activated' => true],
            ], 200),
        ]);

        $resp = Katm::creditBanActive($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertSame(1, $resp->data['status'] ?? null);
        // Activation chaqirilmagan bo‘lishi kerak
        Http::assertSentCount(1);
        Http::assertNotSent(fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanActive->value);
    }

    /** @test */
    public function credit_ban_active_reauth_on_401_then_success_and_appends_for_deactivation(): void
    {
        Cache::forget('katm:token');

        Http::fake([
            // Birinchi status: 0 (aktiv emas) — old_token bilan yuboriladi
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::response([
                'success' => true,
                'data' => ['status' => 0],
            ], 200),

            // Aktivatsiya: 1) 401 (old_token), 2) 200 (new_token)
            $this->base.KatmApiEndpointEnum::CreditBanActive->value => Http::sequence()
                ->push([
                    'success' => false,
                    'error' => ['errId' => 106, 'isFriendly' => true, 'errMsg' => 'Вы не авторизованы'],
                ], 401)
                ->push([
                    'success' => true,
                    'data' => ['activated' => true],
                ], 200),

            // Auth ketma-ketligi: 1) old_token (ensureToken), 2) new_token (401 dan keyin)
            $this->base.KatmApiEndpointEnum::Auth->value => Http::sequence()
                ->push([
                    'success' => true,
                    'data' => ['accessToken' => 'old_token'],
                ], 200)
                ->push([
                    'success' => true,
                    'data' => ['accessToken' => 'new_token'],
                ], 200),
        ]);

        $resp = Katm::creditBanActive($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertTrue($resp->data['activated'] ?? false);

        // Ketma-ketlik (odatiy holatda 5 chaqiriq bo‘ladi):
        // Auth(old) → Status(0, old) → Active(401, old) → Auth(new) → Active(200, new)
        Http::assertSentCount(5);
        Http::assertSentInOrder([
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::Auth->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value
                && $r->hasHeader('Authorization', 'Bearer old_token'),
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanActive->value
                && $r->hasHeader('Authorization', 'Bearer old_token'),
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::Auth->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanActive->value
                && $r->hasHeader('Authorization', 'Bearer new_token'),
        ]);
    }

    /** @test */
    public function credit_ban_active_init_client_on_bad_request_102_then_success(): void
    {
        Cache::put('katm:token', 'valid_token', 300);

        Http::fake([
            // 0) status -> 0 (aktiv emas)
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::response([
                'success' => true,
                'data' => ['status' => 0],
            ], 200),

            // 1) activation -> 400 (client not found), 2) activation -> 200
            $this->base.KatmApiEndpointEnum::CreditBanActive->value => Http::sequence()
                ->push([
                    'success' => false,
                    'error' => ['errId' => 102, 'errMsg' => 'Client not found'],
                ], 400)
                ->push([
                    'success' => true,
                    'data' => ['activated' => true],
                ], 200),

            // init-client -> 200
            $this->base.KatmApiEndpointEnum::AuthClient->value => Http::response([
                'success' => true,
                'data' => ['clientId' => 987],
            ], 200),
        ]);

        $resp = Katm::creditBanActive($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertTrue($resp->data['activated'] ?? false);

        Http::assertSentCount(4);
        Http::assertSentInOrder([
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanActive->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::AuthClient->value,
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanActive->value,
        ]);
    }

    /** @test */
    public function credit_ban_status_reauths_on_401_then_succeeds(): void
    {
        // Cache bilan ishlamaslik uchun token qo‘ymaymiz. ensureToken() birinchi Auth’ni chaqiradi.
        Cache::forget('katm:token');

        Http::fake([
            // Auth ketma-ketligi: 1) old_token, 2) new_token
            $this->base.KatmApiEndpointEnum::Auth->value => Http::sequence()
                ->push([
                    'success' => true,
                    'data' => ['accessToken' => 'old_token'],
                ], 200)
                ->push([
                    'success' => true,
                    'data' => ['accessToken' => 'new_token'],
                ], 200),

            // Status: 1) 401 (old_token bilan yuborilganida), 2) 200 (new_token bilan)
            $this->base.KatmApiEndpointEnum::CreditBanStatus->value => Http::sequence()
                ->push([
                    'success' => false,
                    'error' => [
                        'errId' => 106,
                        'isFriendly' => true,
                        'errMsg' => 'Вы не авторизованы',
                    ],
                ], 401)
                ->push([
                    'success' => true,
                    'data' => ['status' => 0],
                ], 200),
        ]);

        $resp = Katm::creditBanStatus($this->makeInitClientDto());

        $this->assertTrue($resp->success);
        $this->assertSame('Запрет не активирован', $resp->data['resultMessage'] ?? null);

        // Kutilgan ketma-ketlik: Auth(old_token) → Status(401, old_token) → Auth(new_token) → Status(200, new_token)
        Http::assertSentCount(4);
        Http::assertSentInOrder([
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::Auth->value
                && $r->method() === 'POST',
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value
                && $r->method() === 'POST'
                && $r->hasHeader('Authorization', 'Bearer old_token'),
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::Auth->value
                && $r->method() === 'POST',
            fn ($r) => $r->url() === $this->base.KatmApiEndpointEnum::CreditBanStatus->value
                && $r->method() === 'POST'
                && $r->hasHeader('Authorization', 'Bearer new_token'),
        ]);
    }
}
