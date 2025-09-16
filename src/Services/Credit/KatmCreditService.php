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
 * # KatmCreditService
 *
 * Kredit bo‘yicha ban (taqiqlash) servis amallari:
 * - **creditBanStatus**: holatni tekshiradi, `resultMessage` ni boyitadi.
 * - **creditBanActive**: agar allaqachon aktiv bo‘lsa — statusni qaytaradi, aks holda aktivatsiya qiladi.
 *
 * Umumiy xulq:
 * - Bearer token cache’da bo‘lmasa — `authenticate()` chaqiriladi.
 * - 401 (Unauthorized) holatda **bir marta** qayta autentifikatsiya qilinadi va so‘rov qayta yuboriladi.
 * - 400 (BadRequest) bo‘lib, `errId` = `102|110` (client not found) bo‘lsa:
 *    - status uchun: `initClient()` qilinadi va qayta yuboriladi;
 *    - active uchun: `initClient()` qilinadi va qayta yuboriladi.
 * - Boshqa `BadRequestException` lar DTO ko‘rinishida qaytariladi (`KatmResponseDto::from($e->toArray())`).
 */
final class KatmCreditService extends AbstractHttpClientService
{
    public function __construct(private readonly KatmAuthService $auth)
    {
        parent::__construct();
    }

    /**
     * Kredit ban’ni aktivlashtirish (agar allaqachon aktiv bo‘lsa, statusni qaytaradi).
     *
     * Algoritm:
     * 1) Tokenni tekshirish/olish.
     * 2) Statusni olib ko‘rish:
     *    - status = 1 → shu javob qaytariladi (ban allaqachon aktiv).
     *    - status = 0 → aktivlashtirishga o‘tiladi.
     * 3) Aktivlashtirish so‘rovi:
     *    - 401 → re-auth → retry (1 marta).
     *    - 400 (client not found: 102|110) → initClient → retry.
     *    - boshqa 400 → xatoni DTO sifatida qaytarish.
     * 4) Qulaylik uchun `for_deactivation` maydoni qo‘shiladi.
     *
     * @throws UnauthorizedException
     * @throws BadRequestException
     */
    public function creditBanActive(InitClientRequestDto $dto): KatmResponseDto
    {
        $this->ensureToken();

        // 1) Avval statusni olib ko‘ramiz
        $statusResp = $this->tryStatusWithInitIfNeeded($dto);

        if (($statusResp->data['status'] ?? null) === 1) {
            // Allaqachon aktiv — shu javobni qaytaramiz
            return $statusResp;
        }

        // 2) Aktivlashtirish
        $payload = $dto->toCreditBanActiveDto();
        $send = fn () => $this->post(
            path: KatmApiEndpointEnum::CreditBanActive->value,
            payload: $payload,
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $this->sendWithAuthRetry($send);
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                if ($e->errId === 110) {
                    return KatmResponseDto::from($e->toArray());
                }
                // Mijozni ro‘yxatdan o‘tkazamiz va qayta yuboramiz
                $this->auth->initClient($dto);
                $res = $this->sendWithAuthRetry($send);
            } else {
                // Boshqa 400 lar — DTO ko‘rinishida qaytaramiz
                return KatmResponseDto::from($e->toArray());
            }
        }

        // Qo‘shimcha ma’lumot (deaktivatsiya havolasi)
        $res['data']['for_deactivation'] = [
            'url' => 'https://portal.infokredit.uz/ban',
            'apps' => 'KATM, MyGo',
        ];

        return KatmResponseDto::from($res);
    }

    /**
     * Kredit ban statusini tekshiradi va resultMessage qo‘shadi.
     *
     * Xulq:
     * - Token cache’da bo‘lmasa — autentifikatsiya.
     * - 401 → re-auth → retry (1 marta).
     * - 400 (client not found: 102|110) → initClient → retry.
     * - Boshqa 400 → DTO ko‘rinishida qaytarish.
     *
     * `resultMessage`:
     * - status = 0 → "Запрет не активирован"
     * - status = 1 → "Запрет активирован"
     * - boshqalar → "Неизвестный статус"
     *
     * @throws BadRequestException
     * @throws UnauthorizedException
     */
    public function creditBanStatus(InitClientRequestDto $dto): KatmResponseDto
    {
        $this->ensureToken();

        $payload = $dto->toCreditBanStatusDto();
        $send = fn () => $this->post(
            path: KatmApiEndpointEnum::CreditBanStatus->value,
            payload: $payload,
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $this->sendWithAuthRetry($send);
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                $this->auth->initClient($dto);
                $res = $this->sendWithAuthRetry($send);
            } else {
                return KatmResponseDto::from($e->toArray());
            }
        }

        $this->decorateStatusMessage($res);

        return KatmResponseDto::from($res);
    }

    /**
     * 400 (BadRequestException) API xabari mijoz topilmaganini bildiradimi?
     * KATM tarafdagi kelishuvga ko‘ra errId = 102 yoki 110 bo‘lsa — klient yo‘q.
     */
    private function isClientNotFound(BadRequestException $e): bool
    {
        return match ($e->errId) {
            102, 110 => true,
            default => false,
        };
    }

    /**
     * Statusni olish: 401 bo‘lsa re-auth, 102/110 bo‘lsa init-client va retry.
     * Har doim resultMessage bilan qaytaradi.
     *
     * @throws UnauthorizedException
     * @throws BadRequestException
     */
    private function tryStatusWithInitIfNeeded(InitClientRequestDto $dto): KatmResponseDto
    {
        $payload = $dto->toCreditBanStatusDto();
        $send = fn () => $this->post(
            path: KatmApiEndpointEnum::CreditBanStatus->value,
            payload: $payload,
            auth: KatmAuthTypeEnum::AuthBearer->value
        );

        try {
            $res = $this->sendWithAuthRetry($send);
        } catch (BadRequestException $e) {
            if ($this->isClientNotFound($e)) {
                $this->auth->initClient($dto);
                $res = $this->sendWithAuthRetry($send);
            } else {
                return KatmResponseDto::from($e->toArray());
            }
        }

        $this->decorateStatusMessage($res);

        return KatmResponseDto::from($res);
    }

    /**
     * 401 Unauthorized bo‘lsa: bir marta autentifikatsiya qilib qayta yuboradi.
     * Boshqa xatolarni o‘tkazib yuboradi.
     *
     * @return array<mixed>
     *
     * @throws UnauthorizedException
     * @throws BadRequestException
     */
    private function sendWithAuthRetry(callable $send): array
    {

        try {
            return $send();
        } catch (UnauthorizedException) {
            $this->auth->authenticate();
            $this->restoreTokenFromCache();

            return $send();
        }
    }

    /**
     * Tokenni cache’dan tiklash yoki autentifikatsiya qilish.
     */
    private function ensureToken(): void
    {
        if (! $this->restoreTokenFromCache()) {
            $this->auth->authenticate();
            $this->restoreTokenFromCache();
        }
    }

    /**
     * Status javobini qulay matn bilan boyitadi.
     *
     * @param  array<string,mixed>  $res
     */
    private function decorateStatusMessage(array &$res): void
    {
        $status = $res['data']['status'] ?? null;
        $res['data']['resultMessage'] = match ($status) {
            0 => 'Запрет не активирован',
            1 => 'Запрет активирован',
            default => 'Неизвестный статус',
        };
    }
}
