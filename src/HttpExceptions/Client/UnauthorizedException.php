<?php

namespace Katm\KatmSdk\HttpExceptions\Client;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * UnauthorizedException
 *
 * HTTP 401 (Unauthorized) xatolik holatini bildiruvchi maxsus exception.
 *
 * Ushbu exception quyidagi holatlarda yuzaga keladi:
 * - Foydalanuvchi avtorizatsiyadan o‘tmagan
 * - Access token mavjud emas yoki noto‘g‘ri
 * - Token muddati tugagan (expired)
 *
 * Ushbu exception `ExceptionMapper::fromResponse()` metodida 401 status kodi asosida tashlanadi.
 */
class UnauthorizedException extends KatmHttpException {}
