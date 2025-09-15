<?php

declare(strict_types=1);

namespace Katm\KatmSdk\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Katm\KatmSdk\HttpExceptions\ExceptionMapper;
use RuntimeException;

/**
 * AbstractHttpClientService
 *
 * Barcha HTTP so‘rovlar uchun umumiy logika: autentifikatsiya, retry, timeout, headerlar va boshqalar.
 *
 * KatmAuthService, KatmCreditService kabi subclass’lar aynan shu klassdan meros oladi.
 */
abstract class AbstractHttpClientService
{
    /** Bearer token Cashga saqlash chun key  */
    private const TOKEN_CACHE_KEY = 'katm:token';

    /** Konfiguratsiya fayldan o‘qilgan array */
    protected mixed $config;

    /** Baza URL (masalan: https://katm-api.uz) */
    protected string $baseUrl;

    /** Basic auth foydalanuvchi nomi */
    protected string $username;

    /** Basic auth paroli */
    protected string $password;

    /** So‘rovlar uchun timeout (soniya) */
    protected int $timeout;

    /** Default headerlar (Accept, User-Agent, va h.k.) */
    protected array $headers;

    /**
     * Retry sozlamalari:
     * - tries: necha marta qayta yuboriladi
     * - sleep_ms: kutish vaqti (millisekund)
     * - when: HTTP status kodlari ro‘yxati
     *
     * @var array{tries:int,sleep_ms:int,when:int[]}
     */
    protected array $retry;

    /** HTTP client uchun qo‘shimcha Laravel options */
    protected array $options = [];

    /** Bearer token (auth: bearer rejimida ishlatiladi) */
    protected ?string $bearer = null;

    /** Har doim yuboriladigan qo‘shimcha headerlar */
    protected array $extraHeaders = [];

    /** Faqat navbatdagi so‘rovga yuboriladigan headerlar */
    protected array $extraHeadersOnce = [];

    /**
     * Konstruktor — config/katm.php dagi sozlamalarni o‘qib, client’ni tayyorlaydi
     */
    public function __construct()
    {
        $this->config = (array) config('katm', []);

        $this->baseUrl = rtrim((string) ($this->config['base_url'] ?? ''), '/');
        $this->username = (string) ($this->config['username'] ?? '');
        $this->password = (string) ($this->config['password'] ?? '');
        $this->timeout = (int) ($this->config['timeout'] ?? 10);

        $this->headers = is_array($this->config['headers'] ?? null)
            ? $this->config['headers']
            : ['Accept' => 'application/json', 'User-Agent' => 'KatmSdkLaravel/0.1.0'];

        $tries = (int) ($this->config['retry']['tries'] ?? 0);
        $sleepMs = (int) ($this->config['retry']['sleep_ms'] ?? 0);
        $when = (array) ($this->config['retry']['when'] ?? [429, 500, 502, 503, 504]);
        $this->retry = [
            'tries' => max(0, $tries),
            'sleep_ms' => max(0, $sleepMs),
            'when' => $when,
        ];

        if (($connectTimeout = $this->config['connect_timeout'] ?? null) !== null) {
            $this->options['connect_timeout'] = (int) $connectTimeout;
        }

        if (array_key_exists('verify_ssl', $this->config)) {
            $this->options['verify'] = $this->config['verify_ssl'];
        }

        $proxyUrl = $this->config['proxy_url']
            ?? $this->buildProxyUrl(
                $this->config['proxy_proto'] ?? null,
                $this->config['proxy_host'] ?? null,
                $this->config['proxy_port'] ?? null
            );

        $this->options['proxy'] = $proxyUrl;
    }

    /**
     * Bearer token o‘rnatish
     */
    public function withBearer(?string $token): static
    {
        if (! is_string($token) || $token === '') {
            return $this->withoutBearer();
        }
        $this->bearer = $token;

        if ($this->bearer) {
            $jwt_unix_time = (int) ($this->jwtExpOrNull($this->bearer));
            $ttl = $this->calculateTokenTtl($jwt_unix_time);
            Cache::put(self::TOKEN_CACHE_KEY, $this->bearer, $ttl);
        } else {
            Cache::forget(self::TOKEN_CACHE_KEY);
        }

        return $this;
    }

