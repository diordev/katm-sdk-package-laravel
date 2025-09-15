<?php

namespace Katm\KatmSdk\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Enums\KatmApiEndpointEnum;
use Katm\KatmSdk\Enums\KatmCreditBanType;
use Katm\KatmSdk\Enums\KatmGenderTypeEnum;
use Katm\KatmSdk\Facades\Katm;
use Katm\KatmSdk\Tests\TestCase;

class KatmFacadeTest extends TestCase
{
    /** @test */
    //    public function authenticate_success(): void
    //    {
    //        $base = config('katm.base_url');
    //
    //        Http::fake([
    //            $base.KatmApiEndpointEnum::Auth->value => Http::response([
    //                'data' => [
    //                    'accessToken' => 'token_123',
    //                ],
    //                'error' => null,
    //                'success' => true,
    //                'total' => null,
    //            ], 200),
    //        ]);
    //
    //        $resp = Katm::authenticate();
    //
    //        $this->assertTrue($resp->success);
    //        $this->assertSame('token_123', $resp->data->accessToken);
    //        Http::assertSentCount(1);
    //        Http::assertSent(fn ($req) => $req->url() === $base.KatmApiEndpointEnum::Auth->value
    //            && $req->method() === 'POST'
    //            && $req['login'] === 'demo@login'
    //            && $req['password'] === 'secret'
    //        );
    //    }

    /** @test */
    //    public function init_client_uses_bearer_after_authenticate(): void
    //    {
    //        $base = config('katm.base_url');
    //
    //        // 1) Auth beriladi, 2) initClient bearer bilan ketadi
    //        Http::fake([
    //            $base.KatmApiEndpointEnum::Auth->value => Http::response([
    //                'data' => ['accessToken' => 'bearer_abc'],
    //                'error' => null,
    //                'success' => true,
    //                'total' => null,
    //            ], 200),
    //
    //            $base.KatmApiEndpointEnum::AuthClient->value => Http::response([
    //                'data' => ['pClientId' => 'C-001'],
    //                'error' => null,
    //                'success' => true,
    //                'total' => null,
    //            ], 200),
    //        ]);
    //
    //        // Avval authenticate
    //        Katm::authenticate();
    //
    //        // Keyin initClient
    //        $dto = new InitClientRequestDto(
    //            pPinfl: '00112233445566',
    //            pDocSeries: 'AD',
    //            pDocNumber: '1234567',
    //            pFirstName: 'Diyorbek',
    //            pLastName: 'Abdumutalibov',
    //            pMiddleName: "Abdumutallib o'g'li",
    //            pBirthDate: '1995-09-01',
    //            pIssueDocDate: '2022-08-05',
    //            pExpiredDocDate: '2032-08-04',
    //            pGender: KatmGenderTypeEnum::Male,
    //            pDistrictId: '1715',
    //            pResAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pRegAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pPhone: '+9989981774774',
    //            pEmail: 'demo@gmail.com'
    //        );
    //
    //        $resp = Katm::initClient($dto);
    //
    //        $this->assertTrue($resp->success);
    //        $this->assertSame('C-001', $resp->data['pClientId'] ?? null);
    //
    //        // Bearer header yuborilganini tekshiramiz
    //        Http::assertSent(fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value
    //            && $req->hasHeader('Authorization', 'Bearer bearer_abc')
    //        );
    //    }

