<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmGenderTypeEnum
 *
 * Foydalanuvchi jinsini (`gender`) ifodalovchi enum.
 *
 * Raqamli qiymatlar bilan moslik:
 * - `Male`   → 1
 * - `Female` → 2
 *
 * Odatda bu qiymatlar:
 * - Foydalanuvchini ro‘yxatdan o‘tkazishda (`InitClientRequestDto`)
 * - API yuboriladigan payloadlarda
 * - Form dropdown yoki select input'larida
 * ishlatiladi.
 */
enum KatmGenderTypeEnum: int
{
    /** Erkak (male) */
    case Male = 1;

    /** Ayol (female) */
    case Female = 2;
}
