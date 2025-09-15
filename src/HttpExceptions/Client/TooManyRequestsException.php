<?php

namespace Katm\KatmSdk\HttpExceptions\Client;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * TooManyRequestsException
 *
 * HTTP 429 (Too Many Requests) holatida yuzaga keladigan xatolikni bildiruvchi maxsus exception.
 *
 * Ushbu xatolik:
 * - Biror foydalanuvchi yoki servis API’ga juda ko‘p so‘rov yuborganida
 * - Rate limiting siyosati buzilganida
 * - Throttling orqali cheklovlar ishlaganda yuz beradi
 *
 * Exception odatda `ExceptionMapper::fromResponse()` orqali 429 status kodi asosida tashlanadi.
 */
class TooManyRequestsException extends KatmHttpException {}
