<?php

namespace Katm\KatmSdk\HttpExceptions\Client;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * NotFoundException
 *
 * HTTP 404 (Not Found) status kodi uchun maxsus exception.
 *
 * So‘rovda ko‘rsatilgan resurs (mijoz, hisobot, ma’lumot) mavjud bo‘lmagan holatlarda ishlatiladi.
 *
 * Misollar:
 * - Mijoz ID topilmadi
 * - So‘ralgan hisobot mavjud emas
 * - Noto‘g‘ri URL orqali resurs chaqirilgan
 *
 * Ushbu exception odatda `ExceptionMapper::fromResponse()` metodida 404 status kodi asosida tashlanadi.
 */
class NotFoundException extends KatmHttpException {}
