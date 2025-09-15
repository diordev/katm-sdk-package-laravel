<?php

namespace Katm\KatmSdk\Dto\Responses;

use Spatie\LaravelData\Data;

/**
 * TokenResponseDto
 *
 * KATM API orqali autentifikatsiya muvaffaqiyatli bo‘lganda qaytariladigan access token'ni ifodalovchi DTO.
 *
 * Odatda quyidagi holatlarda ishlatiladi:
 * - Login (auth) so‘rovlaridan so‘ng
 * - Tokenni saqlash, yuborish yoki boshqa xizmatlarda ishlatish uchun
 *
 * @property string $accessToken Autentifikatsiya uchun JWT yoki boshqa formatdagi access token
 */
final class TokenResponseDto extends Data
{
    /**
     * @param  string  $accessToken  Autentifikatsiya tokeni
     */
    public function __construct(
        public string $accessToken,
    ) {}
}
