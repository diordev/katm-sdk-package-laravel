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
 */
final readonly class ExceptionMapper
{
    /**
     * HTTP javob statusiga qarab mos `HttpException` tashlaydi.
     *
     * 4xx va 5xx xatoliklar uchun quyidagi exceptionlar:
     * - 400: BadRequestException
     * - 401: UnauthorizedException
     * - 403: ForbiddenException
     * - 404: NotFoundException
     * - 422: UnprocessableEntityException
     * - 429: TooManyRequestsException
     * - 500: ServerErrorException
     * - 502/503/504: ServiceUnavailableException
     *
     * @param  Response  $res  Laravel HTTP javobi
     *
     * @throws KatmHttpException
     */
    public static function fromResponse(Response $res): KatmHttpException
    {
        $status = $res->status();
        $body = $res->json() ?? [];
        $error = is_array($body['error'] ?? null) ? $body['error'] : [];
        $errId = isset($error['errId']) ? (int) $error['errId'] : null;
        $friendly = isset($error['isFriendly']) ? (bool) $error['isFriendly'] : null;
        $errMsg = $error['errMsg'] ?? ($body['message'] ?? null);

        // Xabar: errMsg bo'lsa shuni afzal ko'ramiz
        $msg = sprintf('HTTP %d: %s', $status, $errMsg ? (string) $errMsg : json_encode($body, JSON_UNESCAPED_UNICODE));

        $args = [$msg, $status, null, $errId, $friendly, $errMsg];

        // Statusga mos exception
        return match ($status) {
            400 => new BadRequestException(...$args),
            401 => new UnauthorizedException(...$args),
            403 => new ForbiddenException(...$args),
            404 => new NotFoundException(...$args),
            422 => new UnprocessableEntityException(...$args),
            429 => new TooManyRequestsException(...$args),
            500 => new ServerErrorException(...$args),
            502, 503, 504 => new ServiceUnavailableException(...$args),
            default => new KatmHttpException(...$args),
        };
    }

    /**
     * API javob JSON strukturasi ichidagi `success: false` holatini aniqlaydi
     * va agar mavjud bo‘lsa, KatmApiException otadi.
     *
     * Bu metod 200 OK holatida ham biznes xatoliklarni aniqlash uchun ishlatiladi.
     *
     * @param  array  $json  JSON response body
     * @param  int  $status  HTTP status kodi (default 200)
     *
     * @throws KatmApiException
     */
    public static function ensureSuccess(array $json, int $status = 200): void
    {
        if (($json['success'] ?? null) === true) {
            return;
        }

        $error = is_array($json['error'] ?? null) ? $json['error'] : [];
        $errId = isset($error['errId']) ? (int) $error['errId'] : null;
        $friendly = isset($error['isFriendly']) ? (bool) $error['isFriendly'] : null;
        $errMsg = $error['errMsg'] ?? ($json['message'] ?? 'Operation failed.');

        throw new KatmApiException(
            message: $errMsg,
            code: $errId ?? $status,
            errId: $errId,
            isFriendly: $friendly,
            errMsg: $errMsg,
        );
    }

    /**
     * Transport-level exception (`Throwable`)ni aniqlaydi va mos custom exception'ga map qiladi.
     *
     * - Agar ConnectionException bo‘lsa → ServiceUnavailableException tashlanadi
     * - Agar RequestException bo‘lsa → `fromResponse()` orqali mos statusga qarab exception tashlanadi
     * - Aks holda, original exception tashlanadi
     *
     * @param  \Throwable  $e  Laravel HTTP yoki transport-level xatolik
     *
     * @throws KatmHttpException|\Throwable
     */
    public static function fromTransport(\Throwable $e): \Throwable
    {
        if ($e instanceof ConnectionException) {
            return new ServiceUnavailableException('Connection failed or timeout (connect/read).', 0, $e);
        }

        if ($e instanceof RequestException && $e->response) {
            return self::fromResponse($e->response);
        }

        return $e;
    }

    /**
     * Berilgan HTTP status va JSON body asosida qisqa xatolik xabari tuzadi.
     *
     * @param  int  $status  HTTP status code
     * @param  array  $payload  Response JSON body
     * @return string Formatlangan xatolik xabari (masalan: "HTTP 400: client not found")
     */
    private static function buildMessage(int $status, array $payload): string
    {
        $err = $payload['error'] ?? $payload['message'] ?? null;
        $detail = is_string($err) ? $err : json_encode($payload, JSON_UNESCAPED_UNICODE);

        return sprintf('HTTP %d: %s', $status, $detail ?? 'Unknown error');
    }

    /**
     * HTTP javob JSON emas bo‘lsa, yoki JSON bo‘lsa ham noto‘g‘ri bo‘lsa,
     * qisqa versiyasini qaytaradi (debug va log uchun).
     *
     * @param  Response  $res  Laravel HTTP response
     * @return array JSON yoki qisqa body
     */
    private static function shortBody(Response $res): array
    {
        $json = $res->json();

        return is_array($json) ? $json : ['body' => mb_substr((string) $res->body(), 0, 400)];
    }

    /**
     * Retry (qayta yuborish) kerakligini aniqlaydi.
     *
     * Quyidagi holatlarda `true`:
     * - Transport-level xatolik (ConnectionException)
     * - HTTP status: 429, 500, 502, 503, 504 (yoki berilgan `$whenStatuses` ro‘yxatida bo‘lsa)
     *
     * @param  \Throwable  $e  Laravel exception yoki transport xatolik
     * @param  array<int>  $whenStatuses  Retry ruxsat etilgan HTTP statuslar
     * @return bool Retry kerak yoki yo‘q
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
