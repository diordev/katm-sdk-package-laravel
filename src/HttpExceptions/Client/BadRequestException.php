<?php

namespace Katm\KatmSdk\HttpExceptions\Client;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * BadRequestException
 *
 * HTTP 400 (Bad Request) xatoliklarini ifodalovchi maxsus exception.
 *
 * Odatda noto‘g‘ri yoki to‘liq bo‘lmagan ma’lumotlar bilan so‘rov yuborilganda qaytadi.
 * Masalan:
 * - Majburiy maydonlar yo‘q bo‘lsa
 * - So‘rov formati noto‘g‘ri bo‘lsa
 * - Server validatsiyadan o‘tkaza olmasa
 *
 * Bu klass `ExceptionMapper::fromResponse()` orqali avtomatik tarzda 400 status kodi uchun chaqiriladi.
 */
final class BadRequestException extends KatmHttpException {}
