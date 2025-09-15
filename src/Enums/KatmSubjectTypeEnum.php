<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmSubjectTypeEnum
 *
 * Foydalanuvchi subyekti turini bildiruvchi enum.
 *
 * Qiymatlar:
 * - `Individual`   → 1 (Jismoniy shaxs)
 * - `Organization` → 2 (Yuridik shaxs)
 *
 * Ushbu enum quyidagi holatlarda ishlatiladi:
 * - API so‘rovlarida subyekt turini ko‘rsatish
 * - DTO'lar (masalan: InitClientRequestDto, CreditBanStatusRequestDto) ichida
 * - Ma'lumotlar bazasida yoki logik qarorlar qabul qilishda
 */
enum KatmSubjectTypeEnum: int
{
    /** Jismoniy shaxs */
    case Individual = 1;

    /** Yuridik shaxs (tashkilot) */
    case Organization = 2;
}
