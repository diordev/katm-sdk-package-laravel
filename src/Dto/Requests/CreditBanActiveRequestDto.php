<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmSubjectTypeEnum;
use Spatie\LaravelData\Data;

/**
 * CreditBanActiveRequestDto
 *
 * Kredit bo‘yicha aktiv ban holatini tekshirish uchun kerakli foydalanuvchi ma’lumotlarini
 * ifodalovchi Data Transfer Object (DTO).
 *
 * Ushbu DTO quyidagilar uchun ishlatiladi:
 * - Kredit taqiqlari (ban) bor-yo‘qligini tekshiruvchi API chaqiriqlarida
 * - Foydalanuvchining identifikator ma’lumotlarini yuborish uchun
 * - `Spatie\LaravelData` orqali avtomatik serialization va validation
 *
 * @property string $pIdentifier Foydalanuvchining yagona identifikatori (INN yoki pasport raqami)
 * @property string $pFullName To‘liq ism (familiya ism sharif)
 * @property string $pIdenDate Identifikatsiya sanasi (masalan: tug‘ilgan sana yoki pasport berilgan sana)
 * @property KatmSubjectTypeEnum $pSubjectType Subyekt turi (jismoniy yoki yuridik shaxs)
 */
final class CreditBanActiveRequestDto extends Data
{
    /**
     * DTO constructor.
     *
     * @param  string  $pIdentifier  Foydalanuvchi identifikatori
     * @param  string  $pFullName  Foydalanuvchi to‘liq ismi
     * @param  string  $pIdenDate  Identifikatsiya sanasi (format: YYYY-MM-DD)
     * @param  KatmSubjectTypeEnum  $pSubjectType  Subyekt turi
     */
    public function __construct(
        public string $pIdentifier,
        public string $pFullName,
        public string $pIdenDate,
        public KatmSubjectTypeEnum $pSubjectType,
    ) {}
}
