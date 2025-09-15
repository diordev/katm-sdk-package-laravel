<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmLanguageTypeEnum
 *
 * Hisobot yoki interfeys tilini bildiruvchi enum.
 *
 * Qiymatlar:
 * - `uz` → O‘zbekcha
 * - `ru` → Ruscha
 * - `en` → Inglizcha
 *
 * Ushbu enum odatda quyidagi joylarda ishlatiladi:
 * - Hisobot yoki API javobi tilini tanlash
 * - Foydalanuvchi interfeysi tilini sozlash
 * - Request'larda `language` maydonida uzatish
 */
enum KatmLanguageTypeEnum: string
{
    /** O‘zbek tili */
    case Uzbek = 'uz';

    /** Rus tili */
    case Russia = 'ru';

    /** Ingliz tili */
    case English = 'en';
}
