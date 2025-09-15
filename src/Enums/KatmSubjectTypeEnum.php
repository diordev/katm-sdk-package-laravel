<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmSubjectTypeEnum
 *
 * Foydalanuvchi subyekti turini bildiruvchi enum.
 *
 * Qiymatlar:
 * - `Organization` → 1 (Yuridik shaxs)
 * - `Individual`   → 2 ( Jismoniy shaxs)
 *
 * Ushbu enum quyidagi holatlarda ishlatiladi:
 * - API so‘rovlarida subyekt turini ko‘rsatish
 * - DTO'lar (masalan: InitClientRequestDto, CreditBanStatusRequestDto) ichida
 * - Ma'lumotlar bazasida yoki logik qarorlar qabul qilishda
 */
enum KatmSubjectTypeEnum: int
{
    /** Yuridik shaxs (tashkilot) */
    case Organization = 1;

    /** Jismoniy shaxs */
    case Individual = 2;

}
