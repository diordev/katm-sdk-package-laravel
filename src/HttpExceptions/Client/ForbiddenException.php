<?php

namespace Katm\KatmSdk\HttpExceptions\Client;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * ForbiddenException
 *
 * HTTP 403 (Forbidden) holatini bildiruvchi exception.
 *
 * Foydalanuvchi tizimga kirgan bo‘lishi mumkin, ammo resursga yoki amaliyotga ruxsati mavjud emas.
 *
 * Misollar:
 * - Foydalanuvchi roli yetarli emas (masalan: `admin` huquqi yo‘q)
 * - Resursga bo‘lgan maxsus ruxsatlar mavjud emas
 *
 * Ushbu exception odatda `ExceptionMapper::fromResponse()` orqali 403 status kodi asosida tashlanadi.
 */
class ForbiddenException extends KatmHttpException {}
