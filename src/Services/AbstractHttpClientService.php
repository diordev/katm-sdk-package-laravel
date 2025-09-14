<?php

declare(strict_types=1);

namespace Katm\KatmSdk\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Katm\KatmSdk\HttpExceptions\ExceptionMapper;
use RuntimeException;

abstract class AbstractHttpClientService
{
    protected mixed $config;

    protected string $baseUrl;

    protected string $username;

    protected string $password;

    protected int $timeout;

    protected array $headers;

    /** @var array{tries:int,sleep_ms:int,when:int[]} */
    protected array $retry;

    protected array $options = [];

    protected ?string $bearer = null;

    /** Doimiy qo‘shimcha headerlar */
    protected array $extraHeaders = [];

    /** Faqat navbatdagi so‘rovga qo‘shiladigan headerlar */
    protected array $extraHeadersOnce = [];

    public function __construct()
    {
        // config/katm.php fayldagi malumot o'qib olish
        $this->config = (array) config('katm', []);

        $this->baseUrl = rtrim((string) ($this->config['base_url'] ?? ''), '/');
        $this->username = (string) ($this->config['username'] ?? '');
        $this->password = (string) ($this->config['password'] ?? '');
        $this->timeout = (int) ($this->config['timeout'] ?? 10);

        // Http header config:
        $this->headers = is_array($this->config['headers'] ?? null)
            ? $this->config['headers']
            : ['Accept' => 'application/json', 'User-Agent' => 'KatmSdkLaravel/0.1.0'];

        // Retry config:
        $tries = (int) ($this->config['retry']['tries'] ?? 0);
        $sleepMs = (int) ($this->config['retry']['sleep_ms'] ?? 0);
        $when = (array) ($this->config['retry']['when'] ?? [429, 500, 502, 503, 504]);
        $this->retry = [
            'tries' => max(0, $tries),
            'sleep_ms' => max(0, $sleepMs),
            'when' => $when,
        ];

        // Timeout config:
        $connectTimeout = $this->config['connect_timeout'] ?? null;
        if ($connectTimeout !== null) {
            $this->options['connect_timeout'] = (int) $connectTimeout;
        }
        if (array_key_exists('verify_ssl', $this->config)) {
            // bool yoki sertifikat fayl yo‘li bo‘lishi mumkin
            $this->options['verify'] = $this->config['verify_ssl'];
        }

        // Proxy config:
        $proxyUrl = $this->config['proxy_url']
            ?? $this->buildProxyUrl(
                $this->config['proxy_proto'] ?? null,
                $this->config['proxy_host'] ?? null,
                $this->config['proxy_port'] ?? null
            );

        $this->options['proxy'] = $proxyUrl;
    }

    /** Bearer token ulash/yangilash */
    public function withBearer(?string $token): static
    {
        $this->bearer = $token ?: null;

        return $this;
    }

    /** Bearer tokenni olib tashlash */
    public function withoutBearer(): static
    {
        $this->bearer = null;

        return $this;
    }

    /** Doimiy qo‘shimcha headerlar qo‘shish (merge) */
    public function withExtraHeaders(array $headers): static
    {
        $this->extraHeaders = array_merge($this->extraHeaders, $headers);

        return $this;
    }

    /** Faqat navbatdagi so‘rovga header qo‘shish */
    public function withExtraHeadersOnce(array $headers): static
    {
        $this->extraHeadersOnce = array_merge($this->extraHeadersOnce, $headers);

        return $this;
    }

    /** HTTP klientini tayyorlaydi */
    protected function client(?string $auth = null): PendingRequest
    {
        if ($this->baseUrl === '') {
            throw new RuntimeException("KATM base_url bo'sh. config/katm.php ni to'ldiring.");
        }

        // Headerlarni yig‘ish: base -> persistent -> one-shot
        $headers = array_merge($this->headers, $this->extraHeaders, $this->extraHeadersOnce);

        // Tracing uchun X-Request-ID (app.debug yoki katm.add_request_id yoqilganda)
        $addRequestId = (bool) ($this->config['add_request_id'] || config('app.debug'));
        if ($addRequestId && ! array_key_exists('X-Request-ID', $headers)) {
            $headers['X-Request-ID'] = (string) Str::uuid();
        }

        $client = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($headers)
            ->withOptions($this->options);

        // Subclass’lar uchun hook
        $client = $this->configureClient($client);

        // Retry (immediate retry ham mumkin; connection-level xatolarni ham qamrab oladi)
        if ($this->retry['tries'] > 0) {
            $whenStatuses = $this->retry['when'];
            $client = $client->retry(
                $this->retry['tries'],
                $this->retry['sleep_ms'],
                function ($exception) use ($whenStatuses): bool {
                    return ExceptionMapper::shouldRetry($exception, $whenStatuses);
                }
            );
        }

        // Auth rejimi
        switch ($auth) {
            case 'basic':
                $client = $client->withBasicAuth($this->username, $this->password);
                break;

            case 'bearer':
                if ($this->bearer === null || $this->bearer === '') {
                    throw new RuntimeException('Bearer token topilmadi. Integration servicega Authentication qiling');
                }
                $client = $client->withToken($this->bearer);
                break;

            case 'none':
            default:
                break;
        }

        return $client;
    }

