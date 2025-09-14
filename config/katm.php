<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bazaviy URL
    |--------------------------------------------------------------------------
    | API server uchun bazaviy manzil.
    */
    'base_url' => env('KATM_BASE_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Basic Auth (ixtiyoriy)
    |--------------------------------------------------------------------------
    */
    'username' => env('KATM_USERNAME', 'admin'),
    'password' => env('KATM_PASSWORD', 'secret123'),

    /*
    |--------------------------------------------------------------------------
    | Token TTL (ixtiyoriy, sekundlarda)
    |--------------------------------------------------------------------------
    */
    'token_ttl' => env('KATM_TOKEN_TTL', 120),

    /*
    |--------------------------------------------------------------------------
    | Proxy sozlamalari
    |--------------------------------------------------------------------------
    | To‘liq URL (proxy_url) yoki protokol/host/port bo‘laklari
    */
    'proxy_url' => env('HTTP_PROXY_URL'),
    'proxy_proto' => env('HTTP_PROXY_PROTOCOL'),
    'proxy_host' => env('HTTP_PROXY_HOST'),
    'proxy_port' => env('HTTP_PROXY_PORT'),

    /*
    |--------------------------------------------------------------------------
    | Timeout sozlamalari
    |--------------------------------------------------------------------------
    | timeout         - so‘rovning umumiy muddati (sekundlarda)
    | connect_timeout - ulanishni boshlash muddati (sekundlarda)
    */
    'timeout' => env('HTTP_TIMEOUT', 10),
    'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 5),

    /*
   |--------------------------------------------------------------------------
   | Retry sozlamalari
   |--------------------------------------------------------------------------
   | tries    - necha marta qayta urinish
   | sleep_ms - urinishlar orasida kutish (millisekund)
   | when     - qaysi HTTP status kodlarda qayta urinish
   */
    'retry' => [
        'tries' => env('HTTP_RETRY_TRIES', 2),
        'sleep_ms' => env('HTTP_RETRY_SLEEP_MS', 300),
        'when' => [429, 500, 502, 503, 504],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default headerlar
    |--------------------------------------------------------------------------
    | Har bir so‘rov bilan yuboriladigan umumiy headerlar.
    */
    'headers' => [
        'Accept' => env('HTTP_ACCEPT', 'application/json'),
        'User-Agent' => env('HTTP_USER_AGENT', 'KatmSdkLaravel/0.1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Harbir Requestlarga Str::uuid() yangi unikal ID yaratishga ruxsat berish.
    |--------------------------------------------------------------------------
    | Har bir so‘rov bilan X-Request-ID yuboriladigan umumiy headerlar.
    */
    'add_request_id' => env('HTTP_ADD_REQUEST_ID', false),

    /*
    |--------------------------------------------------------------------------
    | SSL sertifikat tekshiruvi
    |--------------------------------------------------------------------------
    | true:                 - default, xavfsiz ulanish (tavsiya qilinadi, prod uchun)
    | false:                - faqat dev/test uchun (self-signed cert bo‘lsa)
    | "/path/to/cert.pem":  - maxsus CA fayl yo‘li
    */
    'verify_ssl' => env('HTTP_VERIFY_SSL', false),

];
