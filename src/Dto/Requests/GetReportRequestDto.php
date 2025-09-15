<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmLanguageTypeEnum;
use Spatie\LaravelData\Data;

/**
 * GetReportRequestDto
 *
 * Hisobot olish uchun zarur bo‘lgan ma’lumotlarni ifodalovchi DTO (Data Transfer Object).
 * Ushbu DTO quyidagi maqsadlarda ishlatiladi:
 * - KATM API orqali mijozga oid hisobotni olish (masalan, kredit hisobot)
 * - So‘rov tilini, mijoz ID sini va tokenni uzatish
 * - `Spatie\LaravelData` orqali validation, serialization va mapping
 *
 * @property KatmLanguageTypeEnum $language Hisobot tili (enum: uz, ru, en va h.k.)
 * @property string $pClientId Mijozning unikal identifikatori
 * @property string $pToken Mijozga tegishli token (autentifikatsiya uchun)
 */
final class GetReportRequestDto extends Data
{
    /**
     * DTO constructor.
     *
     * @param  KatmLanguageTypeEnum  $language  Hisobot tili
     * @param  string  $pClientId  Mijozning ID raqami (UUID yoki boshqa format)
     * @param  string  $pToken  Autentifikatsiya tokeni
     */
    public function __construct(
        public KatmLanguageTypeEnum $language,
        public string $pClientId,
        public string $pToken
    ) {}
}
