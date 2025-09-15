<?php

namespace Katm\KatmSdk\HttpExceptions\Server;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * ServerErrorException
 *
 * HTTP 500 (Internal Server Error) holatini bildiruvchi exception.
 *
 * Ushbu exception server tomonida kutilmagan yoki tizimiy xatolik yuz berganda tashlanadi.
 * Odatda bu:
 * - Koddagi xatoliklar (buglar)
 * - Xizmatlararo integratsiyada muammo
 * - Resurs yetishmovchiligi (masalan: out of memory)
 *
 * Ushbu exception `ExceptionMapper::fromResponse()` metodida 500 status kodi asosida tashlanadi.
 */
class ServerErrorException extends KatmHttpException {}