    /**
     * Bearer tokenni tozalash
     */
    public function withoutBearer(): static
    {
        $this->bearer = null;
        Cache::forget(self::TOKEN_CACHE_KEY);

        return $this;
    }

    /**
     * Cashda token bor yo'q'ligini tekshiradi.
     *
     * @return bool Token mavjud bo‘lsa, true. Aks holda false.
     */
    public function restoreTokenFromCache(): bool
    {
        $token = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($token) && $token !== '') {
            $this->bearer = $token;

            return true;
        }

        return false;
    }

    /**
     * Har doim yuboriladigan qo‘shimcha headerlar
     */
    public function withExtraHeaders(array $headers): static
    {
        $this->extraHeaders = array_merge($this->extraHeaders, $headers);

        return $this;
    }

    /**
     * Faqat bitta so‘rov uchun yuboriladigan headerlar
     */
    public function withExtraHeadersOnce(array $headers): static
    {
        $this->extraHeadersOnce = array_merge($this->extraHeadersOnce, $headers);

        return $this;
    }

    /**
     * Laravel HTTP klientini sozlab qaytaradi
     */
    protected function client(?string $auth = null): PendingRequest
    {
        if ($this->baseUrl === '') {
            throw new RuntimeException("KATM base_url bo'sh. config/katm.php ni to'ldiring.");
        }

        $headers = array_merge($this->headers, $this->extraHeaders, $this->extraHeadersOnce);

        $addRequestId = (bool) ($this->config['add_request_id'] || config('app.debug'));
        if ($addRequestId && ! array_key_exists('X-Request-ID', $headers)) {
            $headers['X-Request-ID'] = (string) Str::uuid();
        }

        $client = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($headers)
            ->withOptions($this->options);

        $client = $this->configureClient($client);

        if ($this->retry['tries'] > 0) {
            $whenStatuses = $this->retry['when'];
            $client = $client->retry(
                $this->retry['tries'],
                $this->retry['sleep_ms'],
                fn ($exception) => ExceptionMapper::shouldRetry($exception, $whenStatuses)
            );
        }

        // Auth
        return match ($auth) {
            'basic' => $client->withBasicAuth($this->username, $this->password),
            'bearer' => $this->bearer ? $client->withToken($this->bearer) : throw new RuntimeException('Bearer token topilmadi. Authentication qiling.'),
            default => $client,
        };
    }

    /**
     * GET so‘rovi (query bilan)
     */
    protected function get(string $path, array $query = [], ?string $auth = null): array
    {
        return $this->requestJson('GET', $path, ['query' => $query], $auth);
    }

    /**
     * POST so‘rovi (json bilan)
     */
    protected function post(string $path, array $payload = [], ?string $auth = null): array
    {
        return $this->requestJson('POST', $path, ['json' => $payload], $auth);
    }

    /**
     * PUT so‘rovi (json bilan)
     */
    protected function put(string $path, array $payload = [], ?string $auth = null): array
    {
        return $this->requestJson('PUT', $path, ['json' => $payload], $auth);
    }

    /**
     * PATCH so‘rovi (json bilan)
     */
    protected function patch(string $path, array $payload = [], ?string $auth = null): array
    {
        return $this->requestJson('PATCH', $path, ['json' => $payload], $auth);
    }

    /**
     * DELETE so‘rovi (query yoki json)
     */
    protected function delete(string $path, array $payloadOrQuery = [], ?string $auth = null, bool $asBody = false): array
    {
        $options = $asBody ? ['json' => $payloadOrQuery] : ['query' => $payloadOrQuery];

        return $this->requestJson('DELETE', $path, $options, $auth);
    }

    /**
     * POST – x-www-form-urlencoded formatida, lekin JSON javob kutiladi
     */
    protected function postForm(string $path, array $payload = [], ?string $auth = null): array
    {
        $url = $this->norm($path);
        $client = $this->client($auth)->asForm();
        $this->extraHeadersOnce = [];

        try {
            $res = $client->post($url, $payload)->throw();
        } catch (\Throwable $e) {
            ExceptionMapper::fromTransport($e);
        }

        $json = $res->json();
        if (! is_array($json)) {
            throw new RuntimeException("Kutilgan JSON massiv emas: POST {$url}");
        }

        ExceptionMapper::ensureSuccess($json, $res->status());

        return $json;
    }

    /**
     * Barcha metodlar uchun umumiy JSON yuboruvchi
     */
    protected function requestJson(string $method, string $path, array $options = [], ?string $auth = null): array
    {
        $url = $this->norm($path);
        $client = $this->client($auth);
        $this->extraHeadersOnce = [];

        try {
            $res = $client->send($method, $url, $options)->throw();
        } catch (\Throwable $e) {
            throw ExceptionMapper::fromTransport($e);
        }

        $json = $res->json();
        if (! is_array($json)) {
            throw new RuntimeException("Kutilgan JSON massiv emas: {$method} {$url}");
        }

        ExceptionMapper::ensureSuccess($json, $res->status());

        return $json;
    }

    /**
     * JSON emas, oddiy string body qaytaruvchi so‘rov (robots.txt, .xml, .html)
     */
    protected function requestRaw(string $method, string $path, array $options = [], ?string $auth = null): string
    {
        $url = $this->norm($path);
        $client = $this->client($auth);
        $this->extraHeadersOnce = [];

        try {
            $res = $client->send($method, $url, $options)->throw();
        } catch (\Throwable $e) {
            throw ExceptionMapper::fromTransport($e);
        }

        return (string) $res->body();
    }

    /**
     * URL ni normalizatsiya qiladi (relative yoki absolute)
     */
    private function norm(string $path): string
    {
        $p = trim($path);
        if ($p === '') {
            return '/';
        }
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }

        return '/'.ltrim($p, '/');
    }

    /**
     * Config bo‘yicha proxy URL yig‘adi
     */
    private function buildProxyUrl(?string $proto, ?string $host, ?string $port): ?string
    {
        $proto = trim((string) $proto);
        $host = trim((string) $host);
        $port = trim((string) $port);

        return ($proto && $host && $port) ? "{$proto}://{$host}:{$port}" : null;
    }

    /**
     * Subclass’lar uchun sozlash hook (masalan, log qo‘shish yoki trace)
     */
    protected function configureClient(PendingRequest $client): PendingRequest
    {
        return $client;
    }

    /**
     * Jwt token decode qilib expire datesini qaytaradi.
     *
     * @param  string| null  $jwt  JWT token qabul qiladi
     * @return int Unix timestamp (masalan 1758014793)
     */
    private function jwtExpOrNull(?string $jwt): ?int
    {
        if (! is_string($jwt) || $jwt === '' || substr_count($jwt, '.') !== 2) {
            return null;
        }

        [, $payloadB64] = explode('.', $jwt);
        $json = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
        $exp = $json['exp'] ?? null;

        return is_numeric($exp) ? (int) $exp : null;
    }

    /**
     * Token muddati (exp) asosida TTL hisoblaydi.
     *
     * @param  int  $exp  Unix timestamp (masalan: 1758014793)
     * @return \DateTimeInterface Cache uchun TTL (expiry time)
     */
    private function calculateTokenTtl(int $exp): mixed
    {
        // Configdan safety margin (default: 5 minut)
        $marginSec = (int) ($this->config['token_ttl'] ?? 300);

        // exp dan marginni ayrib yuboramiz
        $ttlSeconds = max(0, $exp - time() - $marginSec);

        return $ttlSeconds;
    }
}
