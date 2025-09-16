<?php

declare(strict_types=1);

namespace Katm\KatmSdk\Services\Auth;

use Katm\KatmSdk\Dto\Requests\AuthRequestDto;
use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Dto\Responses\KatmResponseDto;
use Katm\KatmSdk\Enums\KatmApiEndpointEnum;
use Katm\KatmSdk\Enums\KatmAuthTypeEnum;
use Katm\KatmSdk\HttpExceptions\Client\UnauthorizedException;
use Katm\KatmSdk\Services\AbstractHttpClientService;

/**
 * KatmAuthService
 *
 * KATM API uchun autentifikatsiya va boshlang‘ich client ro‘yxatdan o‘tkazish amallarini bajaradi.
 *
 * Ushbu servis quyidagi imkoniyatlarni beradi:
 * - `/auth/login` endpoint orqali token olish (basic auth bilan)
 * - `/auth/init-client` orqali foydalanuvchini ro‘yxatga olish
 * - Bearer tokenni saqlash va qayta foydalanish
 *
 * Tokenlar avtomatik ravishda `withBearer()` orqali bazaviy klassga uzatiladi.
 */
final class KatmAuthService extends AbstractHttpClientService
{
    /**
     * Foydalanuvchining login va paroli orqali autentifikatsiya qiladi.
     *
     * KATM API’dan access token oladi va uni bearer sifatida xotirada saqlaydi.
     *
     * @return KatmResponseDto API javobi (token va holat)
     */
    public function authenticate(): KatmResponseDto
    {
        $payload = new AuthRequestDto(
            login: $this->username,
            password: $this->password
        );

        $res = $this->post(
            path: KatmApiEndpointEnum::Auth->value,
            payload: $payload->toArray(),
            auth: KatmAuthTypeEnum::AuthNone->value
        );

        // Eski accessToken ni olib tashlaymiz
        $this->withoutBearer();

        // Yangi accessToken ni bearer sifatida saqlaymiz
        $this->withBearer($res['data']['accessToken'] ?? null);

        return KatmResponseDto::from($res);
    }

    /**
     * Mijozni birinchi marta tizimda ro‘yxatdan o‘tkazadi.
     *
     * Ushbu metod /auth/init-client endpointga murojaat qiladi.
     * - Agar bearer token mavjud bo‘lsa, u bilan yuboriladi.
     * - Agar 401 (Unauthorized) qaytsa, autentifikatsiya qilinib qayta yuboriladi.
     *
     * @param  InitClientRequestDto  $payload  Mijoz haqidagi ma’lumotlar
     * @return KatmResponseDto API javobi
     *
     * @throws UnauthorizedException
     */
    public function initClient(InitClientRequestDto $payload): KatmResponseDto
    {
        if (! $this->restoreTokenFromCache()) {
            $this->authenticate();
        }
        $send = fn () => $this->post(
            path: KatmApiEndpointEnum::AuthClient->value,
            payload: $payload->toArray(),
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $send();
        } catch (UnauthorizedException) {
            // Agar token expire bo'lgan bo'lsa, faqat 401 bo‘lsa, re-auth qilamiz
            $this->authenticate();
            $res = $send();
        }

        return KatmResponseDto::from($res);
    }
}