    /** GET helper */
    protected function get(string $path, array $query = [], ?string $auth = null): array
    {
        return $this->requestJson('GET', $path, ['query' => $query], $auth);
    }

    /** POST helper (JSON) */
    protected function post(string $path, array $payload = [], ?string $auth = null): array
    {
        return $this->requestJson('POST', $path, ['json' => $payload], $auth);
    }

    /** PUT helper (JSON) */
    protected function put(string $path, array $payload = [], ?string $auth = null): array
    {
        return $this->requestJson('PUT', $path, ['json' => $payload], $auth);
    }

    /** PATCH helper (JSON) */
    protected function patch(string $path, array $payload = [], ?string $auth = null): array
    {
        return $this->requestJson('PATCH', $path, ['json' => $payload], $auth);
    }

    /** DELETE helper (query yoki body bilan) */
    protected function delete(string $path, array $payloadOrQuery = [], ?string $auth = null, bool $asBody = false): array
    {
        $options = $asBody ? ['json' => $payloadOrQuery] : ['query' => $payloadOrQuery];

        return $this->requestJson('DELETE', $path, $options, $auth);
    }

    /** x-www-form-urlencoded POST -> JSON kutadi */
    protected function postForm(string $path, array $payload = [], ?string $auth = null): array
    {
        $url = $this->norm($path);
        $client = $this->client($auth)->asForm();

        // one-shot headerlar faqat bitta so‘rovga
        $this->extraHeadersOnce = [];

        try {
            // HTTP xatolarda Laravel RequestException otadi
            $res = $client->post($url, $payload)->throw();
        } catch (\Throwable $e) {
            // Transport (DNS/timeout) yoki RequestException (4xx/5xx) -> custom exception
            ExceptionMapper::fromTransport($e); // never
        }

        $json = $res->json();
        if (! is_array($json)) {
            $status = $res->status();
            $type = (string) $res->header('Content-Type');
            throw new RuntimeException("Kutilgan JSON massiv emas: POST {$url}; status={$status}; content-type={$type}");
        }

        // 200 OK bo‘lsa-yu success=false bo‘lsa, biznes-xato ko‘tariladi
        ExceptionMapper::ensureSuccess($json, $res->status());

        return $json;
    }

    /** Umumiy yuboruvchi – JSON kutadi */
    protected function requestJson(string $method, string $path, array $options = [], ?string $auth = null): array
    {
        $url = $this->norm($path);
        $client = $this->client($auth);

        // one-shot headerlar iste’mol qilindi
        $this->extraHeadersOnce = [];

        try {
            // 4xx/5xx bo‘lsa Laravel RequestException otadi
            $res = $client->send($method, $url, $options)->throw();
        } catch (\Throwable $e) {
            // Transport/HTTP xatolarini sizning custom exception’laringizga map qiladi
            ExceptionMapper::fromTransport($e); // never
        }

        $json = $res->json();
        if (! is_array($json)) {
            $status = $res->status();
            $type = (string) $res->header('Content-Type');
            throw new RuntimeException("Kutilgan JSON massiv emas: {$method} {$url}; status={$status}; content-type={$type}");
        }

        // 200 OK + success=false -> KatmApiException (biznes-daraja)
        ExceptionMapper::ensureSuccess($json, $res->status());

        return $json;
    }

    /** Umumiy yuboruvchi – raw string body qaytaradi
    / Bu odatda API’dan plain text, HTML, yoki XML olish uchun kerak bo‘ladi
    / Misol: robots.txt, yoki metrics/prometheus endpoint
    /
     */
    protected function requestRaw(string $method, string $path, array $options = [], ?string $auth = null): string
    {
        $url = $this->norm($path);
        $client = $this->client($auth);

        // one-shot headerlar iste’mol qilindi
        $this->extraHeadersOnce = [];

        try {
            // 4xx/5xx bo‘lsa RequestException otadi, transportda ConnectionException
            $res = $client->send($method, $url, $options)->throw();
        } catch (\Throwable $e) {
            ExceptionMapper::fromTransport($e); // never
        }

        // Bu metodda JSON shart emas: plain text/HTML/XML qaytarish uchun
        return (string) $res->body();
    }

    /** Path normalizatsiya: absolyut URL’ni saqlab qoladi */
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

    /** Proxy URL yig‘ish */
    private function buildProxyUrl(?string $proto, ?string $host, ?string $port): ?string
    {
        $proto = trim((string) $proto);
        $host = trim((string) $host);
        $port = trim((string) $port);

        if ($proto !== '' && $host !== '' && $port !== '') {
            return "{$proto}://{$host}:{$port}";
        }

        return null;
    }

    /** Subclass’lar uchun qo‘shimcha client ni sozlash uchun */
    protected function configureClient(PendingRequest $client): PendingRequest
    {
        return $client;
    }
}
