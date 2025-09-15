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

final class KatmAuthService extends AbstractHttpClientService
{
    /**
     * Basic auth bilan token olib, bearer’ni o‘rnatadi.
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

        $data = KatmResponseDto::from($res);
        // accessToken ni bearer sifatida saqlaymiz
        $this->withBearer($data->data->accessToken ?? null);

        return $data;
    }

    /**
     * Mijozni boshlang‘ich ro‘yxatdan o‘tkazish (bearer bilan).
     * 401 bo‘lsa avtomatik re-auth qilib, 1 marta qayta urinadi.
     */
    public function initClient(InitClientRequestDto $payload): KatmResponseDto
    {

        $send = fn (string $auth) => $this->post(
            path: KatmApiEndpointEnum::AuthClient->value,
            payload: $payload->toArray(),
            auth: $auth
        );

        $firstAuth = ($this->bearer ?? '') === ''
            ? KatmAuthTypeEnum::AuthNone->value
            : KatmAuthTypeEnum::AuthBearer->value;

        try {
            $res = $send($firstAuth);
        } catch (UnauthorizedException) {
            // faqat 401 da qayta autentifikatsiya
            $this->authenticate();
            $res = $send(KatmAuthTypeEnum::AuthBearer->value);
        }

        return KatmResponseDto::from($res);
    }

    public function currentToken(): ?string
    {
        return $this->bearer;
    }
}
