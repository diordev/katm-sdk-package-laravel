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
     * - Aks xatolar — tashlanadi
     *
     * @throws UnauthorizedException|BadRequestException
     */
    public function creditBanActive(InitClientRequestDto $dto): KatmResponseDto
    {

        // 1) Avval statusni tekshiramiz
        try {
            $statusResp = $this->creditBanStatus($dto);
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                $this->auth->initClient($dto);
                $statusResp = $this->creditBanStatus($dto);
            } else {
                throw $e;
            }
        }

        // 2) Agar allaqachon aktiv bo‘lsa, shu javobni qaytarib qo‘yamiz
        if (($statusResp->data['status'] ?? null) === 1) {
            return $statusResp;
        }

        // 3) Ban qo‘yish
        $payload = $dto->toCreditBanActiveDto();
        $send = fn () => $this->post(
            KatmApiEndpointEnum::CreditBanActive->value,
            $payload,
            KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $send();
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                $this->auth->initClient($dto);
                $res = $send();
            } else {
                throw $e;
            }
        }

        return KatmResponseDto::from($res);
    }

    /**
     * Kredit ban statusini tekshirish.
     * Cashda Bearer yo‘q bo‘lsa → authenticate() + withBearer()
     * Ishlash logikasi:
     * - Agar `success = false` bo‘lsa → API javobi DTO sifatida qaytariladi
     * - Agar `success = true` bo‘lsa:
     *   - status = 0 → "Запрет не активирован"
     *   - status = 1 → "Запрет активирован"
     */
    /**
     * Kredit ban statusini tekshiradi.
     *
     * @throws BadRequestException
     */
    public function creditBanStatus(InitClientRequestDto $dto): KatmResponseDto
    {
        if (! $this->restoreTokenFromCache()) {
            $this->auth->authenticate();
        }
        $res = $this->post(
            path: KatmApiEndpointEnum::CreditBanStatus->value,
            payload: $dto->toCreditBanStatusDto(),
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        // 1) Agar success = false → oddiy DTO qaytaramiz
        if (($res['success'] ?? false) === false) {
            return KatmResponseDto::from($res);
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
        if ($e->errId === 102) {
            return true;
        }

        $m = mb_strtolower($e->getMessage());

        return str_contains($m, 'Пользователь не найден')
            || str_contains($m, 'Client not found')
            || str_contains($m, 'Not found');
    }
}
