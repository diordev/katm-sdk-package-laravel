<?php

namespace Katm\KatmSdk\HttpExceptions;

use RuntimeException;

/**
 * Class KatmHttpException
 *
 * KATM SDK’dagi barcha HTTP, transport va biznes-darajadagi xatoliklar uchun asosiy exception klassi.
 *
 * Ushbu klass quyidagi turdagi exception’lar uchun umumiy parent hisoblanadi:
 * - Transport-level xatoliklar (masalan: tarmoq uzilishi, timeout)
 * - HTTP status xatoliklari (masalan: 400, 401, 500, va h.k.)
 * - Biznes mantiqqa oid xatolar (masalan: HTTP 200 lekin success: false)
 *
 * Barcha maxsus exception’lar (`BadRequestException`, `UnauthorizedException`,
 * `KatmApiException`, `ServiceUnavailableException`, va boshqalar) ushbu klassdan meros oladi.
 *
 * Shu sababli, foydalanuvchi ilovasida:
 * ```php
 * catch (KatmHttpException $e) {
 *     // Barcha HTTP/transport/API xatoliklarni bitta joyda ushlash mumkin
 * }
 * ```
 *
 * @property int|null $errId API tomonidan qaytarilgan xatolik ID (agar mavjud bo‘lsa)
 * @property bool|null $isFriendly Xatolik foydalanuvchiga ko‘rsatish uchun qulaymi (frontend uchun)
 * @property string|null $errMsg API tomonidan qaytarilgan aniqlashtirilgan xatolik xabari
 */
class KatmHttpException extends RuntimeException
{
    public function __construct(
        string $message = 'HTTP error',
        int $code = 0,
        ?\Throwable $previous = null,

        // KATM API qo‘shimcha maydonlari
        public ?int $errId = null,
        public ?bool $isFriendly = null,
        public ?string $errMsg = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function toArray(): array
    {

        return [
            'error' => [
                'errId' => $this->errId,
                'isFriendly' => $this->isFriendly,
                'errMsg' => $this->message,
            ],
        ];
    }
}
