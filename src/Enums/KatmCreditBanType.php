<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmCreditBanType
 *
 * Kredit bo‘yicha ban operatsiyalarining turini ifodalovchi enum.
 *
 * Enum qiymatlari:
 * - `ACTIVE`: kredit bo‘yicha aktiv ban qo‘yish yoki tekshirish
 * - `STATUS`: mavjud ban holatini tekshirish
 *
 * Ushbu enum quyidagilar uchun ishlatiladi:
 * - API endpointni aniqlash
 * - Tegishli DTO metodini aniqlash
 */
enum KatmCreditBanType: string
{
    /** Kredit bo‘yicha ban qo‘yish yoki faollashtirish */
    case ACTIVE = 'active';

    /** Kredit bo‘yicha mavjud ban holatini tekshirish */
    case STATUS = 'status';

    /**
     * Ban turiga mos API endpointni qaytaradi
     *
     * @return string Masalan: '/client/credit/ban/activate'
     */
    public function endpoint(): string
    {
        return match ($this) {
            self::ACTIVE => KatmApiEndpointEnum::CreditBanActive->value,
            self::STATUS => KatmApiEndpointEnum::CreditBanStatus->value,
        };
    }

    /**
     * Ban turiga mos DTO tayyorlovchi metod nomini qaytaradi
     *
     * Misollar:
     * - ACTIVE → 'toCreditBanActiveDto'
     * - STATUS → 'toCreditBanStatusDto'
     */
    public function dtoMethod(): string
    {
        return match ($this) {
            self::ACTIVE => 'toCreditBanActiveDto',
            self::STATUS => 'toCreditBanStatusDto',
        };
    }
}
