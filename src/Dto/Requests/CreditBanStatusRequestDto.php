<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmSubjectTypeEnum;
use Spatie\LaravelData\Data;

/**
 * CreditBanStatusRequestDto
 *
 * Kredit bo‘yicha ban statusini tekshirish uchun zarur ma’lumotlarni
 * ifodalovchi Data Transfer Object (DTO).
 *
 * Ushbu DTO quyidagi holatlarda ishlatiladi:
 * - Kredit taqiq (ban) mavjud yoki mavjud emasligini aniqlash
 * - Foydalanuvchini aniqlovchi identifikator va subyekt turini yuborish
 * - API chaqiriqlari uchun ma’lumotlarni strukturaviy tarzda uzatish
 *
 * @property string $pIdentifier Foydalanuvchining yagona identifikatori (masalan: INN yoki pasport raqami)
 * @property KatmSubjectTypeEnum $pSubjectType Subyekt turi (jismoniy yoki yuridik shaxs)
 */
final class CreditBanStatusRequestDto extends Data
{
    /**
     * DTO constructor.
     *
     * @param  string  $pIdentifier  Foydalanuvchining INN yoki pasport raqami
     * @param  KatmSubjectTypeEnum  $pSubjectType  Subyekt turi (enum: physical/legal)
     */
    public function __construct(
        public string $pIdentifier,
        public KatmSubjectTypeEnum $pSubjectType,
    ) {}
}
