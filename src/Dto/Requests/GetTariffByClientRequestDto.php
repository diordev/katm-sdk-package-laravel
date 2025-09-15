<?php

namespace Katm\KatmSdk\Dto\Requests;

use Spatie\LaravelData\Data;

/**
 * GetTariffByClientRequestDto
 *
 * Mijoz uchun tarif ma’lumotlarini olish uchun ishlatiladigan DTO (Data Transfer Object).
 * Ushbu DTO quyidagi maqsadlarda qo‘llaniladi:
 * - Mijozning tarif rejasi (paket) haqida ma’lumot olish
 * - API chaqirig‘iga `pClientId` uzatish orqali kerakli natijani olish
 *
 * @property string $pClientId Mijozning unikal identifikatori (odatda UUID yoki integer)
 */
final class GetTariffByClientRequestDto extends Data
{
    /**
     * DTO constructor.
     *
     * @param  string  $pClientId  Mijoz ID (tarif so‘rovi uchun)
     */
    public function __construct(
        public string $pClientId
    ) {}
}
