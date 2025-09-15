<?php

namespace Katm\KatmSdk\Dto\Responses;

use Spatie\LaravelData\Data;

/**
 * ErrorResponseDto
 *
 * KATM API dan qaytgan xatolik (error) ma’lumotlarini ifodalovchi DTO.
 *
 * Ushbu DTO odatda:
 * - `success: false` bo‘lgan javoblarda ishlatiladi
 * - ExceptionMapper yoki biznes-qatlamda xatolik tafsilotlarini olishda qo‘llaniladi
 *
 * Parametrlar:
 * - $errId       — xatolik kodi (masalan: 102)
 * - $isFriendly  — foydalanuvchiga ko‘rsatish mumkin bo‘lgan xatolikmi yoki yo‘q
 * - $errMsg      — xatolik matni (odam o‘qishi uchun tushunarli so‘zlar)
 */
final class ErrorResponseDto extends Data
{
    /**
     * @param  int  $errId  Xatolik identifikatori (masalan, 102)
     * @param  bool  $isFriendly  Foydalanuvchiga ko‘rsatish mumkin bo‘lgan xatolik flagi
     * @param  string  $errMsg  Xatolik matni
     */
    public function __construct(
        public int $errId,
        public bool $isFriendly,
        public string $errMsg,
    ) {}
}
