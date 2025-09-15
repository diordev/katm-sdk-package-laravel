<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmLanguageTypeEnum;
use Spatie\LaravelData\Data;

/**
 * SubmitReportRequestDto
 *
 * Hisobotni yakuniy tarzda jo'natish (submit) uchun zarur ma’lumotlarni o‘zida saqlovchi DTO.
 *
 * Parametrlar:
 * - $language — hisobot jo‘natiladigan til (masalan: uz, ru, en)
 * - $pClientId — hisobot tegishli bo‘lgan mijoz identifikatori
 */
final class SubmitReportRequestDto extends Data
{
    /**
     * @param  KatmLanguageTypeEnum  $language  Hisobot tili
     * @param  string  $pClientId  Mijozning ID raqami (UUID yoki boshqa format)
     */
    public function __construct(
        public KatmLanguageTypeEnum $language,
        public string $pClientId
    ) {}
}
