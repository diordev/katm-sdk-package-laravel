<?php

namespace Katm\KatmSdk\HttpExceptions;

use RuntimeException;

/**
 * KatmHttpException
 *
 * KATM SDK’dagi barcha HTTP bilan bog‘liq custom istisnolar uchun bazaviy klass.
 *
 * - Transport-level xatolar (masalan, tarmoq, timeout)
 * - HTTP status xatolar (400, 401, 403, 500 va h.k.)
 * - Biznes-darajadagi xatolar (200 OK bo‘lsa ham success=false)
 *
 * Ushbu klassdan barcha maxsus exception’lar (BadRequestException,
 * UnauthorizedException, KatmApiException va boshqalar) meros oladi.
 *
 * Shu sababli foydalanuvchi kodida barcha HTTP xatolarni yagona
 * KatmHttpException turidan ushlab olish mumkin.
 */
class KatmHttpException extends RuntimeException
{
    public function __construct(string $message = 'HTTP error', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