    /** @test */
    //    public function init_client_results_on_401_and_retries_once(): void
    //    {
    //        $base = config('katm.base_url');
    //
    //        // Ketma-ketlik:
    //        // 1) initClient -> 401
    //        // 2) auth -> 200 (yangi token)
    //        // 3) initClient -> 200 (muvaffaqiyatli)
    //        Http::fake([
    //            $base.KatmApiEndpointEnum::AuthClient->value => Http::sequence()
    //                ->push([
    //                    'data' => null,
    //                    'error' => [
    //                        'errId' => 106,
    //                        'isFriendly' => true,
    //                        'errMsg' => 'Вы не авторизованы',
    //                    ],
    //                    'success' => false,
    //
    //                ], 401)
    //                ->push([
    //                    'success' => true,
    //                    'data' => ['pClientId' => 'C-777'],
    //                ], 200),
    //
    //            $base.KatmApiEndpointEnum::Auth->value => Http::response([
    //                'success' => true,
    //                'data' => ['accessToken' => 'new_token'],
    //            ], 200),
    //        ]);
    //
    //        // Keyin initClient
    //        $dto = new InitClientRequestDto(
    //            pPinfl: '00112233445566',
    //            pDocSeries: 'AD',
    //            pDocNumber: '1234567',
    //            pFirstName: 'Diyorbek',
    //            pLastName: 'Abdumutalibov',
    //            pMiddleName: "Abdumutallib o'g'li",
    //            pBirthDate: '1995-09-01',
    //            pIssueDocDate: '2022-08-05',
    //            pExpiredDocDate: '2032-08-04',
    //            pGender: KatmGenderTypeEnum::Male,
    //            pDistrictId: '1715',
    //            pResAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pRegAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pPhone: '+9989981774774',
    //            pEmail: 'demo@gmail.com'
    //        );
    //
    //        $resp = Katm::initClient($dto);
    //
    //        $this->assertTrue($resp->success);
    //        $this->assertSame('C-777', $resp->data['pClientId'] ?? null);
    //
    //        // 3 ta so‘rov bo‘lganini kutamiz: 401, auth, retry
    //        Http::assertSentCount(3);
    //
    //        // 2-chi so‘rov auth bo‘lganini va token qaytganini tekshirish
    //        Http::assertSentInOrder([
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value && $req->method() === 'POST',
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::Auth->value && $req->method() === 'POST',
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value
    //                && $req->method() === 'POST'
    //                && $req->hasHeader('Authorization', 'Bearer new_token'),
    //        ]);
    //    }

    /** @test */
    //    public function client_ban_active_uses_bearer_after_authenticate(): void
    //    {
    //        $base = config('katm.base_url');
    //
    //        // 1) Auth beriladi, 2) clientBan bearer bilan ketadi
    //        Http::fake([
    //            $base.KatmApiEndpointEnum::Auth->value => Http::response([
    //                'data' => ['accessToken' => 'bearer_abc'],
    //                'error' => null,
    //                'success' => true,
    //                'total' => null,
    //            ], 200),
    //
    //            $base.KatmApiEndpointEnum::CreditBanActive->value => Http::response([
    //
    //                'data' => [
    //                    'result' => '05000',
    //                    'resultMessage' => 'Запрет успешно добавлен',
    //                ],
    //                'error' => null,
    //                'success' => true,
    //                'total' => null,
    //            ], 200),
    //        ]);
    //
    //        // Avval authenticate
    //        Katm::authenticate();
    //
    //        // Keyin clientBan
    //        $type = KatmCreditBanType::ACTIVE;
    //        $dto = new InitClientRequestDto(
    //            pPinfl: '00112233445566',
    //            pDocSeries: 'AD',
    //            pDocNumber: '1234567',
    //            pFirstName: 'Diyorbek',
    //            pLastName: 'Abdumutalibov',
    //            pMiddleName: "Abdumutallib o'g'li",
    //            pBirthDate: '1995-09-01',
    //            pIssueDocDate: '2022-08-05',
    //            pExpiredDocDate: '2032-08-04',
    //            pGender: KatmGenderTypeEnum::Male,
    //            pDistrictId: '1715',
    //            pResAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pRegAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pPhone: '+9989981774774',
    //            pEmail: 'demo@gmail.com'
    //        );
    //
    //        $resp = Katm::creditBan($type, $dto);
    //
    //        $this->assertTrue($resp->success);
    //        $this->assertSame('05000', $resp->data['result'] ?? null);
    //
    //        // Bearer header yuborilganini tekshiramiz
    //        Http::assertSent(fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value
    //            && $req->hasHeader('Authorization', 'Bearer bearer_abc')
    //        );
    //    }

