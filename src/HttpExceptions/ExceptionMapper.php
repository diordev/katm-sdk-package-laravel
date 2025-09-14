<?php

namespace Katm\KatmSdk\HttpExceptions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Katm\KatmSdk\HttpExceptions\Client\BadRequestException;
use Katm\KatmSdk\HttpExceptions\Client\ForbiddenException;
use Katm\KatmSdk\HttpExceptions\Client\NotFoundException;
use Katm\KatmSdk\HttpExceptions\Client\TooManyRequestsException;
use Katm\KatmSdk\HttpExceptions\Client\UnauthorizedException;
use Katm\KatmSdk\HttpExceptions\Client\UnprocessableEntityException;
use Katm\KatmSdk\HttpExceptions\Server\ServerErrorException;
use Katm\KatmSdk\HttpExceptions\Server\ServiceUnavailableException;

/**
 * ExceptionMapper
 *
 * KATM SDK’da barcha HTTP va API xatolarini yagona joydan boshqaradi.
 *
 * Uchta asosiy vazifasi bor:
 *
 * 1. fromTransport(Throwable $e):
 *    - Transport-level xatolarni (tarmoq uzilishi, timeout va h.k.)
 *      yoki Laravelning RequestException/ConnectionExceptionlarini
 *      mos custom exceptionlarga map qiladi.
 *
 * 2. fromResponse(Response $res):
 *    - Agar HTTP status 4xx/5xx bo‘lsa, status kodiga mos custom
 *      exception (BadRequestException, UnauthorizedException, va h.k.)
 *      tashlaydi.
 *
 * 3. ensureSuccess(array $json, int $status):
 *    - HTTP 200 OK kelgan bo‘lsa ham, javob body ichida
 *      `success=false` bo‘lsa, KatmApiException tashlaydi.
 *      Bu biznes-darajadagi xatolarni bildiradi.
 *
 * Shu tarzda ExceptionMapper yordamida:
 * - Transport xatolari
 * - HTTP status xatolari
 * - Biznes xatolar
 * yagona markaziy qatlamda boshqariladi va SDK foydalanuvchisi
 * xatolarning qaysi turdan kelib chiqqanini aniq bilib oladi.
 */
final class ExceptionMapper
{
    /** Response statusga qarab maxsus exception tashlaydi */
    public static function fromResponse(Response $res): never
    {
        $status = $res->status();
        $payload = self::shortBody($res);
        $msg = self::buildMessage($status, $payload);

        switch ($status) {
            case 400: throw new BadRequestException($msg, 400);
            case 401: throw new UnauthorizedException($msg, 401);
            case 403: throw new ForbiddenException($msg, 403);
            case 404: throw new NotFoundException($msg, 404);
            case 422: throw new UnprocessableEntityException($msg, 422);
            case 429: throw new TooManyRequestsException($msg, 429);
            case 500: throw new ServerErrorException($msg, 500);
            case 502:
            case 503:
            case 504:
                throw new ServiceUnavailableException($msg, $status);
            default:
                // noma’lum status – bazaviy exception
                throw new KatmHttpException($msg, $status);
        }
    }

    public static function ensureSuccess(array $json, int $status = 200): void
    {
        $ok = $json['success'] ?? null;
        if ($ok === true) {
            return;
        }

        $msg = (string) ($json['error']['errMsg'] ?? $json['message'] ?? 'Operation failed.');
        $code = (int) ($json['error']['code'] ?? $json['code'] ?? $status);

        throw new KatmApiException($msg, $code);
    }

    /** Transport-level xatolarni normalizatsiya qilish */
    public static function fromTransport(\Throwable $e): never
    {
        if ($e instanceof ConnectionException) {
            throw new ServiceUnavailableException('Connection failed or timeout (connect/read).', 0, $e);
        }
        if ($e instanceof RequestException && $e->response) {
            self::fromResponse($e->response); // never
        }
        // boshqa xatolar – aynan o‘zini yuboramiz
        throw $e;
    }

    private static function buildMessage(int $status, array $payload): string
    {
        $err = $payload['error'] ?? $payload['message'] ?? null;
        $detail = is_string($err) ? $err : json_encode($payload, JSON_UNESCAPED_UNICODE);

        return sprintf('HTTP %d: %s', $status, $detail ?? 'Unknown error');
    }

    /** JSON tanani qisqa ko‘rinishda qaytarish */
    private static function shortBody(Response $res): array
    {
        $json = $res->json();

        return is_array($json) ? $json : ['body' => mb_substr((string) $res->body(), 0, 400)];
    }

    /**
     * Retry kerakmi? (transport yoki ma'lum statuslar bo'yicha)
     * $whenStatuses: [429, 500, 502, 503, 504] va hok.
     */
    public static function shouldRetry(\Throwable $e, array $whenStatuses = []): bool
    {
        // 1) Transport-level muammolar (DNS, connect timeout, va h.k.) – retry qilamiz
        if ($e instanceof ConnectionException) {
            return true;
        }

        // 2) HTTP 4xx/5xx lar – faqat kiritilgan statuslar bo‘yicha retry
        if ($e instanceof RequestException && $e->response) {
            $status = $e->response->status();

            return in_array($status, $whenStatuses, true);
        }

        // 3) Boshqa hollarda retry kerak emas
        return false;
    }
}
