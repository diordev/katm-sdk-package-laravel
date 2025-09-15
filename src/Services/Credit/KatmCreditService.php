<?php

declare(strict_types=1);

namespace Katm\KatmSdk\Services\Credit;

use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Dto\Responses\KatmResponseDto;
use Katm\KatmSdk\Enums\KatmApiEndpointEnum;
use Katm\KatmSdk\Enums\KatmAuthTypeEnum;
use Katm\KatmSdk\HttpExceptions\Client\BadRequestException;
use Katm\KatmSdk\HttpExceptions\Client\UnauthorizedException;
use Katm\KatmSdk\Services\AbstractHttpClientService;
use Katm\KatmSdk\Services\Auth\KatmAuthService;

/**
 * KatmCreditService
 *
 * KATM API orqali kredit bo‘yicha taqiqlash (ban) operatsiyalarini bajaradi.
 *
 * Quyidagi ishlarni avtomatik bajaradi:
 * - Bearer token mavjud bo‘lmasa, `authenticate()` chaqiradi
 * - Kreditga oid so‘rovni yuboradi (active yoki status)
 * - 401 holatda qayta autentifikatsiya qiladi
 * - 400 holatda agar client topilmasa, `initClient()` bilan ro‘yxatdan o‘tkazadi
 */
final class KatmCreditService extends AbstractHttpClientService
{
    /**
     * @param  KatmAuthService  $auth  Autentifikatsiya servisi (token olish, init client va h.k.)
     */
    public function __construct(private readonly KatmAuthService $auth)
    {
        parent::__construct();
    }

    /**
     * Kredit bo‘yicha ban operatsiyasi (ACTIVE yoki STATUS).
     *
     * Ishlash tartibi:
     * - So‘rov yuboriladi:
     *    - creditBanStatus() metod orqali status tekshiradi
     *      - Agar status=1 → allaqachon aktiv, shu javobni qaytaradi
     *      - Agar status=0 → ban qo‘yadi
     *    - 400 (BadRequest, client not found/102) → bir marta initClient + retry
     *    - 400 (BadRequest, client not found/110) → pinfl xato response
     * - Aks xatolar — tashlanadi
     *
     * @throws UnauthorizedException|BadRequestException
     */
    public function creditBanActive(InitClientRequestDto $dto): KatmResponseDto
    {
        if (! $this->restoreTokenFromCache()) {
            $this->auth->authenticate();
        }

        // 1) Agar allaqachon aktiv bo‘lsa, shu javobni qaytarib qo‘yamiz
        if (($statusResp->data['status'] ?? null) === 1) {
            return $statusResp;
        }

        // 2) Ban qo‘yish
        $payload = $dto->toCreditBanActiveDto();
        $send = fn () => $this->post(
            path: KatmApiEndpointEnum::CreditBanActive->value,
            payload: $payload,
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $send();
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                return KatmResponseDto::from($e->toArray());
            } else {
                $this->auth->authenticate();
                $res = $send();
            }
        }
        $res['data']['for_deactication'] = [
            'url' => 'https://portal.infokredit.uz/ban',
            'apps' => 'KATM, MyGo'
        ];


        return KatmResponseDto::from($res);
    }

    /**
     * Kredit ban statusini tekshirish.
     * Cashda Bearer yo‘q bo‘lsa → authenticate() + withBearer()
     * Client Identifikatsiyadan o'tmagan bo'lsa initClient()
     * Ishlash logikasi:
     * - Agar `success = true` bo‘lsa:
     *   - status = 0 → "Запрет не активирован"
     *   - status = 1 → "Запрет активирован"
     *
     * @throws BadRequestException
     */
    public function creditBanStatus(InitClientRequestDto $dto): KatmResponseDto
    {
        if (! $this->restoreTokenFromCache()) {
            $this->auth->authenticate();
        }

        // 1) Status tekshirish
        $payload = $dto->toCreditBanStatusDto();
        $send = fn () => $this->post(
            path: KatmApiEndpointEnum::CreditBanStatus->value,
            payload: $payload,
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $send();
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                $this->auth->initClient($dto);
                $res = $send();
            } else {
                return KatmResponseDto::from($e->toArray());
            }
        }

        // 2) Agar success = true → status bo‘yicha resultMessage qo‘shamiz
        $status = $res['data']['status'] ?? null;
        $res['data']['resultMessage'] = match ($status) {
            0 => 'Запрет не активирован',
            1 => 'Запрет активирован',
            default => 'Неизвестный статус',
        };

        return KatmResponseDto::from($res);
    }

    /**
     * 400 (BadRequestException) ichidagi xatolik foydalanuvchi topilmaganini bildiradimi — aniqlaydi.
     *
     * @param  BadRequestException  $e  400 xatolik
     * @return bool true bo‘lsa — client yo‘qligi aniqlangan
     */
    private function isClientNotFound(BadRequestException $e): bool
    {
        return match ($e->errId) {
            102, 110 => true
        };

    }
}
