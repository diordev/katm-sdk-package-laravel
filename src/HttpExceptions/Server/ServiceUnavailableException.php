<?php

namespace Katm\KatmSdk\HttpExceptions\Server;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * ServiceUnavailableException
 *
 * HTTP 503 (Service Unavailable) holatini bildiruvchi exception.
 *
 * Server vaqtincha mavjud bo‘lmagan holatlarda ishlatiladi.
 * Odatda quyidagi sabablarda yuz beradi:
 * - Texnik xizmat (maintenance mode)
 * - Server yuklamasi juda katta
 * - Tashqi servislar (masalan, API gateway, DB) vaqtincha ishlamayapti
 *
 * Ushbu exception `ExceptionMapper::fromResponse()` metodida 503 (yoki 502/504) status kodi asosida tashlanadi.
 */
class ServiceUnavailableException extends KatmHttpException {}