    /** @test */
    //    public function client_ban_active_flows_401_then_auth_then_400_then_init_then_success(): void
    //    {
    //        $base = config('katm.base_url');
    //
    //        Http::fake([
    //            // creditBanActive: 1) 401, 3) 400 (client not found), 5) 200 success
    //            $base.KatmApiEndpointEnum::CreditBanActive->value => Http::sequence()
    //                ->push([
    //                    'data' => null,
    //                    'error' => ['errId' => 106, 'isFriendly' => true, 'errMsg' => 'Вы не авторизованы'],
    //                    'success' => false,
    //                ], 401)
    //                ->push([
    //                    'data' => null,
    //                    'error' => ['errId' => 102, 'isFriendly' => true, 'errMsg' => 'Пользователь не найден'],
    //                    'success' => false,
    //                ], 400)
    //                ->push([
    //                    'success' => true,
    //                    'data' => ['result' => 'activated'],
    //                ], 200),
    //
    //            // 2) auth: token beradi
    //            $base.KatmApiEndpointEnum::Auth->value => Http::response([
    //                'success' => true,
    //                'data' => ['accessToken' => 'new_token'],
    //            ], 200),
    //
    //            // 4) initClient: ro'yxatdan o'tkazadi
    //            $base.KatmApiEndpointEnum::AuthClient->value => Http::response([
    //                'success' => true,
    //                'data' => ['pClientId' => 'C-777'],
    //            ], 200),
    //        ]);
    //
    //        $dto = new InitClientRequestDto(
    //            pPinfl: '00112233445566',
    //            pDocSeries: 'AD',
    //            pDocNumber: '1234567',
    //            pFirstName: 'Diyorbek',
    //            pLastName: 'Abdumutalibov',
    //            pMiddleName: "Abdumutallib o'g'li",
    //            pBirthDate: '1995-09-01',
    //            pIssueDocDate: '2022-08-05',
    //            pExpiredDocDate: '2032-08-04',
    //            pGender: KatmGenderTypeEnum::Male,
    //            pDistrictId: '1715',
    //            pResAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pRegAddress: 'Dalvarzin MFY, 4-Tor X.Niyoziy, 77-uy',
    //            pPhone: '+9989981774774',
    //            pEmail: 'demo@gmail.com'
    //        );
    //
    //        // Facade orqali umumiy metod (manager->credit()->creditBan(...))
    //        $resp = Katm::creditBan(KatmCreditBanType::ACTIVE, $dto);
    //
    //        $this->assertTrue($resp->success);
    //        $this->assertSame('activated', $resp->data['result'] ?? null);
    //
    //        // 5 ta so‘rov: creditBan(401), auth(200), creditBan(400), init(200), creditBan(200)
    //        Http::assertSentCount(5);
    //
    //        Http::assertSentInOrder([
    //            // 1) creditBanActive (401)
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value
    //                && $req->method() === 'POST',
    //
    //            // 2) auth (200)
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::Auth->value
    //                && $req->method() === 'POST',
    //
    //            // 3) creditBanActive (400) — endi Bearer bilan
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value
    //                && $req->method() === 'POST'
    //                && $req->hasHeader('Authorization', 'Bearer new_token'),
    //
    //            // 4) initClient (200) — Bearer bilan
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value
    //                && $req->method() === 'POST'
    //                && $req->hasHeader('Authorization', 'Bearer new_token'),
    //
    //            // 5) creditBanActive (200) — Bearer bilan
    //            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value
    //                && $req->method() === 'POST'
    //                && $req->hasHeader('Authorization', 'Bearer new_token'),
    //        ]);
    //    }
    /** @test */
    public function credit_ban_active_flows_401_then_auth_then_400_then_init_then_success(): void
    {
        $base = config('katm.base_url');

        Http::fake([
            // 1) creditBanActive -> 401, 3) creditBanActive -> 400, 5) creditBanActive -> 200
            $base.KatmApiEndpointEnum::CreditBanActive->value => Http::sequence()
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
                    'data' => null,
                    'error' => [
                        'errId' => 102,
                        'isFriendly' => true,
                        'errMsg' => 'Пользователь не найден',
                    ],
                    'success' => false,
                ], 400)
                ->push([
                    'data' => [
                        'result' => '05000',
                        'resultMessage' => 'Запрет успешно добавлен',
                    ],
                    'error' => null,
                    'success' => true,
                    'total' => null,
                ], 200),

            // 2) auth -> 200 (token)
            $base.KatmApiEndpointEnum::Auth->value => Http::response([
                'success' => true,
                'data' => ['accessToken' => 'new_token'],
            ], 200),

            // 4) initClient -> 200
            $base.KatmApiEndpointEnum::AuthClient->value => Http::response([
                'success' => true,
                'data' => ['pClientId' => 'C-777'],
            ], 200),
        ]);

        $dto = new InitClientRequestDto(
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

        // Facade orqali chaqirish
        $resp = Katm::creditBan(KatmCreditBanType::ACTIVE, $dto);

        $this->assertTrue($resp->success);
        $this->assertSame('05000', $resp->data['result'] ?? null);

        // 5 ta so‘rov: creditBan(401), auth(200), creditBan(400), initClient(200), creditBan(200)
        Http::assertSentCount(5);

        Http::assertSentInOrder([
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value && $req->method() === 'POST',
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::Auth->value && $req->method() === 'POST',
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value && $req->method() === 'POST',
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::AuthClient->value && $req->method() === 'POST',
            fn ($req) => $req->url() === $base.KatmApiEndpointEnum::CreditBanActive->value && $req->method() === 'POST',
        ]);
    }
}
