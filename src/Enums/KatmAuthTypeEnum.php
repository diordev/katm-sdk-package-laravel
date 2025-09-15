<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmAuthTypeEnum
 *
 * KATM API chaqiriqlari uchun autentifikatsiya turini bildiruvchi enum.
 *
 * Ushbu enum yordamida API chaqirig‘i uchun autentifikatsiya sarlavhasi (header) tipi belgilanadi.
 *
 * Misollar:
 * - `AuthNone`   → Autentifikatsiyasiz chaqiriq
 * - `AuthBasic`  → Basic autentifikatsiya (login:password base64)
 * - `AuthBearer` → Bearer token orqali autentifikatsiya
 */
enum KatmAuthTypeEnum: string
{
    /** Autentifikatsiya talab qilinmaydi */
    case AuthNone = 'none';

    /** Basic autentifikatsiya (login va parol base64 orqali) */
    case AuthBasic = 'basic';

    /** Bearer token asosida autentifikatsiya (Authorization: Bearer <token>) */
    case AuthBearer = 'bearer';
}
